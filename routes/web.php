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

Route::resource('/', DashboardController::class)->names([
    'index' => 'dashboard.index',
]);

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

Route::resource('teachers', TeacherController::class)->except('show');

Route::resource('peserta-didik', PesertaDidikController::class);

Route::resource('subjects', SubjectController::class);

Route::resource('schedules', ScheduleController::class);

Route::resource('school-locations', SchoolLocationController::class);

Route::get('/scan-wajah', function () {
    return view('scan-wajah');
})->name('scan-wajah');
