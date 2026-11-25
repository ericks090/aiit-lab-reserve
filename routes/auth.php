<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // User Registration with mandatory face biometric recording
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    // Step 1: enter study program email
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'detectStudyProgram']);

    // Step 2: select user from study program + biometric face verification
    Route::get('login/select', [AuthenticatedSessionController::class, 'selectUser'])
        ->name('login.select');
    Route::post('login/verify-face', [AuthenticatedSessionController::class, 'verifyFace'])
        ->name('login.verify-face');
    Route::post('login/enroll-face', [AuthenticatedSessionController::class, 'enrollFace'])
        ->name('login.enroll-face');
    Route::post('login/authenticate', [AuthenticatedSessionController::class, 'authenticate'])
        ->name('login.authenticate');

    // Face registration setup for legacy accounts without face embedding
    Route::get('login/face-setup', [AuthenticatedSessionController::class, 'showFaceSetup'])
        ->name('login.face-setup');
    Route::post('login/face-setup', [AuthenticatedSessionController::class, 'storeFaceSetup'])
        ->name('login.face-setup.store');

    // Admin login — direct email + password
    Route::get('admin/login', fn() => view('auth.admin-login'))
        ->name('admin.login');
    Route::post('admin/login', [AuthenticatedSessionController::class, 'adminAuthenticate'])
        ->name('admin.login.authenticate');
});

Route::middleware('auth')->group(function () {
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
