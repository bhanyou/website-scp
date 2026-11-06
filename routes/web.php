<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\BlockCycleController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\PermissionRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\ClassPromotionController;
use App\Http\Controllers\LessonHourController;

// Login
Route::get('/login', [AuthController::class, 'showLogin'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('guest')
    ->name('login.process');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Lupa Password dengan OTP
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [
        ForgotPasswordController::class,
        'showRequestForm'
    ])->name('password.request');

    Route::post('/forgot-password/send-otp', [
        ForgotPasswordController::class,
        'sendOtp'
    ])->name('password.otp.send');

    Route::get('/forgot-password/verify-otp', [
        ForgotPasswordController::class,
        'showOtpForm'
    ])->name('password.otp.form');

    Route::post('/forgot-password/verify-otp', [
        ForgotPasswordController::class,
        'verifyOtp'
    ])->name('password.otp.verify');

    Route::get('/forgot-password/reset', [
        ForgotPasswordController::class,
        'showResetForm'
    ])->name('password.reset.form');

    Route::post('/forgot-password/reset', [
        ForgotPasswordController::class,
        'resetPassword'
    ])->name('password.reset');
});

// Halaman yang memerlukan login
Route::middleware('auth')->group(function () {

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    // Halaman utama (Arahkan otomatis ke dashboard sesuai role pengguna)
    Route::get('/', function () {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        return match ($user->role) {
            'waka' => redirect()->route('dashboard.waka'),
            'guru', 'bk' => redirect()->route('dashboard.guru'),
            'siswa', 'ortu' => redirect()->route('dashboard.siswa'),
            default => redirect()->route('login'),
        };
    })->name('home');

    // Dashboard
    Route::get('/dashboard/waka', [DashboardController::class, 'waka'])
        ->middleware('role:waka')
        ->name('dashboard.waka');

    Route::get('/dashboard/guru', [DashboardController::class, 'guru'])
        ->middleware('role:guru')
        ->name('dashboard.guru');

    Route::get('/dashboard/siswa', [DashboardController::class, 'siswa'])
        ->middleware('role:siswa,ortu')
        ->name('dashboard.siswa');

    // Master Data - Waka
    Route::get('/teachers/template/download', [TeacherController::class, 'downloadTemplate'])
        ->middleware('role:waka')
        ->name('teachers.template');

    Route::post('/teachers/import', [TeacherController::class, 'import'])
        ->middleware('role:waka')
        ->name('teachers.import');

    Route::delete('/teachers/bulk-destroy', [TeacherController::class, 'bulkDestroy'])
        ->middleware('role:waka')
        ->name('teachers.bulk-destroy');

    Route::delete('/teachers/destroy-all', [TeacherController::class, 'destroyAll'])
        ->middleware('role:waka')
        ->name('teachers.destroy-all');

    Route::resource('teachers', TeacherController::class)
        ->middleware('role:waka');

    Route::get('/students/template/download', [StudentController::class, 'downloadTemplate'])
        ->middleware('role:waka')
        ->name('students.template');

    Route::post('/students/import', [StudentController::class, 'import'])
        ->middleware('role:waka')
        ->name('students.import');

    Route::delete('/students/bulk-destroy', [StudentController::class, 'bulkDestroy'])
        ->middleware('role:waka')
        ->name('students.bulk-destroy');

    Route::delete('/students/destroy-all', [StudentController::class, 'destroyAll'])
        ->middleware('role:waka')
        ->name('students.destroy-all');

    Route::resource('students', StudentController::class)
        ->middleware('role:waka,bk');

    Route::get('/classes/template/download', [SchoolClassController::class, 'downloadTemplate'])
        ->middleware('role:waka')
        ->name('classes.template');

    Route::post('/classes/import', [SchoolClassController::class, 'import'])
        ->middleware('role:waka')
        ->name('classes.import');

    Route::post('/classes/clean-anomalies', [SchoolClassController::class, 'cleanAnomalies'])
        ->middleware('role:waka')
        ->name('classes.clean-anomalies');

    Route::resource('classes', SchoolClassController::class)
        ->middleware('role:waka');

    Route::get('/subjects/template/download', [SubjectController::class, 'downloadTemplate'])
        ->middleware('role:waka')
        ->name('subjects.template');

    Route::post('/subjects/import', [SubjectController::class, 'import'])
        ->middleware('role:waka')
        ->name('subjects.import');

    Route::resource('subjects', SubjectController::class)
        ->middleware('role:waka');

    Route::get('/rooms/template/download', [RoomController::class, 'downloadTemplate'])
        ->middleware('role:waka')
        ->name('rooms.template');

    Route::post('/rooms/import', [RoomController::class, 'import'])
        ->middleware('role:waka')
        ->name('rooms.import');

    Route::delete('/rooms/destroy-all', [RoomController::class, 'destroyAll'])
        ->middleware('role:waka')
        ->name('rooms.destroy-all');

    Route::resource('rooms', RoomController::class)
        ->middleware('role:waka');

    Route::resource('majors', MajorController::class)
        ->middleware('role:waka');

    // Tahun Ajaran & Semester - Waka
    Route::get('/academic-year', [AcademicYearController::class, 'index'])
        ->middleware('role:waka')
        ->name('academic-year.index');

    Route::post('/academic-year', [AcademicYearController::class, 'store'])
        ->middleware('role:waka')
        ->name('academic-year.store');

    Route::post('/academic-year/set-active', [AcademicYearController::class, 'setActive'])
        ->middleware('role:waka')
        ->name('academic-year.set-active');

    Route::delete('/academic-year', [AcademicYearController::class, 'destroy'])
        ->middleware('role:waka')
        ->name('academic-year.destroy');

    // Kenaikan Kelas & Kelulusan - Waka
    Route::get('/class-promotions', [ClassPromotionController::class, 'index'])
        ->middleware('role:waka')
        ->name('class-promotions.index');

    Route::post('/class-promotions/process', [ClassPromotionController::class, 'process'])
        ->middleware('role:waka')
        ->name('class-promotions.process');

    Route::post('/class-promotions/rollback', [ClassPromotionController::class, 'rollback'])
        ->middleware('role:waka')
        ->name('class-promotions.rollback');

    // Siklus Blok - Waka
    Route::post('/block-cycles/{id}/toggle-active', [
        BlockCycleController::class,
        'toggleActive'
    ])->middleware('role:waka')
      ->name('block-cycles.toggle-active');

    Route::post('/block-cycles/{id}/toggle-lock', [
        BlockCycleController::class,
        'toggleLock'
    ])->middleware('role:waka')
      ->name('block-cycles.toggle-lock');

    Route::resource('block-cycles', BlockCycleController::class)
        ->middleware('role:waka');

    // Jadwal CSV Import & Export (Khusus Waka)
    Route::get('/schedules/template', [ScheduleController::class, 'downloadTemplate'])
        ->name('schedules.template')
        ->middleware('role:waka');

    Route::get('/schedules/export', [ScheduleController::class, 'exportCsv'])
        ->name('schedules.export')
        ->middleware('role:waka');

    Route::post('/schedules/import', [ScheduleController::class, 'import'])
        ->name('schedules.import')
        ->middleware('role:waka');

    Route::delete('/schedules/destroy-all', [ScheduleController::class, 'destroyAll'])
        ->middleware('role:waka')
        ->name('schedules.destroy-all');

    // Jadwal
    Route::get('/schedules', [ScheduleController::class, 'index'])
        ->name('schedules.index');

    Route::resource('schedules', ScheduleController::class)
        ->except(['index', 'show'])
        ->middleware('role:waka');

    Route::get('/schedules/{schedule}', [ScheduleController::class, 'show'])
        ->name('schedules.show');

    // Alokasi Waktu Jam Pelajaran - Waka
    Route::get('/lesson-hours', [LessonHourController::class, 'index'])
        ->middleware('role:waka')
        ->name('lesson-hours.index');

    Route::post('/lesson-hours/update', [LessonHourController::class, 'update'])
        ->middleware('role:waka')
        ->name('lesson-hours.update');

    Route::post('/lesson-hours/toggle-monday-mode', [LessonHourController::class, 'toggleMondayMode'])
        ->middleware('role:waka')
        ->name('lesson-hours.toggle-monday-mode');

    Route::post('/lesson-hours/reset-defaults', [LessonHourController::class, 'resetDefaults'])
        ->middleware('role:waka')
        ->name('lesson-hours.reset-defaults');

    Route::get('/api/lesson-hours/{day}', [LessonHourController::class, 'getPeriodsApi'])
        ->name('lesson-hours.api');

    // Jurnal Mengajar Guru
    Route::get('/journals/print', [JournalController::class, 'teacherPrint'])
        ->middleware('role:guru')
        ->name('journals.print');

    Route::resource('journals', JournalController::class)
        ->middleware('role:guru');

    // Monitoring Jurnal Guru & Presensi - Waka
    Route::get('/waka/journals/late-monitoring', [JournalController::class, 'lateMonitoring'])
        ->middleware('role:waka')
        ->name('monitoring.keterlambatan');

    Route::get('/monitoring/keterlambatan', fn() => redirect()->route('monitoring.keterlambatan'))
        ->middleware('role:waka');

    Route::get('/waka/journals/print', [JournalController::class, 'wakaPrint'])
        ->middleware('role:waka')
        ->name('waka.journals.print');

    Route::get('/waka/journals', [JournalController::class, 'wakaIndex'])
        ->middleware('role:waka')
        ->name('waka.journals.index');

    Route::get('/waka/journals/{id}', [JournalController::class, 'wakaShow'])
        ->middleware('role:waka')
        ->name('waka.journals.show');

    // Rekap Kehadiran
    Route::get('/attendances/print', [AttendanceController::class, 'print'])
        ->name('attendances.print');

    Route::get('/attendances', [AttendanceController::class, 'index'])
        ->name('attendances.index');

    // Jadwal Pelajaran - Siswa & Orang Tua
    Route::get('/student/schedule', [ScheduleController::class, 'studentSchedule'])
        ->middleware('role:siswa,ortu')
        ->name('student.schedule');

    // Pengajuan Izin - Siswa & Orang Tua
    Route::get('/permission-requests', [
        PermissionRequestController::class,
        'index'
    ])->middleware('role:siswa,ortu')
      ->name('permission-requests.index');

    Route::get('/permission-requests/create', [
        PermissionRequestController::class,
        'create'
    ])->middleware('role:siswa,ortu')
      ->name('permission-requests.create');

    Route::post('/permission-requests', [
        PermissionRequestController::class,
        'store'
    ])->middleware('role:siswa,ortu')
      ->name('permission-requests.store');

    // Persetujuan Izin Siswa Kelas Binaan - Khusus Wali Kelas & Waka
    Route::get('/homeroom/permission-requests', [
        PermissionRequestController::class,
        'homeroomIndex'
    ])->middleware('role:guru')
      ->name('homeroom.permissions.index');

    Route::post('/homeroom/permission-requests/{id}/approve', [
        PermissionRequestController::class,
        'approve'
    ])->middleware('role:guru,waka')
      ->name('homeroom.permissions.approve');

    Route::post('/homeroom/permission-requests/{id}/reject', [
        PermissionRequestController::class,
        'reject'
    ])->middleware('role:guru,waka')
      ->name('homeroom.permissions.reject');

    Route::get('/permission-requests/{id}', [
        PermissionRequestController::class,
        'show'
    ])->middleware('role:siswa,ortu,waka,guru,bk')
      ->name('permission-requests.show');

    // Monitoring Izin Siswa - Waka Kurikulum & Guru BK
    Route::get('/monitoring/permission-requests', [
        PermissionRequestController::class,
        'verificationIndex'
    ])->middleware('role:waka,bk')
      ->name('permission-requests.verification');
});
