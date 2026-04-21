<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TahunAjarController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\RombonganBelajarController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\PesertaDidikController;
use App\Http\Controllers\AnggotaRombelController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SchoolLocationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FaceRecognitionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\SchoolSettingController;
use App\Http\Controllers\AttendancePermissionController;
use App\Http\Controllers\AssessmentCategoryController;
use App\Http\Controllers\HolidayController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    // Dashboard & Profile
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/password', [\App\Http\Controllers\ProfileController::class, 'editPassword'])->name('profile.password.edit');
    Route::post('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/dashboard/attendance-detail/{schedule}', [DashboardController::class, 'getAttendanceDetail'])->name('dashboard.attendance-detail');
    Route::get('/dashboard/attendance-chart', [DashboardController::class, 'getAttendanceChartData'])->name('dashboard.attendance-chart');

    // Attendance Permissions (Sakit/Izin)
    Route::resource('attendance-permissions', AttendancePermissionController::class);
    Route::post('attendance-permissions/{permission}/status/{status}', [AttendancePermissionController::class, 'updateStatus'])
        ->name('attendance-permissions.update-status');

    // --- KHUSUS ADMIN ---
    Route::middleware('role:admin')->group(function () {
        // Manual Attendance Adjustment
        Route::get('/attendance/manual', [AttendanceController::class, 'manualView'])->name('attendance.manual');
        Route::post('/attendance/manual-adjust', [AttendanceController::class, 'manualAdjust'])->name('attendance.manual-adjust');
        // School Settings
        Route::get('/school-settings', [SchoolSettingController::class, 'index'])->name('school-settings.index');
        Route::post('/school-settings', [SchoolSettingController::class, 'update'])->name('school-settings.update');
        Route::post('/school-settings/reset-face-logs', [SchoolSettingController::class, 'resetFaceLogs'])->name('school-settings.reset-face-logs');
        Route::post('/school-settings/sync-yesterday-alpha', [SchoolSettingController::class, 'syncYesterdayAlpha'])->name('school-settings.sync-yesterday-alpha');
        Route::post('/school-settings/purge-attachments', [SchoolSettingController::class, 'purgeOldPermissions'])->name('school-settings.purge-attachments');

        // User Management
        Route::prefix('users')->group(function () {
            Route::get('/admin', [UserController::class, 'admin'])->name('users.admin');
            Route::get('/guru', [UserController::class, 'guru'])->name('users.guru');
            Route::get('/siswa', [UserController::class, 'siswa'])->name('users.siswa');
            Route::get('/create', [UserController::class, 'create'])->name('users.create');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::post('/', [UserController::class, 'store'])->name('users.store');
            Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
            Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        // Master Data
        Route::resource('jurusan', JurusanController::class);
        Route::resource('tahun-ajar', TahunAjarController::class)->except('show');
        Route::resource('rombongan-belajar', RombonganBelajarController::class)->except(['index', 'show']);
        Route::resource('teachers', TeacherController::class)->except('show');
        Route::resource('peserta-didik', PesertaDidikController::class)->except('show');
        Route::resource('subjects', SubjectController::class);
        Route::resource('schedules', ScheduleController::class);
        Route::resource('school-locations', SchoolLocationController::class);

        // Anggota Rombel
        Route::post('rombongan-belajar/{rombel}/anggota', [AnggotaRombelController::class, 'store'])->name('rombels.anggota.store');
        Route::delete('rombongan-belajar/{rombel}/anggota/{anggotaRombel}', [AnggotaRombelController::class, 'destroy'])->name('rombels.anggota.destroy');
        Route::get('/ajax/siswa-by-rombel/{rombel}', [AnggotaRombelController::class, 'getSiswaByRombel'])->name('ajax.siswa.by.rombel');

        // Kategori Penilaian
        Route::resource('assessment-category', AssessmentCategoryController::class)->names('assessment_category');

        // Hari Libur
        Route::resource('holidays', HolidayController::class)->except(['show', 'create', 'edit']);
    });

    // --- ADMIN & GURU ---
    Route::middleware('role:admin,guru')->group(function () {
        // Akademik - Rombel Index
        Route::get('/rombongan-belajar', [RombonganBelajarController::class, 'index'])->name('rombongan-belajar.index');
        Route::get('/my-schedules', [ScheduleController::class, 'mySchedules'])->name('schedules.my_schedules');

        // Peserta Didik Detail & Photo (Accessible by Admin & Walas)
        Route::get('peserta-didik/{peserta_didik}', [PesertaDidikController::class, 'show'])->name('peserta-didik.show');
        Route::get('peserta-didik/{pesertaDidik}/photo', [PesertaDidikController::class, 'showPhoto'])->name('peserta-didik.photo');

        // Face Recognition
        Route::prefix('face-recognition')->group(function () {
            Route::get('/enroll', [FaceRecognitionController::class, 'indexEnroll'])->name('face.enroll');
            Route::post('/enroll', [FaceRecognitionController::class, 'enroll'])->name('face.enroll.post');
        });

        // Laporan (Admin & Guru Only)
        Route::prefix('laporan')->group(function () {
            Route::get('/absensi-kelas', [AttendanceReportController::class, 'perKelas'])->name('laporan.absensi.kelas');
            Route::get('/absensi-kelas/pdf', [AttendanceReportController::class, 'exportPdf'])->name('laporan.absensi.kelas.pdf');
            Route::get('/absensi-kelas/excel', [AttendanceReportController::class, 'exportExcel'])->name('laporan.absensi.kelas.excel');
            Route::get('/absensi-mapel', [AttendanceReportController::class, 'perMapel'])->name('laporan.absensi.mapel');
            Route::get('/absensi-mapel/pdf', [AttendanceReportController::class, 'exportMapelPdf'])->name('laporan.absensi.mapel.pdf');
            Route::get('/absensi-mapel/excel', [AttendanceReportController::class, 'exportMapelExcel'])->name('laporan.absensi.mapel.excel');
            Route::get('/absensi-mapel/detail', [AttendanceReportController::class, 'getDetailMapel'])->name('laporan.absensi.mapel.detail');
        });

        // Penilaian (Admin & Guru)
        Route::prefix('assessment')->group(function () {
            Route::get('/', [\App\Http\Controllers\AssessmentController::class, 'index'])->name('assessment.index');
            Route::get('/create', [\App\Http\Controllers\AssessmentController::class, 'create'])->name('assessment.create');
            Route::post('/', [\App\Http\Controllers\AssessmentController::class, 'store'])->name('assessment.store');
        });
    });

    // Assessment Detail (Siswa can see their own)
    Route::get('/assessment/{id}', [\App\Http\Controllers\AssessmentController::class, 'show'])->name('assessment.show');

    // --- SEMUA ROLE (Scan Presence & Info Kelas) ---
    Route::middleware('role:admin,guru,siswa')->group(function () {
        Route::prefix('attendance')->group(function () {
            Route::get('/scanner/{type?}', [AttendanceController::class, 'scanner'])->name('attendance.scanner');
            Route::post('/verify', [AttendanceController::class, 'verify'])->name('attendance.verify');
        });

        // Route for Calendar Data API
        Route::get('/api/attendance-calendar/{peserta_didik_id?}', [AttendanceController::class, 'getCalendarData'])->name('attendance.calendar');

        // Show Rombel (Students can see their own class)
        Route::get('/rombongan-belajar/{rombongan_belajar}', [RombonganBelajarController::class, 'show'])
            ->name('rombongan-belajar.show');
    });

    // Integrity & Flexibility Module
    Route::prefix('integrity')->group(function () {
        // Admin Routes
        Route::middleware('role:admin')->group(function () {
            Route::get('/admin', [\App\Http\Controllers\IntegrityController::class, 'adminIndex'])->name('integrity.admin.index');
            Route::post('/rules', [\App\Http\Controllers\IntegrityController::class, 'storeRule'])->name('integrity.rules.store');
            Route::put('/rules/{rule}', [\App\Http\Controllers\IntegrityController::class, 'updateRule'])->name('integrity.rules.update');
            Route::delete('/rules/{rule}', [\App\Http\Controllers\IntegrityController::class, 'destroyRule'])->name('integrity.rules.destroy');
            Route::post('/items', [\App\Http\Controllers\IntegrityController::class, 'storeItem'])->name('integrity.items.store');
            Route::put('/items/{item}', [\App\Http\Controllers\IntegrityController::class, 'updateItem'])->name('integrity.items.update');
            Route::delete('/items/{item}', [\App\Http\Controllers\IntegrityController::class, 'destroyItem'])->name('integrity.items.destroy');
        });

        // User Routes
        Route::middleware('role:siswa,guru,admin')->group(function () {
            Route::get('/wallet', [\App\Http\Controllers\IntegrityController::class, 'userIndex'])->name('integrity.user.index');
            Route::post('/market/buy/{item}', [\App\Http\Controllers\IntegrityController::class, 'buyItem'])->name('integrity.market.buy');
            Route::post('/manual/give', [\App\Http\Controllers\IntegrityController::class, 'givePointManually'])->name('integrity.manual.give');
            Route::post('/manual/reset-today', [\App\Http\Controllers\IntegrityController::class, 'resetPointsToday'])->name('integrity.manual.reset_today');
        });
    });
});

