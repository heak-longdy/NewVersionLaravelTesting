<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FileManagerController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::resource('customers', CustomerController::class);

    // FileManager Routes
    Route::get('file-manager', [FileManagerController::class, 'index'])->name('file-manager.index');
    Route::get('file-manager/api/files', [FileManagerController::class, 'api'])->name('file-manager.api');
    Route::post('file-manager', [FileManagerController::class, 'store'])->name('file-manager.store');
    Route::get('file-manager/{id}/download', [FileManagerController::class, 'download'])->name('file-manager.download');
    Route::put('file-manager/{fileItem}', [FileManagerController::class, 'update'])->name('file-manager.update');
    Route::delete('file-manager/{fileItem}', [FileManagerController::class, 'destroy'])->name('file-manager.destroy');
    Route::post('file-manager/{id}/restore', [FileManagerController::class, 'restore'])->name('file-manager.restore');
    Route::delete('file-manager/{id}/force-delete', [FileManagerController::class, 'forceDelete'])->name('file-manager.force-delete');
    Route::delete('file-manager/trash/empty', [FileManagerController::class, 'emptyTrash'])->name('file-manager.empty-trash');
});

require __DIR__.'/settings.php';
