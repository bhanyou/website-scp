<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\ClassGroup;
use App\Models\Teacher;
use App\Models\Department;
use App\Models\Major;
use App\Services\DocumentTableExtractor;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SchoolClassController extends Controller
{
    public function index(Request $request)
    {
        $query = SchoolClass::with(['homeroomTeacher', 'department'])
            ->withCount('students');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('homeroomTeacher', function ($tq) use ($search) {
                      $tq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('department', function ($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('grade')) {
            $query->where('grade', $request->input('grade'));
        }

        if ($request->filled('major')) {
            $majorFilter = $request->input('major');
            $query->where(function ($q) use ($majorFilter) {
                $q->where('major', $majorFilter)
                  ->orWhereHas('department', function ($dq) use ($majorFilter) {
                      $dq->where('code', $majorFilter);
                  });
            });
        }

        $classes = $query->orderBy('grade')->orderBy('name')->get();
        $teachers = Teacher::orderBy('name')->get();
        $allMajors = Major::orderBy('code')->get();
        $majors = SchoolClass::select('major')->distinct()->whereNotNull('major')->orderBy('major')->pluck('major');
        $grades = SchoolClass::select('grade')->distinct()->whereNotNull('grade')->orderBy('grade')->pluck('grade');

        return view('classes.index', compact('classes', 'teachers', 'majors', 'grades', 'allMajors'));
    }

    public function create()
    {
        $teachers = Teacher::orderBy('name')->get();
        $allMajors = Major::orderBy('code')->get();

        return view('classes.create', compact('teachers', 'allMajors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'grade' => ['nullable', 'string', 'max:20'],
            'major' => ['nullable', 'string', 'max:100'],
            'homeroom_teacher_id' => [
                'nullable',
                'exists:teachers,id',
            ],
        ]);

        // Auto-sinkronisasi data Jurusan (Department) menggunakan regex /^(X|XI|XII)\s+([A-Z]+)/i
        $dept = SchoolClass::resolveDepartmentFromName($validated['name']);
        if ($dept) {
            $validated['department_id'] = $dept->id;
            $validated['major_id'] = $dept->id;
            if (empty($validated['major'])) {
                $validated['major'] = $dept->code;
            }
        }

        if (empty($validated['grade']) && preg_match('/^(XII|XI|X)\b/i', trim($validated['name']), $m)) {
            $validated['grade'] = strtoupper($m[1]);
        }

        SchoolClass::create($validated);

        return redirect()
            ->route('classes.index')
            ->with('success', 'Data kelas berhasil ditambahkan dan otomatis terhubung ke Jurusan.');
    }

    public function show(string $id)
    {
        $class = SchoolClass::with(['homeroomTeacher', 'department'])
            ->findOrFail($id);

        return view('classes.show', compact('class'));
    }

    public function edit(string $id)
    {
        $class = SchoolClass::findOrFail($id);
        $teachers = Teacher::orderBy('name')->get();
        $allMajors = Major::orderBy('code')->get();

        return view('classes.edit', compact('class', 'teachers', 'allMajors'));
    }

    public function update(Request $request, string $id)
    {
        $class = SchoolClass::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'grade' => ['nullable', 'string', 'max:20'],
            'major' => ['nullable', 'string', 'max:100'],
            'homeroom_teacher_id' => [
                'nullable',
                'exists:teachers,id',
            ],
        ]);

        // Auto-sinkronisasi data Jurusan (Department) menggunakan regex /^(X|XI|XII)\s+([A-Z]+)/i
        $dept = SchoolClass::resolveDepartmentFromName($validated['name']);
        if ($dept) {
            $validated['department_id'] = $dept->id;
            $validated['major_id'] = $dept->id;
            if (empty($validated['major'])) {
                $validated['major'] = $dept->code;
            }
        }

        if (empty($validated['grade']) && preg_match('/^(XII|XI|X)\b/i', trim($validated['name']), $m)) {
            $validated['grade'] = strtoupper($m[1]);
        }

        $class->update($validated);

        return redirect()
            ->route('classes.index')
            ->with('success', 'Data kelas berhasil diperbarui dan disinkronkan ke Jurusan.');
    }

    public function destroy(string $id)
    {
        $class = SchoolClass::findOrFail($id);

        $class->delete();

        return redirect()
            ->route('classes.index')
            ->with('success', 'Data kelas berhasil dihapus.');
    }

    /**
     * Import data kelas dari file Excel (.xlsx, .xls), CSV (.csv), Word (.docx), atau PDF (.pdf).
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
        ], [
            'file.required' => 'File CSV/Excel/Dokumen wajib diunggah.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ]);

        $file = $request->file('file');
        $extension = strtolower(trim((string) $file->getClientOriginalExtension()));
        if (empty($extension)) {
            $extension = strtolower(trim((string) $file->extension()));
        }

        $filePath = $file->getRealPath();
        $allRows = DocumentTableExtractor::extract($filePath, $extension);

        if (empty($allRows)) {
            return redirect()
                ->route('classes.index')
                ->with('error', 'Gagal membaca tabel data dari file yang diunggah. Pastikan file memuat baris data yang valid.');
        }

        // Cari baris header secara cerdas di antara 10 baris pertama
        $headerRowIndex = null;
        $headerMap = [];

        foreach (array_slice($allRows, 0, 10, true) as $rowIndex => $candidateRow) {
            $tempMap = [];
            foreach ($candidateRow as $colIdx => $col) {
                $clean = strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', (string)$col)));
                $clean = preg_replace('/[^a-z0-9]/', '', $clean);

                if (empty($clean)) {
                    continue;
                }

                if (!isset($tempMap['name']) && (str_contains($clean, 'kelas') || str_contains($clean, 'rombel') || str_contains($clean, 'namakelas') || str_contains($clean, 'class'))) {
                    $tempMap['name'] = $colIdx;
                } elseif (!isset($tempMap['grade']) && (str_contains($clean, 'tingkat') || str_contains($clean, 'grade') || str_contains($clean, 'jenjang'))) {
                    $tempMap['grade'] = $colIdx;
                } elseif (!isset($tempMap['major']) && (str_contains($clean, 'jurusan') || str_contains($clean, 'program') || str_contains($clean, 'kompetensi') || str_contains($clean, 'major'))) {
                    $tempMap['major'] = $colIdx;
                } elseif (!isset($tempMap['homeroom']) && (str_contains($clean, 'wali') || str_contains($clean, 'walikelas') || str_contains($clean, 'guru'))) {
                    $tempMap['homeroom'] = $colIdx;
                }
            }

            if (isset($tempMap['name'])) {
                $headerRowIndex = $rowIndex;
                $headerMap = $tempMap;
                break;
            }
        }

        if ($headerRowIndex === null) {
            return redirect()
                ->route('classes.index')
                ->with('error', 'Header kolom wajib (nama_kelas / rombel) tidak ditemukan di dalam berkas.');
        }

        $rows = array_slice($allRows, $headerRowIndex + 1);
        $importedCount = 0;
        $updatedCount = 0;
        $teachers = Teacher::all();

        foreach ($rows as $row) {
            $nameRaw = isset($headerMap['name']) ? trim((string)($row[$headerMap['name']] ?? '')) : '';
            if (empty($nameRaw)) {
                continue;
            }

            // Normalisasi nama kelas
            $className = preg_replace('/\s+/', ' ', $nameRaw);

            // Tentukan tingkat
            $grade = isset($headerMap['grade']) ? trim((string)($row[$headerMap['grade']] ?? '')) : '';
            if (empty($grade) && preg_match('/^(XII|XI|X)\b/i', $className, $m)) {
                $grade = strtoupper($m[1]);
            }

            // Tentukan jurusan
            $majorCode = isset($headerMap['major']) ? strtoupper(trim((string)($row[$headerMap['major']] ?? ''))) : '';

            // Auto-resolve Department dengan regex /^(X|XI|XII)\s+([A-Z]+)/i
            $dept = SchoolClass::resolveDepartmentFromName($className);
            $departmentId = $dept ? $dept->id : null;
            if ($dept && empty($majorCode)) {
                $majorCode = $dept->code;
            }

            // Cari wali kelas jika ada
            $homeroomTeacherId = null;
            if (isset($headerMap['homeroom'])) {
                $teacherName = trim((string)($row[$headerMap['homeroom']] ?? ''));
                if (!empty($teacherName)) {
                    $matchedTeacher = $teachers->first(function ($t) use ($teacherName) {
                        return stripos($t->name, $teacherName) !== false || stripos($teacherName, $t->name) !== false;
                    });
                    if ($matchedTeacher) {
                        $homeroomTeacherId = $matchedTeacher->id;
                    }
                }
            }

            $existing = SchoolClass::where('name', $className)->first();
            if ($existing) {
                $existing->update([
                    'grade' => $grade ?: $existing->grade,
                    'major' => $majorCode ?: $existing->major,
                    'department_id' => $departmentId ?: $existing->department_id,
                    'major_id' => $departmentId ?: $existing->major_id,
                    'homeroom_teacher_id' => $homeroomTeacherId ?: $existing->homeroom_teacher_id,
                ]);
                $updatedCount++;
            } else {
                SchoolClass::create([
                    'name' => $className,
                    'grade' => $grade ?: 'X',
                    'major' => $majorCode ?: ($dept ? $dept->code : 'UMUM'),
                    'department_id' => $departmentId,
                    'major_id' => $departmentId,
                    'homeroom_teacher_id' => $homeroomTeacherId,
                ]);
                $importedCount++;
            }
        }

        return redirect()
            ->route('classes.index')
            ->with('success', "Proses import selesai. Ditambahkan: {$importedCount} kelas baru, Diperbarui: {$updatedCount} kelas.");
    }

    /**
     * Download template CSV untuk import data kelas.
     */
    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_kelas.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            // Header kolom CSV
            fputcsv($handle, ['nama_kelas', 'tingkat', 'jurusan', 'nama_wali_kelas']);

            // Contoh data resmi SMKN 1 Bantul
            fputcsv($handle, ['X RPL 1', 'X', 'RPL', 'TRI SUSILOWATI']);
            fputcsv($handle, ['XI TKJ 2', 'XI', 'TKJ', '']);
            fputcsv($handle, ['XII DKV 1', 'XII', 'DKV', '']);
            fputcsv($handle, ['X AKL 1', 'X', 'AK', '']);
            fputcsv($handle, ['X LPS 1', 'X', 'LPS', '']);
            fputcsv($handle, ['X MP 1', 'X', 'MP', '']);
            fputcsv($handle, ['X BR 1', 'X', 'BR', '']);
            fputcsv($handle, ['X BD', 'X', 'BD', '']);

            fclose($handle);
        }, 200, $headers);
    }
}
