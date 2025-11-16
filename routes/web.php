<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileShareController;

Route::get('/', [FileShareController::class, 'index'])->name('files.index');

Route::post('/upload', [FileShareController::class, 'store'])
    ->name('files.upload')
    ->middleware('throttle:uploads'); // ver notas abajo

Route::get('/f/{slug}', [FileShareController::class, 'view'])->name('files.view');

Route::get('/d/{slug}', [FileShareController::class, 'download'])->name('files.download');


Route::delete('/f/{slug}', [FileShareController::class, 'destroy'])
    ->name('files.delete');

    Route::get('/view/{slug}', [FileShareController::class, 'streamInline'])->name('files.stream');

    Route::post('/password/{slug}', [FileShareController::class, 'validatePassword'])->name('files.validate-password');