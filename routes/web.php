<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileShareController;

// ── Página principal ──────────────────────────────────────────────────
Route::get('/', [FileShareController::class, 'index'])->name('files.index');

// ── Subida de archivo ─────────────────────────────────────────────────
Route::post('/upload', [FileShareController::class, 'store'])
    ->name('files.upload')
    ->middleware('throttle:uploads');

// ── Bloc de notas (NUEVO) ─────────────────────────────────────────────
Route::post('/notes', [FileShareController::class, 'storeNote'])->name('files.storeNote');

// ── Links (NUEVO) ─────────────────────────────────────────────────────
Route::post('/links', [FileShareController::class, 'storeLink'])->name('files.storeLink');

// ── Búsqueda por slug (NUEVO) ─────────────────────────────────────────
Route::get('/search', [FileShareController::class, 'search'])->name('files.search');

// ── Verificar disponibilidad de slug (NUEVO) ──────────────────────────
Route::get('/slug-check', [FileShareController::class, 'checkSlug'])->name('files.slugCheck');

// ── Ver archivo ───────────────────────────────────────────────────────
Route::get('/f/{slug}', [FileShareController::class, 'view'])->name('files.view');

// ── Descargar ─────────────────────────────────────────────────────────
Route::get('/d/{slug}', [FileShareController::class, 'download'])->name('files.download');

// ── Eliminar ──────────────────────────────────────────────────────────
Route::delete('/f/{slug}', [FileShareController::class, 'destroy'])->name('files.delete');

// ── Stream inline ─────────────────────────────────────────────────────
Route::get('/view/{slug}', [FileShareController::class, 'streamInline'])->name('files.stream');

// ── Validar contraseña ────────────────────────────────────────────────
Route::post('/password/{slug}', [FileShareController::class, 'validatePassword'])->name('files.validate-password');