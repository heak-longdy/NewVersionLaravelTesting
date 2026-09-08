<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FileManagerController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::inertia('/', 'Welcome')->name('home');

// Storage Fallback Route (serves storage assets if requests reach PHP/Vercel serverless)
Route::get('storage/{path}', function (string $path) {
    // 1. Check in public/storage
    $publicPath = public_path('storage/'.$path);
    if (file_exists($publicPath) && ! is_dir($publicPath)) {
        return response()->file($publicPath);
    }

    // 2. Check in base repository storage (deployed in Vercel /var/task)
    $repoPath = base_path('storage/app/public/'.$path);
    if (file_exists($repoPath) && ! is_dir($repoPath)) {
        return response()->file($repoPath);
    }

    // 3. Check public disk
    if (Storage::disk('public')->exists($path)) {
        return Storage::disk('public')->response($path);
    }

    abort(404);
})->where('path', '.*')->name('storage.local');

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
