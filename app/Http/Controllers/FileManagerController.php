<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileManager\StoreFileItemRequest;
use App\Http\Requests\FileManager\UpdateFileItemRequest;
use App\Models\FileItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileManagerController extends Controller
{
    /**
     * Display a listing of files or recycle bin.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $tab = $request->input('tab', 'all');
        $search = $request->input('search');
        $category = $request->input('category', 'all');
        $sort = $request->input('sort', 'date_desc');
        $perPage = (int) $request->input('per_page', 24);

        $query = $tab === 'trash'
            ? FileItem::onlyTrashed()
            : FileItem::query();

        $query->search($search)->category($category);

        // Sorting
        match ($sort) {
            'date_asc' => $query->oldest('id'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'size_desc' => $query->orderBy('size', 'desc'),
            'size_asc' => $query->orderBy('size', 'asc'),
            default => $query->latest('id'),
        };

        $files = $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($files);
        }

        // Stats calculation
        $totalFiles = FileItem::count();
        $trashCount = FileItem::onlyTrashed()->count();
        $totalBytes = (int) FileItem::sum('size');
        $storageUsedFormatted = $this->formatBytes($totalBytes);

        return Inertia::render('file-manager/Index', [
            'files' => $files,
            'filters' => [
                'tab' => $tab,
                'search' => $search ?? '',
                'category' => $category,
                'sort' => $sort,
            ],
            'stats' => [
                'total_files' => $totalFiles,
                'trash_count' => $trashCount,
                'storage_used' => $totalBytes,
                'storage_used_formatted' => $storageUsedFormatted,
                'upload_max_filesize' => ini_get('upload_max_filesize') ?: '100M',
                'post_max_size' => ini_get('post_max_size') ?: '100M',
            ],
        ]);
    }

    /**
     * JSON API endpoint for selecting/browsing files inside modals (e.g. Customer Form).
     */
    public function api(Request $request): JsonResponse
    {
        $search = $request->input('search');
        $category = $request->input('category', 'image');
        $perPage = (int) $request->input('per_page', 20);

        $files = FileItem::query()
            ->search($search)
            ->category($category)
            ->latest('id')
            ->paginate($perPage);

        return response()->json($files);
    }

    /**
     * Store uploaded file(s) of any extension.
     */
    public function store(StoreFileItemRequest $request): RedirectResponse|JsonResponse
    {
        $userId = Auth::id();
        $uploadedItems = [];

        $rawFiles = [];
        if ($request->hasFile('files')) {
            $incoming = $request->file('files');
            $rawFiles = is_array($incoming) ? $incoming : [$incoming];
        } elseif ($request->hasFile('file')) {
            $rawFiles = [$request->file('file')];
        } elseif ($request->allFiles()) {
            foreach ($request->allFiles() as $fileOrFiles) {
                if (is_array($fileOrFiles)) {
                    $rawFiles = array_merge($rawFiles, $fileOrFiles);
                } elseif ($fileOrFiles instanceof UploadedFile) {
                    $rawFiles[] = $fileOrFiles;
                }
            }
        }

        if (empty($rawFiles)) {
            if ($request->wantsJson()) {
                return response()->json(['message' => __('No files were uploaded or files exceeded server limit.')], 422);
            }

            return back()->withErrors(['files' => __('Please select at least one valid file to upload.')]);
        }

        $uploadErrors = [];
        foreach ($rawFiles as $index => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            if (! $file->isValid()) {
                $errorReason = match ($file->getError()) {
                    UPLOAD_ERR_INI_SIZE => __(':filename exceeds the server maximum upload limit (:limit).', [
                        'filename' => $file->getClientOriginalName(),
                        'limit' => ini_get('upload_max_filesize'),
                    ]),
                    UPLOAD_ERR_FORM_SIZE => __(':filename exceeds the form maximum size limit.', [
                        'filename' => $file->getClientOriginalName(),
                    ]),
                    UPLOAD_ERR_PARTIAL => __(':filename was only partially uploaded.', [
                        'filename' => $file->getClientOriginalName(),
                    ]),
                    default => __(':filename failed to upload.', [
                        'filename' => $file->getClientOriginalName(),
                    ]),
                };
                $uploadErrors["files.{$index}"] = $errorReason;

                continue;
            }

            $originalName = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension());
            $mimeType = $file->getMimeType() ?: 'application/octet-stream';
            $size = $file->getSize() ?: 0;

            // Generate sanitized storage path in public disk
            $storedPath = $file->store('file-manager', 'public');

            // Default display name is original filename without extension, or request name for single upload
            $displayName = count($rawFiles) === 1 && filled($request->input('name'))
                ? (string) $request->input('name')
                : pathinfo($originalName, PATHINFO_FILENAME);

            $fileItem = FileItem::create([
                'user_id' => $userId,
                'name' => $displayName,
                'original_name' => $originalName,
                'file_path' => $storedPath,
                'disk' => 'public',
                'mime_type' => $mimeType,
                'extension' => $extension ?: null,
                'size' => $size,
                'description' => count($rawFiles) === 1 ? $request->input('description') : null,
            ]);

            $uploadedItems[] = $fileItem;
        }

        if (! empty($uploadErrors) && empty($uploadedItems)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => reset($uploadErrors),
                    'errors' => $uploadErrors,
                ], 422);
            }

            return back()->withErrors($uploadErrors);
        }

        $count = count($uploadedItems);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => __(':count file(s) uploaded successfully.', ['count' => $count]),
                'files' => $uploadedItems,
                'file' => $uploadedItems[0] ?? null,
                'errors' => $uploadErrors,
            ]);
        }

        Inertia::flash('toast', [
            'type' => empty($uploadErrors) ? 'success' : 'warning',
            'message' => empty($uploadErrors)
                ? __(':count file(s) uploaded successfully.', ['count' => $count])
                : __(':count file(s) uploaded, but some files had errors.', ['count' => $count]),
        ]);

        if (! empty($uploadErrors)) {
            return back()->withErrors($uploadErrors);
        }

        return back();
    }

    /**
     * Update file details (rename, description, or replacement file).
     */
    public function update(UpdateFileItemRequest $request, FileItem $fileItem): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            /** @var UploadedFile $newFile */
            $newFile = $request->file('file');

            // Delete old file from storage disk
            if (Storage::disk($fileItem->disk)->exists($fileItem->file_path)) {
                Storage::disk($fileItem->disk)->delete($fileItem->file_path);
            }

            $storedPath = $newFile->store('file-manager', 'public');
            $fileItem->file_path = $storedPath;
            $fileItem->original_name = $newFile->getClientOriginalName();
            $fileItem->extension = strtolower($newFile->getClientOriginalExtension()) ?: null;
            $fileItem->mime_type = $newFile->getMimeType() ?: 'application/octet-stream';
            $fileItem->size = $newFile->getSize() ?: 0;
        }

        $fileItem->name = $data['name'];
        $fileItem->description = $data['description'] ?? null;
        $fileItem->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('File updated successfully.'),
        ]);

        return back();
    }

    /**
     * Move file to Trash (Recycle Bin via soft delete).
     */
    public function destroy(FileItem $fileItem): RedirectResponse
    {
        $fileItem->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('File moved to Recycle Bin.'),
        ]);

        return back();
    }

    /**
     * Restore file from Trash.
     */
    public function restore(int $id): RedirectResponse
    {
        /** @var FileItem $fileItem */
        $fileItem = FileItem::onlyTrashed()->findOrFail($id);
        $fileItem->restore();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('File restored successfully.'),
        ]);

        return back();
    }

    /**
     * Permanently delete file from storage and database.
     */
    public function forceDelete(int $id): RedirectResponse
    {
        /** @var FileItem $fileItem */
        $fileItem = FileItem::onlyTrashed()->findOrFail($id);

        // Delete physical file from storage disk
        if (Storage::disk($fileItem->disk)->exists($fileItem->file_path)) {
            Storage::disk($fileItem->disk)->delete($fileItem->file_path);
        }

        $fileItem->forceDelete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('File permanently deleted.'),
        ]);

        return back();
    }

    /**
     * Empty entire Recycle Bin.
     */
    public function emptyTrash(): RedirectResponse
    {
        $trashedFiles = FileItem::onlyTrashed()->get();

        foreach ($trashedFiles as $file) {
            if (Storage::disk($file->disk)->exists($file->file_path)) {
                Storage::disk($file->disk)->delete($file->file_path);
            }
            $file->forceDelete();
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Recycle Bin emptied successfully.'),
        ]);

        return back();
    }

    /**
     * Download file with original filename.
     */
    public function download(int $id): SymfonyResponse
    {
        /** @var FileItem $fileItem */
        $fileItem = FileItem::withTrashed()->findOrFail($id);

        if (Storage::disk($fileItem->disk)->exists($fileItem->file_path)) {
            return Storage::disk($fileItem->disk)->download(
                $fileItem->file_path,
                $fileItem->original_name
            );
        }

        // Check in public/storage
        $publicPath = public_path('storage/'.$fileItem->file_path);
        if (file_exists($publicPath) && ! is_dir($publicPath)) {
            return response()->download($publicPath, $fileItem->original_name);
        }

        // Check in base repository storage (for deployed files on Vercel)
        $repoPath = base_path('storage/app/public/'.$fileItem->file_path);
        if (file_exists($repoPath) && ! is_dir($repoPath)) {
            return response()->download($repoPath, $fileItem->original_name);
        }

        abort(404, 'File not found on storage.');
    }

    /**
     * Format bytes to readable string.
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }
}
