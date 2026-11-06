<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlockCycle;
use App\Models\Schedule;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ScheduleApiController extends Controller
{
    /**
     * Waka melihat semua jadwal siklus aktif.
     * Guru hanya melihat jadwal miliknya sendiri.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['waka', 'guru'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu tidak memiliki akses ke jadwal.',
            ], 403);
        }

        $teacher = null;

        if ($user->role === 'guru') {
            $teacher = Teacher::where('user_id', $user->id)->first();

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Guru belum memiliki data guru.',
                ], 403);
            }
        }

        $activeCycle = BlockCycle::where('is_active', true)->first();

        if (!$activeCycle) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada siklus blok yang sedang aktif.',
            ], 404);
        }

        $query = Schedule::with([
            'blockCycle',
            'teacher',
            'subject',
            'schoolClass',
            'room',
        ])->where('block_cycle_id', $activeCycle->id);

        if ($user->role === 'guru') {
            $query->where('teacher_id', $teacher->id);
        }

        $schedules = $query
            ->orderByRaw("
                CASE day
                    WHEN 'Senin' THEN 1
                    WHEN 'Selasa' THEN 2
                    WHEN 'Rabu' THEN 3
                    WHEN 'Kamis' THEN 4
                    WHEN 'Jumat' THEN 5
                    ELSE 6
                END
            ")
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data jadwal berhasil diambil.',
            'siklus_aktif' => [
                'id' => $activeCycle->id,
                'nama' => $activeCycle->cycle,
                'mulai' => $activeCycle->start_date,
                'selesai' => $activeCycle->end_date,
                'is_locked' => (bool) $activeCycle->is_locked,
            ],
            'data' => $schedules->map(function ($schedule) {
                return $this->scheduleData($schedule);
            })->values(),
        ]);
    }

    /**
     * Hanya Waka yang dapat menambahkan jadwal.
     */
    public function store(Request $request)
    {
        if ($request->user()->role !== 'waka') {
            return $this->forbidden();
        }

        $activeCycle = BlockCycle::where('is_active', true)->first();

        if (!$activeCycle) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada putaran blok yang aktif.',
            ], 422);
        }

        if ($activeCycle->is_locked) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal telah dikunci oleh Waka Kurikulum.',
            ], 403);
        }

        if ($request->filled('start_period') && $request->filled('end_period')) {
            $periods = Schedule::PERIODS;
            $startP = (int) $request->input('start_period');
            $endP = (int) $request->input('end_period');
            if (isset($periods[$startP]) && isset($periods[$endP])) {
                $request->merge([
                    'start_time' => $periods[$startP]['start'],
                    'end_time' => $periods[$endP]['end'],
                ]);
            }
        }

        $validated = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'day' => [
                'required',
                'string',
                'in:Senin,Selasa,Rabu,Kamis,Jumat',
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
        ]);

        $conflict = $this->findConflictMessage(
            $validated,
            $activeCycle->id
        );

        if ($conflict !== null) {
            return response()->json([
                'success' => false,
                'message' => $conflict,
            ], 422);
        }

        $schedule = Schedule::create([
            'block_cycle_id' => $activeCycle->id,
            'teacher_id' => $validated['teacher_id'],
            'subject_id' => $validated['subject_id'],
            'class_id' => $validated['class_id'],
            'room_id' => $validated['room_id'],
            'day' => $validated['day'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        $schedule->load([
            'blockCycle',
            'teacher',
            'subject',
            'schoolClass',
            'room',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil ditambahkan.',
            'data' => $this->scheduleData($schedule),
        ], 201);
    }

    /**
     * Hanya Waka yang dapat mengubah jadwal.
     */
    public function update(Request $request, string $id)
    {
        if ($request->user()->role !== 'waka') {
            return $this->forbidden();
        }

        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan.',
            ], 404);
        }

        $activeCycle = BlockCycle::where('is_active', true)->first();

        if (!$activeCycle) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada putaran blok yang aktif.',
            ], 422);
        }

        if ((int) $schedule->block_cycle_id !== (int) $activeCycle->id) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal bukan bagian dari putaran yang sedang aktif.',
            ], 422);
        }

        if ($activeCycle->is_locked) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal telah dikunci oleh Waka Kurikulum.',
            ], 403);
        }

        if ($request->filled('start_period') && $request->filled('end_period')) {
            $periods = Schedule::PERIODS;
            $startP = (int) $request->input('start_period');
            $endP = (int) $request->input('end_period');
            if (isset($periods[$startP]) && isset($periods[$endP])) {
                $request->merge([
                    'start_time' => $periods[$startP]['start'],
                    'end_time' => $periods[$endP]['end'],
                ]);
            }
        }

        $validated = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'day' => [
                'required',
                'string',
                'in:Senin,Selasa,Rabu,Kamis,Jumat',
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
        ]);

        $conflict = $this->findConflictMessage(
            $validated,
            $activeCycle->id,
            (int) $schedule->id
        );

        if ($conflict !== null) {
            return response()->json([
                'success' => false,
                'message' => $conflict,
            ], 422);
        }

        $schedule->update([
            'teacher_id' => $validated['teacher_id'],
            'subject_id' => $validated['subject_id'],
            'class_id' => $validated['class_id'],
            'room_id' => $validated['room_id'],
            'day' => $validated['day'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        $schedule->load([
            'blockCycle',
            'teacher',
            'subject',
            'schoolClass',
            'room',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil diperbarui.',
            'data' => $this->scheduleData($schedule),
        ]);
    }

    /**
     * Hanya Waka yang dapat menghapus jadwal.
     */
    public function destroy(Request $request, string $id)
    {
        if ($request->user()->role !== 'waka') {
            return $this->forbidden();
        }

        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan.',
            ], 404);
        }

        $activeCycle = BlockCycle::where('is_active', true)->first();

        if (!$activeCycle) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada putaran blok yang aktif.',
            ], 422);
        }

        if ((int) $schedule->block_cycle_id !== (int) $activeCycle->id) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal bukan bagian dari putaran yang sedang aktif.',
            ], 422);
        }

        if ($activeCycle->is_locked) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal telah dikunci oleh Waka Kurikulum.',
            ], 403);
        }

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil dihapus.',
        ]);
    }

    /**
     * Membuat pesan ketika jadwal bertabrakan.
     */
    private function findConflictMessage(
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

        $conflict = $query->first();

        if (!$conflict) {
            return null;
        }

        $messages = [];

        if ((int) $conflict->room_id === (int) $validated['room_id']) {
            $messages[] = 'Ruang '
                . ($conflict->room?->name ?? '-')
                . ' sudah terpakai pada waktu tersebut.';
        }

        if ((int) $conflict->class_id === (int) $validated['class_id']) {
            $messages[] = 'Kelas '
                . ($conflict->schoolClass?->name ?? '-')
                . ' sudah memiliki jadwal pada waktu tersebut.';
        }

        if ((int) $conflict->teacher_id === (int) $validated['teacher_id']) {
            $messages[] = 'Guru '
                . ($conflict->teacher?->name ?? 'bersangkutan')
                . ' sudah terjadwal mengajar'
                . ($conflict->subject?->name ? ' (' . $conflict->subject->name . ')' : '')
                . ' pada waktu tersebut.';
        }

        return implode(' ', $messages);
    }

    /**
     * Format data jadwal untuk respons API.
     */
    private function scheduleData(Schedule $schedule): array
    {
        return [
            'id' => $schedule->id,
            'guru' => $schedule->teacher?->name,
            'hari' => $schedule->day,
            'jam_mulai' => $schedule->start_time,
            'jam_selesai' => $schedule->end_time,
            'sesi' => $schedule->session_label,
            'jam_ke_mulai' => $schedule->start_period,
            'jam_ke_selesai' => $schedule->end_period,
            'mata_pelajaran' => $schedule->subject?->name,
            'kelas' => $schedule->schoolClass?->name,
            'ruang' => $schedule->room?->name,
            'siklus' => $schedule->blockCycle?->cycle,
        ];
    }

    /**
     * Respons penolakan akses.
     */
    private function forbidden()
    {
        return response()->json([
            'success' => false,
            'message' => 'Hanya Waka Kurikulum yang dapat mengelola jadwal.',
        ], 403);
    }
}
