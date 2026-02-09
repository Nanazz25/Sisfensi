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

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::middleware('auth')->group(function () {
    // School Settings
    Route::get('/school-settings', [SchoolSettingController::class, 'index'])->name('school-settings.index');
    Route::post('/school-settings', [SchoolSettingController::class, 'update'])->name('school-settings.update');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::prefix('users')->group(function () {
        Route::get('/admin', [UserController::class, 'admin'])->name('users.admin');
        Route::get('/guru', [UserController::class, 'guru'])->name('users.guru');
        Route::get('/siswa', [UserController::class, 'siswa'])->name('users.siswa');

        Route::get('/create', [UserController::class, 'create'])->name('users.create');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');

        Route::post('/', [UserController::class, 'store'])->name('users.store');
        Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::resource('jurusan', JurusanController::class);
    Route::resource('tahun-ajar', TahunAjarController::class)->except('show');
    Route::resource('rombongan-belajar', RombonganBelajarController::class);
    Route::resource('teachers', TeacherController::class)->except('show');
    Route::resource('peserta-didik', PesertaDidikController::class);
    Route::get('peserta-didik/{pesertaDidik}/photo', [PesertaDidikController::class, 'showPhoto'])->name('peserta-didik.photo');
    Route::resource('subjects', SubjectController::class);
    Route::resource('schedules', ScheduleController::class);
    Route::resource('school-locations', SchoolLocationController::class);

    Route::post(
        'rombongan-belajar/{rombel}/anggota',
        [AnggotaRombelController::class, 'store']
    )->name('rombels.anggota.store');

    Route::delete(
        'rombongan-belajar/{rombel}/anggota/{anggotaRombel}',
        [AnggotaRombelController::class, 'destroy']
    )->name('rombels.anggota.destroy');

    Route::get(
        '/ajax/siswa-by-rombel/{rombel}',
        [AnggotaRombelController::class, 'getSiswaByRombel']
    )->name('ajax.siswa.by.rombel');

    // Face Recognition (Registration)
    Route::prefix('face-recognition')->group(function () {
        Route::get('/enroll', [FaceRecognitionController::class, 'indexEnroll'])->name('face.enroll');
        Route::post('/enroll', [FaceRecognitionController::class, 'enroll'])->name('face.enroll.post');
    });

    // Attendance
    Route::prefix('attendance')->group(function () {
        Route::get('/scanner/{type?}', [AttendanceController::class, 'scanner'])->name('attendance.scanner');
        Route::post('/verify', [AttendanceController::class, 'verify'])->name('attendance.verify');
    });

    Route::prefix('laporan')->group(function () {
        Route::get(
            '/absensi-kelas',
            [AttendanceReportController::class, 'perKelas']
        )->name('laporan.absensi.kelas');

        Route::get(
            '/absensi-kelas/pdf',
            [AttendanceReportController::class, 'exportPdf']
        )->name('laporan.absensi.kelas.pdf');

        Route::get(
            '/absensi-kelas/excel',
            [AttendanceReportController::class, 'exportExcel']
        )->name('laporan.absensi.kelas.excel');
    });
});