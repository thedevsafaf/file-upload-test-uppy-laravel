<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DailyReportController;

Route::get('/', function () {
    return view('welcome');
});


//NORMAL UPLOAD WITH AJAX
Route::get('/daily-reports/create', [DailyReportController::class, 'create'])->name('daily-reports.create');
Route::post('/daily-reports', [DailyReportController::class, 'store'])->name('daily-reports.store');
Route::get('/daily-reports', [DailyReportController::class, 'index'])->name('daily-reports.index');

//FILES UPLOAD USING UPPY LIBRARY
Route::get('/daily-reports/uppy/create', [DailyReportController::class, 'uppyCreate'])->name('daily-reports.uppy.create');
Route::post('/daily-reports/uppy-upload', [DailyReportController::class, 'uppyUpload'])->name('daily-reports.uppy-upload');
Route::post('/daily-reports/uppy-store', [DailyReportController::class, 'storeUppy'])->name('daily-reports.store-uppy');    
