<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminStampCorrectionRequestController;

Route::prefix('admin')
    ->middleware('auth')
    ->group(function () {

        Route::get('/', [AdminController::class, 'index'])
            ->name('admin.index');

        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])
            ->name('admin.attendance.show');

        Route::post('/attendance/{id}', [AdminAttendanceController::class, 'update'])
            ->name('admin.attendance.update');

        Route::get('/stamp_correction_request/list', [AdminStampCorrectionRequestController::class, 'index'])
            ->name('admin.requests.index');

        Route::get(
            'admin/stamp_correction_request/{id}',
            [AdminStampCorrectionRequestController::class, 'show']
        )->name('admin.requests.show');

        Route::post(
            'admin/stamp_correction_request/approve/{id}',
            [AdminStampCorrectionRequestController::class, 'approve']
        )->name('admin.requests.approve');

        Route::get('/staff', [AdminController::class, 'staffList'])
            ->name('admin.staff.list');

        Route::get('/staff/{user}/attendance', [AdminController::class, 'staffAttendance'])
            ->name('admin.staff.attendance');

        Route::get('/staff/{user}/attendance/csv', [AdminController::class, 'staffAttendanceCsv'])
            ->name('admin.staff.attendance.csv');
    });

Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])
    ->name('admin.login.post');

Route::post('/admin/logout', [AdminAuthController::class, 'logout'])
    ->name('admin.logout')
    ->middleware('auth');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified']);

Route::get('/mypage', function () {
    return view('mypage');
})->middleware(['auth', 'verified']);

/*
|--------------------------------------------------------------------------
| メール認証
|--------------------------------------------------------------------------
*/

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', '認証メールを再送しました');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

/*
|--------------------------------------------------------------------------
| 一般ユーザー機能
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    Route::post('/attendance/start', [AttendanceController::class, 'start'])
        ->name('attendance.start');

    Route::post('/attendance/break/start', [AttendanceController::class, 'breakStart'])
        ->name('attendance.break.start');

    Route::post('/attendance/break/end', [AttendanceController::class, 'breakEnd'])
        ->name('attendance.break.end');

    Route::post('/attendance/end', [AttendanceController::class, 'end'])
        ->name('attendance.end');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');

    Route::get('/attendance/{id}', [AttendanceController::class, 'show'])
        ->whereNumber('id')
        ->name('attendance.show');

    Route::post('/attendance/{id}/update', [AttendanceController::class, 'update'])
        ->whereNumber('id')
        ->name('attendance.update');

    Route::get('/requests', [StampCorrectionRequestController::class, 'index'])
        ->name('requests.index');
});