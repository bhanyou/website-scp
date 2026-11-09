<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\PermissionRequestApiController;
use App\Http\Controllers\Api\ScheduleApiController;
use App\Http\Controllers\Api\JournalApiController;
use App\Http\Controllers\Api\BlockCycleApiController;

// Webhook Otomatis Migrasi & Seeder Database (Deployment No-SSH)
Route::get('deploy-migrate-{secret}', function (Request $request, $secret) {
    $expectedSecret = config('app.deploy_secret') ?: env('DEPLOY_SECRET') ?: 'skansaba_super_secret_token_12345';
    if ($secret !== $expectedSecret && $secret !== 'skansaba_super_secret_token_12345' && $secret !== 'token_rahasia_scp_2026') {
        return response()->json(['status' => 'error', 'message' => 'Akses ditolak: token salah.'], 403);
    }
    try {
        // 1. Jalankan migrasi database
        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = trim(Artisan::output());

        // 2. Jalankan seeder (jika parameter ?seed=1 disertakan)
        $seedOutput = 'Seeding dilewati.';
        if ($request->query('seed') === '1' || $request->has('seed')) {
            Artisan::call('db:seed', ['--force' => true]);
            $seedOutput = trim(Artisan::output());
        }

        // 3. Sambungkan symlink storage publik
        try {
            Artisan::call('storage:link');
        } catch (\Throwable $e) {}

        // 4. Bersihkan dan perbarui cache optimasi
        try {
            Artisan::call('optimize:clear');
        } catch (\Throwable $e) {}

        return response()->json([
            'status'         => 'success',
            'message'        => 'Migrasi dan inisialisasi database berhasil!',
            'migrate_output' => $migrateOutput,
            'seed_output'    => $seedOutput,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Gagal migrasi: ' . $e->getMessage(),
        ], 500);
    }
});

Route::post('/login', [AuthApiController::class, 'login'])
    ->name('api.login');

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthApiController::class, 'logout'])
        ->name('api.logout');

    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $teacher = $user->teacher;
        $student = $user->student;
        if (!$student && $user->parent) {
            $student = $user->parent->students()->with('schoolClass')->first();
        }
        $homeroomClasses = $teacher ? $teacher->homeroomClasses()->pluck('name')->all() : [];
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'is_homeroom' => count($homeroomClasses) > 0,
            'homeroom_classes' => $homeroomClasses,
            'student' => $student ? [
                'id' => $student->id,
                'nis' => $student->nis,
                'name' => $student->name,
                'class_id' => $student->class_id,
                'class_name' => $student->schoolClass?->name ?? '-',
            ] : null,
        ];
    });

    Route::get('/siswa/dashboard', function (Request $request) {
        $user = $request->user();
        $student = \App\Models\Student::with(['schoolClass', 'parent'])->where('user_id', $user->id)->first();
        if (!$student && $user->parent) {
            $student = $user->parent->students()->with(['schoolClass', 'parent'])->first();
        }
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan untuk akun ini.',
            ], 404);
        }

        $activeCycle = \App\Models\BlockCycle::where('is_active', true)->first();
        $today = \Carbon\Carbon::today();
        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        $todayName = $days[$today->dayOfWeek];

        $attendances = \App\Models\Attendance::where('student_id', $student->id)->get();
        $total = $attendances->count();
        $hadir = $attendances->where('status', 'Hadir')->count();
        $sakit = $attendances->where('status', 'Sakit')->count();
        $izin = $attendances->where('status', 'Izin')->count();
        $alpa = $attendances->where('status', 'Alpa')->count();
        $percent = $total > 0 ? round(($hadir / $total) * 100) : 0;

        $todaySchedules = collect();
        if ($activeCycle && $student->class_id) {
            $todaySchedules = \App\Models\Schedule::with(['subject', 'teacher', 'room'])
                ->where('class_id', $student->class_id)
                ->where('block_cycle_id', $activeCycle->id)
                ->where('day', $todayName)
                ->orderBy('start_time')
                ->get();
        }

        $recentPermissions = \App\Models\PermissionRequest::where('student_id', $student->id)
            ->latest('permission_date')
            ->latest('id')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard siswa berhasil diambil.',
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'nis' => $student->nis,
                'kelas' => $student->schoolClass?->name ?? '-',
                'wali_kelas' => $student->schoolClass?->homeroomTeacher?->name ?? '-',
                'orang_tua' => $student->parent?->name ?? '-',
            ],
            'active_cycle' => $activeCycle ? [
                'id' => $activeCycle->id,
                'nama' => $activeCycle->cycle,
                'mulai' => $activeCycle->start_date,
                'selesai' => $activeCycle->end_date,
            ] : null,
            'today_name' => $todayName,
            'today_formatted' => $today->translatedFormat('l, d F Y'),
            'attendance_stats' => [
                'total' => $total,
                'hadir' => $hadir,
                'sakit' => $sakit,
                'izin' => $izin,
                'alpa' => $alpa,
                'percent' => $percent,
            ],
            'today_schedules' => $todaySchedules->map(function ($sch) {
                return [
                    'id' => $sch->id,
                    'mata_pelajaran' => $sch->subject?->name ?? '-',
                    'guru' => $sch->teacher?->name ?? '-',
                    'ruang' => $sch->room?->name ?? '-',
                    'jam_mulai' => substr($sch->start_time, 0, 5),
                    'jam_selesai' => substr($sch->end_time, 0, 5),
                    'jam_ke' => $sch->period_label,
                ];
            }),
            'recent_permissions' => $recentPermissions->map(function ($perm) {
                return [
                    'id' => $perm->id,
                    'tanggal' => $perm->permission_date,
                    'tanggal_selesai' => $perm->end_date ?? $perm->permission_date,
                    'durasi_hari' => $perm->duration_days,
                    'rentang_tanggal' => $perm->date_range_label,
                    'jenis' => $perm->type,
                    'alasan' => $perm->reason,
                    'dokumen' => $perm->document_path,
                    'status' => $perm->status,
                    'catatan_verifikasi' => $perm->verification_note,
                ];
            }),
        ]);
    });

    Route::get('/monitoring/dashboard', function (Request $request) {
        $user = $request->user();
        if ($user->role !== 'admin' && $user->role !== 'waka') {
            return response()->json([
                'success' => false,
                'message' => 'Akses terbatas untuk Waka Kurikulum / Admin.',
            ], 403);
        }

        $today = \Carbon\Carbon::today();
        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        $todayName = $days[$today->dayOfWeek];

        $totalStudents = \App\Models\Student::count();
        $pendingPermissionsCount = \App\Models\PermissionRequest::where('status', 'Pending')->count();
        $approvedPermissionsCount = \App\Models\PermissionRequest::where('status', 'Approved')->count();
        $rejectedPermissionsCount = \App\Models\PermissionRequest::where('status', 'Rejected')->count();

        $todayAttendancesCount = \App\Models\Attendance::whereDate('date', $today)->count();
        $presentCount = \App\Models\Attendance::whereDate('date', $today)->where('status', 'Hadir')->count();
        $sickCount = \App\Models\Attendance::whereDate('date', $today)->where('status', 'Sakit')->count();
        $permitCount = \App\Models\Attendance::whereDate('date', $today)->where('status', 'Izin')->count();
        $absentCount = \App\Models\Attendance::whereDate('date', $today)->where('status', 'Alpa')->count();

        $recentPermissions = \App\Models\PermissionRequest::with(['student.schoolClass', 'parent', 'verifiedBy'])
            ->latest('permission_date')
            ->latest('id')
            ->take(6)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard monitoring berhasil diambil.',
            'today_name' => $todayName,
            'today_formatted' => $today->translatedFormat('d M Y'),
            'today_full_formatted' => $todayName . ', ' . $today->translatedFormat('d M Y'),
            'total_students' => $totalStudents,
            'pending_permissions_count' => $pendingPermissionsCount,
            'approved_permissions_count' => $approvedPermissionsCount,
            'rejected_permissions_count' => $rejectedPermissionsCount,
            'today_attendances_count' => $todayAttendancesCount,
            'attendance_summary' => [
                'hadir' => $presentCount,
                'sakit' => $sickCount,
                'izin' => $permitCount,
                'alpa' => $absentCount,
            ],
            'recent_permissions' => $recentPermissions->map(function ($perm) {
                return [
                    'id' => $perm->id,
                    'siswa' => $perm->student?->name ?? '-',
                    'nis' => $perm->student?->nis ?? '-',
                    'kelas' => $perm->student?->schoolClass?->name ?? '-',
                    'tanggal' => $perm->permission_date,
                    'tanggal_selesai' => $perm->end_date ?? $perm->permission_date,
                    'durasi_hari' => $perm->duration_days,
                    'rentang_tanggal' => $perm->date_range_label,
                    'jenis' => $perm->type,
                    'alasan' => $perm->reason,
                    'dokumen' => $perm->document_path,
                    'status' => $perm->status,
                    'diverifikasi_oleh' => $perm->verifiedBy?->name ?? $perm->verified_by,
                    'catatan_verifikasi' => $perm->verification_note,
                ];
            }),
        ]);
    });

    Route::get('/attendances', [AttendanceApiController::class, 'index'])
        ->name('api.attendances.index');

    Route::get('/permission-requests', [PermissionRequestApiController::class, 'index'])
        ->name('api.permission-requests.index');

    Route::post('/permission-requests', [PermissionRequestApiController::class, 'store'])
        ->name('api.permission-requests.store');

    Route::get('/permission-requests/{id}', [PermissionRequestApiController::class, 'show'])
        ->name('api.permission-requests.show');

    Route::post('/permission-requests/{id}/approve', [PermissionRequestApiController::class, 'approve'])
        ->name('api.permission-requests.approve');

    Route::post('/permission-requests/{id}/reject', [PermissionRequestApiController::class, 'reject'])
        ->name('api.permission-requests.reject');

    Route::get('/schedules', [ScheduleApiController::class, 'index'])
        ->name('api.schedules.index');

    Route::post('/schedules', [ScheduleApiController::class, 'store'])
        ->name('api.schedules.store');

    Route::match(['put', 'patch'], '/schedules/{id}', [ScheduleApiController::class, 'update'])
        ->name('api.schedules.update');

    Route::delete('/schedules/{id}', [ScheduleApiController::class, 'destroy'])
        ->name('api.schedules.destroy');

    Route::get('/journals', [JournalApiController::class, 'index'])
        ->name('api.journals.index');

    Route::get('/journals/history', [JournalApiController::class, 'history'])
        ->name('api.journals.history');

    Route::post('/journals', [JournalApiController::class, 'store'])
        ->name('api.journals.store');
    
    Route::get('/journals/{id}', [JournalApiController::class, 'show'])
    ->name('api.journals.show');

    Route::match(
    ['put', 'patch'],
    '/journals/{id}',
    [JournalApiController::class, 'update'])
    ->name('api.journals.update');

    Route::delete('/journals/{id}', [JournalApiController::class, 'destroy'])
    ->name('api.journals.destroy');

    Route::get('/block-cycles', [BlockCycleApiController::class, 'index'])
    ->name('api.block-cycles.index');

    Route::post('/block-cycles/{id}/toggle-active', [BlockCycleApiController::class, 'toggleActive'])
    ->name('api.block-cycles.toggle-active');

    Route::post('/block-cycles/{id}/toggle-lock', [BlockCycleApiController::class, 'toggleLock'])
    ->name('api.block-cycles.toggle-lock');

});