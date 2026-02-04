<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FaceController;

Route::post('/simpan-wajah', [FaceController::class, 'store']);
