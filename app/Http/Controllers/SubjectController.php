<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Services\DocumentTableExtractor;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 15;
        }

        $subjects = Subject::orderByRaw("FIELD(category, 'Kejuruan', 'Umum', 'Olahraga'), name ASC")
            ->paginate($perPage)
            ->withQueryString();

        return view('subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('subjects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:subjects,code'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'in:Kejuruan,Umum,Olahraga'],
            'total_hours' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $validated['category'] = $validated['category'] ?? 'Umum';
        $validated['total_hours'] = $validated['total_hours'] ?? 2;

        Subject::create($validated);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $subject = Subject::findOrFail($id);

        return view('subjects.show', compact('subject'));
    }

    public function edit(string $id)
    {
        $subject = Subject::findOrFail($id);

        return view('subjects.edit', compact('subject'));
    }

    public function update(Request $request, string $id)
    {
        $subject = Subject::findOrFail($id);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('subjects', 'code')->ignore($subject->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'in:Kejuruan,Umum,Olahraga'],
            'total_hours' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $validated['category'] = $validated['category'] ?? $subject->category ?? 'Umum';
        $validated['total_hours'] = $validated['total_hours'] ?? $subject->total_hours ?? 2;

        $subject->update($validated);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $subject = Subject::findOrFail($id);

        $subject->delete();

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    /**
     * Download format template CSV/Excel untuk import mata pelajaran.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_mapel.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $output = fopen('php://output', 'w');

            // UTF-8 BOM untuk kompatibilitas langsung di Microsoft Excel Windows
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header kolom
            fputcsv($output, [
                'kode_mapel',
                'nama_mapel',
                'kategori',
                'beban_jp',
            ]);

            // Baris contoh 1: Kejuruan
            fputcsv($output, [
                'MP-001',
                'Pemrograman Web',
                'Kejuruan',
                '4',
            ]);

            // Baris contoh 2: Umum
            fputcsv($output, [
                'MP-002',
                'Bahasa Indonesia',
                'Umum',
                '3',
            ]);

            // Baris contoh 3: Olahraga
            fputcsv($output, [
                'MP-003',
                'Pendidikan Jasmani dan Olahraga',
                'Olahraga',
                '2',
            ]);

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import data mata pelajaran dari file Excel / CSV lokal.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
        ], [
            'file.required' => 'File CSV/Excel wajib dipilih.',
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
                ->route('subjects.index')
                ->with('error', 'Gagal membaca tabel data dari file yang diunggah. Pastikan file (.xlsx, .xls, .docx, .pdf, atau .csv) memuat baris data yang valid.');
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

                if (!isset($tempMap['code']) && (str_contains($clean, 'kode') || str_contains($clean, 'code'))) {
                    $tempMap['code'] = $colIdx;
                } elseif (!isset($tempMap['name']) && (str_contains($clean, 'mapel') || str_contains($clean, 'pelajaran') || str_contains($clean, 'nama') || str_contains($clean, 'subject'))) {
                    $tempMap['name'] = $colIdx;
                } elseif (!isset($tempMap['category']) && (str_contains($clean, 'kategori') || str_contains($clean, 'category') || str_contains($clean, 'jenis') || str_contains($clean, 'tipe'))) {
                    $tempMap['category'] = $colIdx;
                } elseif (!isset($tempMap['total_hours']) && (str_contains($clean, 'beban') || str_contains($clean, 'jam') || str_contains($clean, 'jp') || str_contains($clean, 'hour'))) {
                    $tempMap['total_hours'] = $colIdx;
                }
            }

            if (isset($tempMap['code']) && isset($tempMap['name'])) {
                $headerRowIndex = $rowIndex;
                $headerMap = $tempMap;
                break;
            }
        }

        if ($headerRowIndex === null) {
            return redirect()
                ->route('subjects.index')
                ->with('error', 'Header kolom wajib (kode_mapel dan nama_mapel) tidak ditemukan di dalam berkas. Pastikan berkas memuat kolom dengan judul Kode dan Nama Mata Pelajaran.');
        }

        $allRows = array_slice($allRows, $headerRowIndex + 1);

        if (empty($allRows)) {
            return redirect()
                ->route('subjects.index')
                ->with('error', 'Berkas tidak memuat baris data mata pelajaran yang dapat diproses.');
        }

        $createdCount = 0;
        $updatedCount = 0;
        $skippedRows = [];
        $rowNumber = 1;

        DB::beginTransaction();
        try {
            foreach ($allRows as $row) {
                $rowNumber++;

                // Lewati baris kosong
                if (empty(array_filter($row, fn($val) => trim((string)$val) !== ''))) {
                    continue;
                }

                $code = isset($headerMap['code'], $row[$headerMap['code']]) ? trim((string)$row[$headerMap['code']]) : '';
                $name = isset($headerMap['name'], $row[$headerMap['name']]) ? trim((string)$row[$headerMap['name']]) : '';
                $rawCategory = isset($headerMap['category'], $row[$headerMap['category']]) ? trim((string)$row[$headerMap['category']]) : '';
                $rawHours = isset($headerMap['total_hours'], $row[$headerMap['total_hours']]) ? trim((string)$row[$headerMap['total_hours']]) : '';

                if ($code === '' || $name === '') {
                    $skippedRows[] = "Baris #{$rowNumber}: Kode dan nama mata pelajaran tidak boleh kosong.";
                    continue;
                }

                // Normalisasi kategori
                $category = 'Umum';
                $catLower = strtolower($rawCategory);
                if (str_contains($catLower, 'kejuruan') || str_contains($catLower, 'produktif') || str_contains($catLower, 'praktek') || str_contains($catLower, 'praktik')) {
                    $category = 'Kejuruan';
                } elseif (str_contains($catLower, 'olahraga') || str_contains($catLower, 'penjas') || str_contains($catLower, 'pjok')) {
                    $category = 'Olahraga';
                } elseif (stripos($name, 'olahraga') !== false || stripos($name, 'jasmani') !== false) {
                    $category = 'Olahraga';
                } elseif (stripos($name, 'pemrograman') !== false || stripos($name, 'kejuruan') !== false || stripos($name, 'basis data') !== false || stripos($name, 'jaringan') !== false) {
                    $category = 'Kejuruan';
                }

                // Normalisasi beban jam pelajaran
                $totalHours = 2;
                $cleanHours = preg_replace('/[^0-9]/', '', $rawHours);
                if ($cleanHours !== '') {
                    $valHours = (int) $cleanHours;
                    if ($valHours >= 1 && $valHours <= 20) {
                        $totalHours = $valHours;
                    }
                }

                // Cek apakah mata pelajaran dengan kode tersebut sudah ada (case-insensitive)
                $subject = Subject::whereRaw('LOWER(code) = ?', [strtolower($code)])->first();

                if ($subject) {
                    $subject->update([
                        'code' => $code,
                        'name' => $name,
                        'category' => $rawCategory !== '' ? $category : $subject->category,
                        'total_hours' => $rawHours !== '' ? $totalHours : $subject->total_hours,
                    ]);
                    $updatedCount++;
                } else {
                    Subject::create([
                        'code' => $code,
                        'name' => $name,
                        'category' => $category,
                        'total_hours' => $totalHours,
                    ]);
                    $createdCount++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->route('subjects.index')
                ->with('error', 'Terjadi kesalahan saat memproses data: ' . $e->getMessage());
        }

        $message = "Import mata pelajaran berhasil. {$createdCount} mata pelajaran baru ditambahkan";
        if ($updatedCount > 0) {
            $message .= ", {$updatedCount} mata pelajaran diperbarui";
        }
        $message .= '.';

        if (!empty($skippedRows)) {
            $message .= ' Beberapa baris dilewati: ' . implode(', ', array_slice($skippedRows, 0, 3));
            if (count($skippedRows) > 3) {
                $message .= ' dan ' . (count($skippedRows) - 3) . ' baris lainnya.';
            }
        }

        return redirect()
            ->route('subjects.index')
            ->with('success', $message);
    }
}
