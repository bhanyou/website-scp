<?php

namespace App\Http\Controllers;

use App\Models\Major;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class MajorController extends Controller
{
    public function index()
    {
        $majors = Major::with(['classes' => function ($q) {
            $q->withCount('students');
        }])->orderBy('code')->get();

        // Siapkan atribut kalkulasi rombel dan siswa
        foreach ($majors as $major) {
            $maxRombel = 0;
            foreach ($major->classes as $c) {
                if (preg_match('/(\d+)$/', trim($c->name), $matches)) {
                    $maxRombel = max($maxRombel, (int)$matches[1]);
                }
            }
            $major->rombel_count = $maxRombel ?: ($major->classes->groupBy('grade')->map->count()->max() ?: 1);
            $major->total_classes = $major->classes->count();
            $major->total_students = $major->classes->sum('students_count');
            $major->level = 'Tingkat X, XI, XII';
        }

        return view('majors.index', compact('majors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:majors,code'],
            'name' => ['required', 'string', 'max:255'],
            'rombel_count' => ['required', 'integer', 'min:1', 'max:4'],
        ], [
            'code.required' => 'Kode jurusan wajib diisi.',
            'code.unique' => 'Kode jurusan sudah terdaftar.',
            'name.required' => 'Nama jurusan wajib diisi.',
            'rombel_count.required' => 'Jumlah rombel wajib dipilih.',
            'rombel_count.min' => 'Jumlah rombel minimal 1.',
            'rombel_count.max' => 'Jumlah rombel maksimal 4.',
        ]);

        $code = strtoupper(trim($validated['code']));
        $name = trim($validated['name']);
        $rombelCount = (int)$validated['rombel_count'];

        Major::create([
            'code' => $code,
            'name' => $name,
        ]);

        // Otomatis buat rombel kelas untuk tingkat X, XI, XII
        $grades = ['X', 'XI', 'XII'];
        foreach ($grades as $grade) {
            for ($r = 1; $r <= $rombelCount; $r++) {
                SchoolClass::firstOrCreate(
                    ['name' => "{$grade} {$code} {$r}"],
                    [
                        'grade' => $grade,
                        'major' => $code,
                        'homeroom_teacher_id' => null,
                    ]
                );
            }
        }

        return redirect()
            ->route('majors.index')
            ->with('success', "Jurusan {$code} berhasil ditambahkan beserta {$rombelCount} rombel kelas per tingkat (X, XI, XII).");
    }

    public function update(Request $request, string $id)
    {
        $major = Major::findOrFail($id);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:majors,code,' . $major->id],
            'name' => ['required', 'string', 'max:255'],
            'rombel_count' => ['required', 'integer', 'min:1', 'max:4'],
        ], [
            'code.required' => 'Kode jurusan wajib diisi.',
            'code.unique' => 'Kode jurusan sudah terdaftar.',
            'name.required' => 'Nama jurusan wajib diisi.',
            'rombel_count.required' => 'Jumlah rombel wajib dipilih.',
            'rombel_count.min' => 'Jumlah rombel minimal 1.',
            'rombel_count.max' => 'Jumlah rombel maksimal 4.',
        ]);

        $oldCode = $major->code;
        $newCode = strtoupper(trim($validated['code']));
        $newName = trim($validated['name']);
        $targetRombel = (int)$validated['rombel_count'];

        // Jika kode jurusan berubah, sinkronkan kode dan nama pada data rombel kelas
        if ($oldCode !== $newCode) {
            $classes = SchoolClass::where('major', $oldCode)->get();
            foreach ($classes as $c) {
                $updatedName = preg_replace('/\b' . preg_quote($oldCode, '/') . '\b/', $newCode, $c->name);
                $c->update([
                    'major' => $newCode,
                    'name' => $updatedName,
                ]);
            }
        }

        $major->update([
            'code' => $newCode,
            'name' => $newName,
        ]);

        // Sesuaikan rombel kelas untuk tingkat X, XI, XII
        $grades = ['X', 'XI', 'XII'];
        foreach ($grades as $grade) {
            for ($r = 1; $r <= 4; $r++) {
                $className = "{$grade} {$newCode} {$r}";
                $existingClass = SchoolClass::where('grade', $grade)
                    ->where('major', $newCode)
                    ->where('name', $className)
                    ->first();

                if ($r <= $targetRombel) {
                    if (!$existingClass) {
                        SchoolClass::create([
                            'name' => $className,
                            'grade' => $grade,
                            'major' => $newCode,
                            'homeroom_teacher_id' => null,
                        ]);
                    }
                } else {
                    // Jika rombel dikurangi, hapus rombel berlebih jika belum ada siswa dan jadwal
                    if ($existingClass) {
                        $hasData = $existingClass->students()->exists() || $existingClass->schedules()->exists();
                        if (!$hasData) {
                            $existingClass->delete();
                        }
                    }
                }
            }
        }

        return redirect()
            ->route('majors.index')
            ->with('success', "Data jurusan {$newCode} berhasil diperbarui.");
    }

    public function destroy(string $id)
    {
        $major = Major::findOrFail($id);

        $classes = SchoolClass::where('major', $major->code)->get();
        $hasData = false;
        foreach ($classes as $c) {
            if ($c->students()->exists() || $c->schedules()->exists()) {
                $hasData = true;
                break;
            }
        }

        if ($hasData) {
            return redirect()
                ->route('majors.index')
                ->with('error', "Jurusan {$major->code} tidak dapat dihapus karena kelasnya sudah memiliki data siswa atau jadwal pelajaran.");
        }

        // Hapus rombel kelas yang kosong dan jurusan
        SchoolClass::where('major', $major->code)->delete();
        $major->delete();

        return redirect()
            ->route('majors.index')
            ->with('success', "Jurusan {$major->code} beserta rombel kelasnya berhasil dihapus.");
    }
}
