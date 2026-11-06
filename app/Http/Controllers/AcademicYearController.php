<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\Schedule;
use App\Models\Setting;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    /**
     * Halaman manajemen Tahun Ajaran & Semester.
     */
    public function index()
    {
        // Tahun Ajaran Aktif saat ini
        $currentYear = Setting::firstOrCreate(
            ['key' => 'academic_year'],
            ['value' => '2025/2026']
        )->value;

        // Semester Aktif saat ini
        $currentSemester = Setting::firstOrCreate(
            ['key' => 'semester'],
            ['value' => 'Ganjil']
        )->value;

        // Daftar tahun ajaran yang terdaftar di sistem
        $yearsJson = Setting::where('key', 'academic_years_list')->value('value');
        $yearsList = $yearsJson ? json_decode($yearsJson, true) : ['2023/2024', '2024/2025', '2025/2026', '2026/2027'];

        if (!is_array($yearsList)) {
            $yearsList = ['2023/2024', '2024/2025', '2025/2026', '2026/2027'];
        }

        // Pastikan tahun ajaran aktif selalu ada dalam daftar
        if (!in_array($currentYear, $yearsList)) {
            $yearsList[] = $currentYear;
        }

        // Urutkan tahun ajaran secara descending (terbaru di atas)
        rsort($yearsList);

        // Statistik umum
        $totalStudents = Student::where('status', 'aktif')->count();
        $totalTeachers = Teacher::count();
        $totalClasses = SchoolClass::count();
        $totalSchedules = Schedule::count();

        // Rekomendasi tahun ajaran berikutnya
        $suggestedNextYear = '';
        if (preg_match('/^(\d{4})\/(\d{4})$/', $currentYear, $matches)) {
            $suggestedNextYear = ($matches[1] + 1) . '/' . ($matches[2] + 1);
        } else {
            $suggestedNextYear = date('Y') . '/' . (date('Y') + 1);
        }

        return view('academic_year.index', compact(
            'currentYear',
            'currentSemester',
            'yearsList',
            'totalStudents',
            'totalTeachers',
            'totalClasses',
            'totalSchedules',
            'suggestedNextYear'
        ));
    }

    /**
     * Daftarkan tahun ajaran baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => [
                'required',
                'string',
                'regex:/^\d{4}\/\d{4}$/',
            ],
            'semester' => ['nullable', 'in:Ganjil,Genap'],
            'set_active' => ['nullable', 'boolean'],
        ], [
            'year.required' => 'Tahun ajaran wajib diisi.',
            'year.regex' => 'Format tahun ajaran harus berupa format tahun (contoh: 2026/2027).',
        ]);

        $newYear = trim($validated['year']);

        // Ambil daftar tahun ajaran yang sudah ada
        $yearsJson = Setting::where('key', 'academic_years_list')->value('value');
        $yearsList = $yearsJson ? json_decode($yearsJson, true) : ['2023/2024', '2024/2025', '2025/2026', '2026/2027'];

        if (!in_array($newYear, $yearsList)) {
            $yearsList[] = $newYear;
            rsort($yearsList);
            Setting::updateOrCreate(
                ['key' => 'academic_years_list'],
                ['value' => json_encode($yearsList)]
            );
        }

        // Jika langsung diaktifkan
        if ($request->boolean('set_active')) {
            Setting::updateOrCreate(
                ['key' => 'academic_year'],
                ['value' => $newYear]
            );

            if (!empty($validated['semester'])) {
                Setting::updateOrCreate(
                    ['key' => 'semester'],
                    ['value' => $validated['semester']]
                );
            }
        }

        return redirect()
            ->route('academic-year.index')
            ->with('success', "Tahun ajaran {$newYear} berhasil ditambahkan.");
    }

    /**
     * Jadikan tahun ajaran dan semester tertentu sebagai status AKTIF.
     */
    public function setActive(Request $request)
    {
        $validated = $request->validate([
            'academic_year' => ['required', 'string'],
            'semester' => ['required', 'in:Ganjil,Genap'],
        ]);

        Setting::updateOrCreate(
            ['key' => 'academic_year'],
            ['value' => $validated['academic_year']]
        );

        Setting::updateOrCreate(
            ['key' => 'semester'],
            ['value' => $validated['semester']]
        );

        // Pastikan masuk ke daftar
        $yearsJson = Setting::where('key', 'academic_years_list')->value('value');
        $yearsList = $yearsJson ? json_decode($yearsJson, true) : [];
        if (!in_array($validated['academic_year'], $yearsList)) {
            $yearsList[] = $validated['academic_year'];
            rsort($yearsList);
            Setting::updateOrCreate(
                ['key' => 'academic_years_list'],
                ['value' => json_encode($yearsList)]
            );
        }

        return redirect()
            ->route('academic-year.index')
            ->with('success', "Status aktif berhasil diubah menjadi: Tahun Ajaran {$validated['academic_year']} (Semester {$validated['semester']}).");
    }

    /**
     * Hapus tahun ajaran dari daftar (hanya jika tidak sedang aktif).
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'year' => ['required', 'string'],
        ]);

        $yearToDelete = trim($request->year);
        $currentYear = Setting::where('key', 'academic_year')->value('value') ?? '2025/2026';

        if ($yearToDelete === $currentYear) {
            return redirect()
                ->route('academic-year.index')
                ->with('error', "Tahun ajaran {$yearToDelete} sedang berstatus AKTIF dan tidak dapat dihapus.");
        }

        $yearsJson = Setting::where('key', 'academic_years_list')->value('value');
        $yearsList = $yearsJson ? json_decode($yearsJson, true) : [];

        $yearsList = array_values(array_filter($yearsList, fn($y) => $y !== $yearToDelete));

        Setting::updateOrCreate(
            ['key' => 'academic_years_list'],
            ['value' => json_encode($yearsList)]
        );

        return redirect()
            ->route('academic-year.index')
            ->with('success', "Tahun ajaran {$yearToDelete} berhasil dihapus dari daftar.");
    }
}
