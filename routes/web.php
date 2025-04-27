<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\ChunkUploadController;

Route::get('/', function () {
    return view('welcome');
});

// TO SEE ALL THE DAILY REPORTS INDEX
Route::get('/daily-reports', [DailyReportController::class, 'index'])->name('daily-reports.index');

// NORMAL UPLOAD WITH AJAX
Route::get('/daily-reports/create', [DailyReportController::class, 'create'])->name('daily-reports.create');
Route::post('/daily-reports', [DailyReportController::class, 'store'])->name('daily-reports.store');

// FILES UPLOAD USING UPPY LIBRARY
Route::get('/daily-reports/uppy/create', [DailyReportController::class, 'uppyCreate'])->name('daily-reports.uppy.create');
Route::post('/daily-reports/uppy-upload', [DailyReportController::class, 'uppyUpload'])->name('daily-reports.uppy-upload');
Route::post('/daily-reports/uppy-store', [DailyReportController::class, 'storeUppy'])->name('daily-reports.store-uppy');    

// UPPY WITH CHUNK UPLOAD
Route::get('/daily-reports/uppy/chunks/create', [ChunkUploadController::class, 'createChunkUppy'])->name('daily-reports.uppy.chunks.create');
Route::post('/daily-reports/chunks/uppy-upload', [ChunkUploadController::class, 'uploadChunkUppy'])->name('daily-reports.chunks.uppy-upload');
Route::post('/daily-reports/chunks/uppy-store', [ChunkUploadController::class, 'storeChunkUppy'])->name('daily-reports.chunks.store-uppy'); 

// FILES UPLOAD USING RESUMABLE JS
Route::get('/daily-reports/resumable/create', [DailyReportController::class, 'resumableCreate'])->name('daily_reports.resumable.create');
Route::post('/daily-reports/resumable/upload', [DailyReportController::class, 'resumableUpload'])->name('daily_reports.resumable.upload');

// RESUMABKE JS WITH SEPARATED AND CHUNK UPLOAD 
Route::get('/daily-reports/resumable/chunks/create', [ChunkUploadController::class, 'createChunkResumable'])->name('daily_reports.resumable.chunks.create');
Route::post('/daily-reports/resumable/chunks/upload', [ChunkUploadController::class, 'uploadChunkResumable'])->name('daily_reports.resumable.chunks.upload');