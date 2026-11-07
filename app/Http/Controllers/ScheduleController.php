<?php

namespace App\Http\Controllers;

use App\Models\BlockCycle;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\LessonHour;
use App\Services\ScheduleImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    /**
     * Waka melihat semua jadwal pada siklus aktif.
     * Guru hanya melihat jadwal miliknya pada siklus aktif.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $blockCycles = BlockCycle::orderBy('id', 'asc')->take(2)->get();
        $activeCycle = BlockCycle::where('is_active', true)->first();

        // 1. Dukung filter putaran / siklus blok dari request (block_cycle_id / block_cycle)
        $cycleFilter = $request->input('block_cycle_id') ?: $request->input('block_cycle');
        if ($cycleFilter) {
            $currentViewCycle = $blockCycles->firstWhere('id', $cycleFilter)
                ?? $blockCycles->firstWhere('cycle', $cycleFilter)
                ?? BlockCycle::find($cycleFilter)
                ?? $activeCycle;
        } else {
            $currentViewCycle = $activeCycle ?? $blockCycles->first();
        }
        $selectedCycleId = $currentViewCycle?->id;

        $scheduleQuery = Schedule::with([
            'blockCycle',
            'teacher',
            'subject',
            'schoolClass',
            'room',
        ]);

        $isBk = $user->role === 'bk' || ($user->teacher && $user->teacher->isBkTeacher());

        if ($user->role === 'guru' && !$isBk) {
            $teacher = $user->teacher ?? Teacher::where('user_id', $user->id)->first();

            if (!$teacher) {
                abort(403, 'Akun ini belum memiliki data profil guru.');
            }

            $scheduleQuery->where('teacher_id', $teacher->id);

            // Guru melihat jadwal pada putaran yang dipilih atau aktif
            if ($currentViewCycle) {
                $scheduleQuery->where('block_cycle_id', $currentViewCycle->id);
            } elseif ($activeCycle) {
                $scheduleQuery->where('block_cycle_id', $activeCycle->id);
            } else {
                $scheduleQuery->whereRaw('1 = 0');
            }
        } elseif ($user->role === 'waka' || $isBk) {
            // Waka dan Guru BK otomatis melihat jadwal pada putaran yang dipilih atau aktif
            if ($currentViewCycle) {
                $scheduleQuery->where('block_cycle_id', $currentViewCycle->id);
            } elseif ($activeCycle) {
                $scheduleQuery->where('block_cycle_id', $activeCycle->id);
            } elseif ($selectedCycleId) {
                $scheduleQuery->where('block_cycle_id', $selectedCycleId);
            }
        } else {
            abort(403, 'Kamu tidak memiliki akses ke jadwal.');
        }

        // --- FITUR FILTER JADWAL MENGAJAR ---
        // 1. Filter Hari: Dropdown (Semua Hari, Senin, Selasa, Rabu, Kamis, Jumat)
        $scheduleQuery->when($request->filled('day'), function ($q) use ($request) {
            $q->where('day', $request->day);
        });

        // 2. Filter Kelas: Dropdown daftar kelas (mendukung param class_group_id maupun class_id)
        $classFilter = $request->input('class_group_id') ?: $request->input('class_id');
        $scheduleQuery->when(!empty($classFilter), function ($q) use ($classFilter) {
            $q->where('class_id', $classFilter);
        });

        // 3. Filter Ruangan: Dropdown daftar ruangan (rooms)
        $scheduleQuery->when($request->filled('room_id'), fn($q) => $q->where('room_id', $request->room_id));

        // 4. Filter Guru: Dropdown / input pencarian guru pengajar
        $scheduleQuery->when($request->filled('teacher_id'), function ($q) use ($request) {
            $q->where('teacher_id', $request->teacher_id);
        });

        // 5. Input Search: Pencarian cepat untuk nama / kode mata pelajaran
        $scheduleQuery->when($request->filled('search'), function ($q) use ($request) {
            $search = trim($request->search);
            $q->whereHas('subject', function ($s) use ($search) {
                $s->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        });

        // Urutkan berdasarkan hari (Senin s/d Jumat) kemudian jam mulai
        $scheduleQuery->orderByRaw("FIELD(day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'), start_time ASC");

        // --- KETENTUAN PAGINATION ---
        $perPage = (int) $request->get('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 15;
        }

        $schedules = $scheduleQuery->paginate($perPage)->withQueryString();

        $selectedRoom = $request->filled('room_id') ? Room::find($request->room_id) : null;

        $teachers = Teacher::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $classes = SchoolClass::orderBy('name')->get();

        // Urutkan Ruangan secara natural: L.1 s/d L.22, L.IPA, L.PB, OR s/d OR3, HALL, R. 1 s/d R. 48
        $rooms = Room::all()->sortBy(function ($room) {
            $name = trim($room->name);
            if (preg_match('/^L\.(\d+)$/i', $name, $m)) {
                return sprintf('01-%04d', (int) $m[1]);
            }
            if (strcasecmp($name, 'L.IPA') === 0) {
                return '02-0001';
            }
            if (strcasecmp($name, 'L.PB') === 0) {
                return '02-0002';
            }
            if (preg_match('/^OR(\d*)$/i', $name, $m)) {
                $num = !empty($m[1]) ? (int) $m[1] : 0;
                return sprintf('03-%04d', $num);
            }
            if (strcasecmp($name, 'HALL') === 0) {
                return '04-0001';
            }
            if (preg_match('/^R\.\s*(\d+)$/i', $name, $m)) {
                return sprintf('05-%04d', (int) $m[1]);
            }
            return '99-' . $name;
        })->values();

        $periods = Schedule::PERIODS;
        $allLessonPeriods = [
            'Senin' => LessonHour::getPeriodsForDay('Senin'),
            'Selasa' => LessonHour::getPeriodsForDay('Selasa'),
            'Rabu' => LessonHour::getPeriodsForDay('Rabu'),
            'Kamis' => LessonHour::getPeriodsForDay('Kamis'),
            'Jumat' => LessonHour::getPeriodsForDay('Jumat'),
        ];

        return view('schedules.index', compact(
            'schedules',
            'blockCycles',
            'activeCycle',
            'currentViewCycle',
            'selectedCycleId',
            'selectedRoom',
            'teachers',
            'subjects',
            'classes',
            'rooms',
            'periods',
            'allLessonPeriods'
        ));
    }

    /**
     * Form tambah jadwal hanya untuk Waka.
     */
    public function create()
    {
        $this->ensureWaka();

        $blockCycles = BlockCycle::orderBy('id', 'asc')->take(2)->get();
        $activeCycle = BlockCycle::where('is_active', true)->first();

        $teachers = Teacher::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $classes = SchoolClass::orderBy('name')->get();
        $rooms = Room::orderBy('name')->get();
        $periods = Schedule::PERIODS;
        $allLessonPeriods = [
            'Senin' => LessonHour::getPeriodsForDay('Senin'),
            'Selasa' => LessonHour::getPeriodsForDay('Selasa'),
            'Rabu' => LessonHour::getPeriodsForDay('Rabu'),
            'Kamis' => LessonHour::getPeriodsForDay('Kamis'),
            'Jumat' => LessonHour::getPeriodsForDay('Jumat'),
        ];

        return view('schedules.create', compact(
            'blockCycles',
            'activeCycle',
            'teachers',
            'subjects',
            'classes',
            'rooms',
            'periods',
            'allLessonPeriods'
        ));
    }

    /**
     * Menyimpan jadwal yang dibuat Waka.
     */
    public function store(Request $request)
    {
        $this->ensureWaka();

        $validated = $request->validate([
            'block_cycle_id' => ['required', 'exists:block_cycles,id'],
            'teacher_id'     => ['required', 'exists:teachers,id'],
            'subject_id'     => ['required', 'exists:subjects,id'],
            'class_id'       => ['required', 'exists:classes,id'],
            'room_id'        => ['required', 'exists:rooms,id'],
            'day'            => ['required', 'string', 'in:Senin,Selasa,Rabu,Kamis,Jumat'],
            'start_period'   => ['nullable', 'integer', 'min:1', 'max:10'],
            'end_period'     => ['nullable', 'integer', 'min:1', 'max:10', 'gte:start_period'],
            'start_time'     => ['nullable', 'date_format:H:i'],
            'end_time'       => ['nullable', 'date_format:H:i'],
        ], [
            'end_period.gte' => 'Jam selesai tidak boleh lebih awal dari jam mulai.',
        ]);

        if (!empty($validated['start_period']) && !empty($validated['end_period'])) {
            $periods = LessonHour::getPeriodsForDay($validated['day']);
            if (isset($periods[$validated['start_period']]) && isset($periods[$validated['end_period']])) {
                $validated['start_time'] = $periods[$validated['start_period']]['start'];
                $validated['end_time'] = $periods[$validated['end_period']]['end'];
            } else {
                $periods = Schedule::PERIODS;
                $validated['start_time'] = $periods[$validated['start_period']]['start'];
                $validated['end_time'] = $periods[$validated['end_period']]['end'];
            }
        } elseif (empty($validated['start_time']) || empty($validated['end_time'])) {
            return back()->withInput()->withErrors(['start_period' => 'Silakan pilih jam pelajaran mulai dan selesai.']);
        }

        // Cek apakah putaran yang dipilih sedang dikunci oleh Waka
        $targetCycle = BlockCycle::findOrFail($validated['block_cycle_id']);
        if ($targetCycle->is_locked) {
            return redirect()
                ->route('schedules.index', ['cycle_id' => $targetCycle->id])
                ->with('error', "Jadwal tidak dapat ditambahkan karena Putaran {$targetCycle->cycle} sedang dikunci oleh Waka Kurikulum.");
        }

        // Cek bentrok ruangan dan kelas pada putaran tersebut
        $this->ensureNoConflict($validated, $targetCycle->id);

        Schedule::create([
            'block_cycle_id' => $targetCycle->id,
            'teacher_id'     => $validated['teacher_id'],
            'subject_id'     => $validated['subject_id'],
            'class_id'       => $validated['class_id'],
            'room_id'        => $validated['room_id'],
            'day'            => $validated['day'],
            'start_time'     => $validated['start_time'],
            'end_time'       => $validated['end_time'],
        ]);

        return redirect()
            ->route('schedules.index', ['cycle_id' => $targetCycle->id])
            ->with('success', "Jadwal berhasil ditambahkan ke Putaran {$targetCycle->cycle}.");
    }

    /**
     * Waka dapat melihat detail semua jadwal.
     * Guru hanya dapat melihat jadwal miliknya.
     */
    public function show(string $id)
    {
        $user = Auth::user();

        $query = Schedule::with([
            'blockCycle',
            'teacher',
            'subject',
            'schoolClass',
            'room',
        ]);

        $isBk = $user->role === 'bk' || ($user->teacher && $user->teacher->isBkTeacher());

        if ($user->role === 'guru' && !$isBk) {
            $teacher = Teacher::where('user_id', $user->id)->first();

            if (!$teacher) {
                abort(403, 'Akun Guru belum memiliki data guru.');
            }

            $query->where('teacher_id', $teacher->id);
        } elseif ($user->role !== 'waka' && !$isBk) {
            abort(403, 'Kamu tidak memiliki akses ke jadwal.');
        }

        $schedule = $query->findOrFail($id);

        return view('schedules.show', compact('schedule'));
    }

    /**
     * Form edit jadwal hanya untuk Waka.
     */
    public function edit(string $id)
    {
        $this->ensureWaka();

        $schedule = Schedule::with('blockCycle')->findOrFail($id);
        $activeCycle = BlockCycle::where('is_active', true)->first();
        $targetCycle = $schedule->blockCycle ?? $activeCycle;

        if (!$targetCycle) {
            return redirect()
                ->route('schedules.index')
                ->with('error', 'Putaran blok tidak ditemukan.');
        }

        if ($targetCycle->is_locked) {
            return redirect()
                ->route('schedules.index', ['cycle_id' => $targetCycle->id])
                ->with('error', "Jadwal tidak dapat diubah karena Putaran {$targetCycle->cycle} sedang dikunci oleh Waka Kurikulum.");
        }

        $teachers = Teacher::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $classes = SchoolClass::orderBy('name')->get();
        $rooms = Room::orderBy('name')->get();
        $periods = Schedule::PERIODS;
        $allLessonPeriods = [
            'Senin' => LessonHour::getPeriodsForDay('Senin'),
            'Selasa' => LessonHour::getPeriodsForDay('Selasa'),
            'Rabu' => LessonHour::getPeriodsForDay('Rabu'),
            'Kamis' => LessonHour::getPeriodsForDay('Kamis'),
            'Jumat' => LessonHour::getPeriodsForDay('Jumat'),
        ];

        return view('schedules.edit', compact(
            'schedule',
            'activeCycle',
            'targetCycle',
            'teachers',
            'subjects',
            'classes',
            'rooms',
            'periods',
            'allLessonPeriods'
        ));
    }

    /**
     * Memperbarui jadwal hanya untuk Waka.
     */
    public function update(Request $request, string $id)
    {
        $this->ensureWaka();

        $schedule = Schedule::with('blockCycle')->findOrFail($id);
        $targetCycle = $schedule->blockCycle;

        if (!$targetCycle) {
            return redirect()
                ->route('schedules.index')
                ->with('error', 'Putaran blok tidak ditemukan.');
        }

        if ($targetCycle->is_locked) {
            return redirect()
                ->route('schedules.index', ['cycle_id' => $targetCycle->id])
                ->with('error', "Jadwal tidak dapat diubah karena Putaran {$targetCycle->cycle} sedang dikunci oleh Waka Kurikulum.");
        }

        $validated = $request->validate([
            'teacher_id'   => ['required', 'exists:teachers,id'],
            'subject_id'   => ['required', 'exists:subjects,id'],
            'class_id'     => ['required', 'exists:classes,id'],
            'room_id'      => ['required', 'exists:rooms,id'],
            'day'          => ['required', 'string', 'in:Senin,Selasa,Rabu,Kamis,Jumat'],
            'start_period' => ['nullable', 'integer', 'min:1', 'max:10'],
            'end_period'   => ['nullable', 'integer', 'min:1', 'max:10', 'gte:start_period'],
            'start_time'   => ['nullable', 'date_format:H:i'],
            'end_time'     => ['nullable', 'date_format:H:i'],
        ], [
            'end_period.gte' => 'Jam selesai tidak boleh lebih awal dari jam mulai.',
        ]);

        if (!empty($validated['start_period']) && !empty($validated['end_period'])) {
            $periods = LessonHour::getPeriodsForDay($validated['day']);
            if (isset($periods[$validated['start_period']]) && isset($periods[$validated['end_period']])) {
                $validated['start_time'] = $periods[$validated['start_period']]['start'];
                $validated['end_time'] = $periods[$validated['end_period']]['end'];
            } else {
                $periods = Schedule::PERIODS;
                $validated['start_time'] = $periods[$validated['start_period']]['start'];
                $validated['end_time'] = $periods[$validated['end_period']]['end'];
            }
        } elseif (empty($validated['start_time']) || empty($validated['end_time'])) {
            return back()->withInput()->withErrors(['start_period' => 'Silakan pilih jam pelajaran mulai dan selesai.']);
        }

        $this->ensureNoConflict(
            $validated,
            $targetCycle->id,
            $schedule->id
        );

        $schedule->update([
            'teacher_id' => $validated['teacher_id'],
            'subject_id' => $validated['subject_id'],
            'class_id'   => $validated['class_id'],
            'room_id'    => $validated['room_id'],
            'day'        => $validated['day'],
            'start_time' => $validated['start_time'],
            'end_time'   => $validated['end_time'],
        ]);

        return redirect()
            ->route('schedules.index', ['cycle_id' => $targetCycle->id])
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    /**
     * Menghapus jadwal hanya untuk Waka.
     */
    public function destroy(string $id)
    {
        $this->ensureWaka();

        $schedule = Schedule::with('blockCycle')->findOrFail($id);
        $targetCycle = $schedule->blockCycle;

        if ($targetCycle && $targetCycle->is_locked) {
            return redirect()
                ->route('schedules.index', ['cycle_id' => $targetCycle->id])
                ->with('error', "Jadwal tidak dapat dihapus karena Putaran {$targetCycle->cycle} sedang dikunci oleh Waka Kurikulum.");
        }

        $schedule->delete();

        return redirect()
            ->route('schedules.index', ['cycle_id' => $targetCycle?->id])
            ->with('success', 'Jadwal berhasil dihapus.');
    }

    /**
     * Menghapus seluruh jadwal mengajar (kecuali pada putaran yang dikunci).
     */
    public function destroyAll()
    {
        $this->ensureWaka();

        $lockedIds = BlockCycle::where('is_locked', true)->pluck('id');

        $deleted = DB::transaction(function () use ($lockedIds) {
            return Schedule::whereNotIn('block_cycle_id', $lockedIds)->delete();
        });

        $message = "Seluruh data jadwal mengajar berhasil dihapus ({$deleted} jadwal).";
        if ($lockedIds->isNotEmpty()) {
            $message .= ' Jadwal pada putaran yang dikunci tidak ikut dihapus.';
        }

        return redirect()->route('schedules.index')->with('success', $message);
    }

    /**
     * Memastikan pengelolaan jadwal dilakukan oleh Waka.
     */
    private function ensureWaka(): void
    {
        if (Auth::user()->role !== 'waka') {
            abort(403, 'Hanya Waka Kurikulum yang dapat mengelola jadwal.');
        }
    }

    /**
     * Download format template CSV/Excel untuk import jadwal pelajaran.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_jadwal.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $sampleTeacher = null;
        $sampleSubject = null;
        $sampleClass = null;
        $sampleRoom = null;
        $sampleCycle = null;

        try {
            $sampleTeacher = Teacher::orderBy('name')->first();
            $sampleSubject = Subject::orderBy('name')->first();
            $sampleClass = SchoolClass::orderBy('name')->first();
            $sampleRoom = Room::orderBy('name')->first();
            $sampleCycle = BlockCycle::orderBy('id')->first();
        } catch (\Throwable $e) {
            // Abaikan error jika database sedang tidak dapat diakses
        }

        $cycleNum = $sampleCycle ? ($sampleCycle->cycle ?? 1) : 1;
        $teacherNip = $sampleTeacher?->nip ?? '198501152010011002';
        $teacherName = $sampleTeacher?->name ?? 'Budi Santoso, S.Kom';
        $subjectName = $sampleSubject?->name ?? 'Pemrograman Web';
        $className = $sampleClass?->name ?? 'XII RPL 1';
        $roomName = $sampleRoom?->name ?? 'Lab 16 RPL';

        $callback = function () use ($cycleNum, $teacherNip, $teacherName, $subjectName, $className, $roomName) {
            $output = fopen('php://output', 'w');

            // UTF-8 BOM untuk kompatibilitas langsung di Microsoft Excel Windows
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header kolom
            fputcsv($output, [
                'putaran',
                'hari',
                'nip_guru',
                'nama_guru',
                'mata_pelajaran',
                'kelas',
                'ruangan',
                'jam_mulai',
                'jam_selesai',
            ]);

            // Baris contoh 1
            fputcsv($output, [
                $cycleNum,
                'Senin',
                $teacherNip,
                $teacherName,
                $subjectName,
                $className,
                $roomName,
                '07:00',
                '08:30',
            ]);

            // Baris contoh 2
            fputcsv($output, [
                $cycleNum,
                'Selasa',
                $teacherNip,
                $teacherName,
                $subjectName,
                $className,
                $roomName,
                '08:30',
                '10:00',
            ]);

            // Baris contoh 3
            fputcsv($output, [
                $cycleNum,
                'Rabu',
                $teacherNip,
                $teacherName,
                $subjectName,
                $className,
                $roomName,
                '10:15',
                '11:45',
            ]);

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Ekspor data jadwal mengajar ke file CSV.
     */
    public function exportCsv(Request $request)
    {
        $user = Auth::user();
        $blockCycles = BlockCycle::orderBy('id', 'asc')->take(2)->get();
        $activeCycle = BlockCycle::where('is_active', true)->first();

        $selectedCycleId = $request->query('cycle_id', $activeCycle?->id ?? $blockCycles->first()?->id);
        $currentViewCycle = $blockCycles->firstWhere('id', $selectedCycleId) ?? $activeCycle;

        $scheduleQuery = Schedule::with([
            'blockCycle',
            'teacher',
            'subject',
            'schoolClass',
            'room',
        ]);

        $isBk = $user->role === 'bk' || ($user->teacher && $user->teacher->isBkTeacher());

        if ($user->role === 'guru' && !$isBk) {
            $teacher = $user->teacher ?? Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                $scheduleQuery->where('teacher_id', $teacher->id);
            }
            if ($activeCycle) {
                $scheduleQuery->where('block_cycle_id', $activeCycle->id);
            }
        } elseif ($currentViewCycle) {
            $scheduleQuery->where('block_cycle_id', $currentViewCycle->id);
        }

        if ($request->filled('class_id')) {
            $scheduleQuery->where('class_id', $request->query('class_id'));
        }
        if ($request->filled('day')) {
            $scheduleQuery->where('day', $request->query('day'));
        }
        if ($request->filled('search')) {
            $search = $request->query('search');
            $scheduleQuery->where(function ($q) use ($search) {
                $q->whereHas('teacher', fn($t) => $t->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('subject', fn($s) => $s->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('schoolClass', fn($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('room', fn($r) => $r->where('name', 'like', "%{$search}%"));
            });
        }

        $schedules = $scheduleQuery->orderBy('day')->orderBy('start_time')->get();

        $cycleName = $currentViewCycle ? 'putaran_' . $currentViewCycle->cycle . '_' : '';
        $filename = 'jadwal_mengajar_' . $cycleName . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($schedules) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Format 100% identik dengan format template import (Gambar 1)
            fputcsv($output, [
                'putaran',
                'hari',
                'nip_guru',
                'nama_guru',
                'mata_pelajaran',
                'kelas',
                'ruangan',
                'jam_mulai',
                'jam_selesai',
            ]);

            foreach ($schedules as $schedule) {
                fputcsv($output, [
                    $schedule->blockCycle?->cycle ?? '1',
                    $schedule->day,
                    $schedule->teacher?->nip ?? '',
                    $schedule->teacher?->name ?? '',
                    $schedule->subject?->name ?? '',
                    $schedule->schoolClass?->name ?? '',
                    $schedule->room?->name ?? '',
                    substr((string) $schedule->start_time, 0, 5),
                    substr((string) $schedule->end_time, 0, 5),
                ]);
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import jadwal pelajaran dari file PDF, Excel, atau CSV.
     */
    public function import(Request $request, ScheduleImportService $importService)
    {
        $user = Auth::user();

        // Hak akses: Waka Kurikulum, atau Guru untuk jadwal miliknya
        $currentTeacher = null;
        if ($user->role !== 'waka') {
            $currentTeacher = $user->teacher ?? Teacher::where('user_id', $user->id)->first();
            if (!$currentTeacher) {
                abort(403, 'Hanya Waka Kurikulum atau Guru terdaftar yang dapat mengimpor jadwal.');
            }
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,pdf,xlsx,xls', 'max:20480'],
            'default_day' => ['nullable', 'string', 'in:Senin,Selasa,Rabu,Kamis,Jumat'],
        ], [
            'file.required' => 'Berkas PDF, Excel, atau CSV wajib dipilih.',
            'file.mimes' => 'Format berkas harus berupa .pdf, .csv, atau .xlsx/.xls.',
            'file.max' => 'Ukuran file maksimal 20MB.',
        ]);

        $bypassConflict = $request->has('bypass_conflict')
            ? $request->boolean('bypass_conflict')
            : true;

        $result = $importService->import(
            $request->file('file'),
            $request->input('default_day'),
            null,
            $currentTeacher,
            $bypassConflict
        );

        if (!$result['success']) {
            return redirect()
                ->route('schedules.index')
                ->with('error', $result['message'])
                ->with('import_errors', $result['skipped_errors'] ?? [])
                ->with('import_warnings', $result['warning_logs'] ?? []);
        }

        return redirect()
            ->route('schedules.index')
            ->with('success', $result['message'])
            ->with('import_errors', $result['skipped_errors'] ?? [])
            ->with('import_warnings', $result['warning_logs'] ?? []);
    }

    /**
     * Memeriksa bentrok 3 dimensi: ruangan, kelas, dan guru pada siklus serta hari yang sama.
     */
    private function ensureNoConflict(
        array $validated,
        int $cycleId,
        ?int $exceptId = null
    ): void {
        $conflictMsg = $this->checkConflict($validated, $cycleId, $exceptId);

        if ($conflictMsg !== null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'schedule' => $conflictMsg,
            ]);
        }
    }

    /**
     * Helper untuk memeriksa tabrakan jadwal tanpa melempar exception langsung.
     */
    private function checkConflict(
        array $validated,
        int $cycleId,
        ?int $exceptId = null
    ): ?string {
        $query = Schedule::with([
            'room',
            'schoolClass',
            'teacher',
            'subject',
        ])
            ->where('block_cycle_id', $cycleId)
            ->where('day', $validated['day'])
            ->where(function ($query) use ($validated) {
                $query->where('room_id', $validated['room_id'])
                    ->orWhere('class_id', $validated['class_id'])
                    ->orWhere('teacher_id', $validated['teacher_id']);
            })
            ->where('start_time', '<', $validated['end_time'])
            ->where('end_time', '>', $validated['start_time']);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        $conflictingSchedule = $query->first();

        if (!$conflictingSchedule) {
            return null;
        }

        $messages = [];

        if ((int) $conflictingSchedule->room_id === (int) $validated['room_id']) {
            $messages[] = 'Ruang '
                . ($conflictingSchedule->room?->name ?? '-')
                . ' sudah terpakai oleh '
                . ($conflictingSchedule->subject?->name ?? 'mata pelajaran lain')
                . ' pada waktu tersebut.';
        }

        if ((int) $conflictingSchedule->class_id === (int) $validated['class_id']) {
            $messages[] = 'Kelas '
                . ($conflictingSchedule->schoolClass?->name ?? '-')
                . ' sudah memiliki jadwal pada waktu tersebut.';
        }

        if ((int) $conflictingSchedule->teacher_id === (int) $validated['teacher_id']) {
            $messages[] = 'Guru '
                . ($conflictingSchedule->teacher?->name ?? 'bersangkutan')
                . ' sudah terjadwal mengajar'
                . ($conflictingSchedule->subject?->name ? ' (' . $conflictingSchedule->subject->name . ')' : '')
                . ' pada waktu tersebut.';
        }

        return implode(' ', $messages);
    }

    /**
     * Menampilkan jadwal pelajaran lengkap mingguan (Senin - Jumat) untuk siswa & orang tua
     * berdasarkan kelas siswa pada Putaran yang sedang Aktif & Terkunci oleh Waka Kurikulum.
     */
    /**
     * Menampilkan jadwal pelajaran lengkap mingguan (Senin - Jumat) untuk siswa & orang tua
     * berdasarkan kelas siswa pada Putaran yang sedang Aktif & Terkunci oleh Waka Kurikulum.
     */
    public function studentSchedule()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $student = $user->student;
        if (!$student && $user->parent) {
            $student = $user->parent->students()->first();
        }

        if (!$student) {
            abort(403, 'Akun Anda tidak terhubung dengan data siswa.');
        }

        $student->load(['schoolClass.homeroomTeacher', 'parent']);
        $schoolClass = $student->schoolClass;

        // Ambil putaran aktif yang ditetapkan oleh Waka Kurikulum
        $activeCycle = \App\Models\BlockCycle::where('is_active', true)->first();
        if (!$activeCycle) {
            $activeCycle = \App\Models\BlockCycle::orderBy('id', 'asc')->first();
        }

        // Tentukan hari ini dalam bahasa Indonesia
        $indonesianDays = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        $todayName = $indonesianDays[now()->dayOfWeek] ?? 'Senin';

        // Ambil seluruh jadwal kelas siswa untuk putaran aktif (Senin - Jumat)
        $schedules = collect();
        if ($schoolClass && $activeCycle) {
            $schedules = \App\Models\Schedule::with(['subject', 'teacher', 'room'])
                ->where('class_id', $schoolClass->id)
                ->where('block_cycle_id', $activeCycle->id)
                ->orderBy('day')
                ->orderBy('start_time')
                ->get();
        }

        // Kelompokkan jadwal per hari
        $schedulesByDay = [
            'Senin' => $schedules->where('day', 'Senin')->values(),
            'Selasa' => $schedules->where('day', 'Selasa')->values(),
            'Rabu' => $schedules->where('day', 'Rabu')->values(),
            'Kamis' => $schedules->where('day', 'Kamis')->values(),
            'Jumat' => $schedules->where('day', 'Jumat')->values(),
        ];

        // Sesi jam pelajaran per hari dari LessonHour
        $allLessonPeriods = [
            'Senin' => \App\Models\LessonHour::getPeriodsForDay('Senin'),
            'Selasa' => \App\Models\LessonHour::getPeriodsForDay('Selasa'),
            'Rabu' => \App\Models\LessonHour::getPeriodsForDay('Rabu'),
            'Kamis' => \App\Models\LessonHour::getPeriodsForDay('Kamis'),
            'Jumat' => \App\Models\LessonHour::getPeriodsForDay('Jumat'),
        ];

        return view('schedules.student', compact(
            'student',
            'schoolClass',
            'activeCycle',
            'todayName',
            'schedules',
            'schedulesByDay',
            'allLessonPeriods'
        ));
    }
}
