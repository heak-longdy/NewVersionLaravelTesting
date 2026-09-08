<?php

use App\Models\FileItem;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to login from file manager routes', function () {
    $this->get(route('file-manager.index'))->assertRedirect(route('login'));
    $this->post(route('file-manager.store'), [])->assertRedirect(route('login'));
});

test('authenticated users can view the file manager index', function () {
    $user = User::factory()->create();
    FileItem::factory()->count(3)->create(['user_id' => $user->id]);
    FileItem::factory()->trashed()->count(2)->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('file-manager.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('file-manager/Index')
        ->has('files.data', 3)
        ->where('stats.total_files', 3)
        ->where('stats.trash_count', 2)
    );
});

test('users can upload any file extension', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    // Test multiple arbitrary extensions: pdf, zip, exe, custom, json
    $files = [
        UploadedFile::fake()->create('contract.pdf', 500, 'application/pdf'),
        UploadedFile::fake()->create('archive.zip', 1200, 'application/zip'),
        UploadedFile::fake()->create('setup.exe', 3000, 'application/octet-stream'),
        UploadedFile::fake()->create('data.xyz', 100, 'application/octet-stream'),
        UploadedFile::fake()->create('payload.json', 50, 'application/json'),
    ];

    $response = $this
        ->actingAs($user)
        ->post(route('file-manager.store'), [
            'files' => $files,
        ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    expect(FileItem::count())->toBe(5);

    $pdf = FileItem::where('original_name', 'contract.pdf')->first();
    expect($pdf)->not->toBeNull()
        ->and($pdf->extension)->toBe('pdf')
        ->and($pdf->category)->toBe('document');
    Storage::disk('public')->assertExists($pdf->file_path);

    $xyz = FileItem::where('original_name', 'data.xyz')->first();
    expect($xyz)->not->toBeNull()
        ->and($xyz->extension)->toBe('xyz')
        ->and($xyz->category)->toBe('other');
    Storage::disk('public')->assertExists($xyz->file_path);
});

test('users can update file display name and description', function () {
    $user = User::factory()->create();
    $file = FileItem::factory()->create([
        'user_id' => $user->id,
        'name' => 'Old Name',
        'description' => 'Old description',
    ]);

    $response = $this
        ->actingAs($user)
        ->put(route('file-manager.update', $file), [
            'name' => 'Updated Proposal Title',
            'description' => 'New updated description',
        ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $file->refresh();
    expect($file->name)->toBe('Updated Proposal Title')
        ->and($file->description)->toBe('New updated description');
});

test('users can replace file content', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $oldFile = UploadedFile::fake()->create('old.pdf', 100);
    $path = $oldFile->store('file-manager', 'public');

    $fileItem = FileItem::factory()->create([
        'user_id' => $user->id,
        'name' => 'Document',
        'original_name' => 'old.pdf',
        'file_path' => $path,
        'extension' => 'pdf',
        'disk' => 'public',
    ]);

    Storage::disk('public')->assertExists($path);

    $replacement = UploadedFile::fake()->create('brand_new.docx', 250);

    $response = $this
        ->actingAs($user)
        ->put(route('file-manager.update', $fileItem), [
            'name' => 'Brand New Document',
            'file' => $replacement,
        ]);

    $response->assertRedirect();
    $fileItem->refresh();

    expect($fileItem->name)->toBe('Brand New Document')
        ->and($fileItem->original_name)->toBe('brand_new.docx')
        ->and($fileItem->extension)->toBe('docx');

    // Old file is deleted, new file exists
    Storage::disk('public')->assertMissing($path);
    Storage::disk('public')->assertExists($fileItem->file_path);
});

test('users can soft delete file to recycle bin', function () {
    $user = User::factory()->create();
    $file = FileItem::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->delete(route('file-manager.destroy', $file));

    $response->assertRedirect();

    expect(FileItem::count())->toBe(0)
        ->and(FileItem::withTrashed()->count())->toBe(1);

    $trashed = FileItem::onlyTrashed()->first();
    expect($trashed->id)->toBe($file->id);
});

test('trashed files appear in trash tab and not in active tab', function () {
    $user = User::factory()->create();
    $active = FileItem::factory()->create(['user_id' => $user->id, 'name' => 'Active File']);
    $trashed = FileItem::factory()->trashed()->create(['user_id' => $user->id, 'name' => 'Trashed File']);

    // Active tab
    $responseActive = $this
        ->actingAs($user)
        ->get(route('file-manager.index', ['tab' => 'all']));

    $responseActive->assertOk();
    $responseActive->assertInertia(fn (Assert $page) => $page
        ->has('files.data', 1)
        ->where('files.data.0.name', 'Active File')
    );

    // Trash tab
    $responseTrash = $this
        ->actingAs($user)
        ->get(route('file-manager.index', ['tab' => 'trash']));

    $responseTrash->assertOk();
    $responseTrash->assertInertia(fn (Assert $page) => $page
        ->has('files.data', 1)
        ->where('files.data.0.name', 'Trashed File')
    );
});

test('users can restore file from trash', function () {
    $user = User::factory()->create();
    $file = FileItem::factory()->trashed()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->post(route('file-manager.restore', $file->id));

    $response->assertRedirect();

    expect(FileItem::count())->toBe(1)
        ->and(FileItem::onlyTrashed()->count())->toBe(0);
});

test('users can permanently delete file from trash', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $fakeFile = UploadedFile::fake()->create('delete_me.zip', 100);
    $path = $fakeFile->store('file-manager', 'public');

    $file = FileItem::factory()->trashed()->create([
        'user_id' => $user->id,
        'file_path' => $path,
        'disk' => 'public',
    ]);

    Storage::disk('public')->assertExists($path);

    $response = $this
        ->actingAs($user)
        ->delete(route('file-manager.force-delete', $file->id));

    $response->assertRedirect();

    expect(FileItem::withTrashed()->count())->toBe(0);
    Storage::disk('public')->assertMissing($path);
});

test('users can empty the entire recycle bin', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $path1 = UploadedFile::fake()->create('file1.pdf', 100)->store('file-manager', 'public');
    $path2 = UploadedFile::fake()->create('file2.png', 100)->store('file-manager', 'public');

    FileItem::factory()->trashed()->create(['user_id' => $user->id, 'file_path' => $path1, 'disk' => 'public']);
    FileItem::factory()->trashed()->create(['user_id' => $user->id, 'file_path' => $path2, 'disk' => 'public']);
    FileItem::factory()->create(['user_id' => $user->id, 'name' => 'Still Active']);

    Storage::disk('public')->assertExists($path1);
    Storage::disk('public')->assertExists($path2);

    $response = $this
        ->actingAs($user)
        ->delete(route('file-manager.empty-trash'));

    $response->assertRedirect();

    expect(FileItem::count())->toBe(1)
        ->and(FileItem::onlyTrashed()->count())->toBe(0);

    Storage::disk('public')->assertMissing($path1);
    Storage::disk('public')->assertMissing($path2);
});

test('users can download files', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $fakeFile = UploadedFile::fake()->create('my_report.pdf', 200);
    $path = $fakeFile->store('file-manager', 'public');

    $file = FileItem::factory()->create([
        'user_id' => $user->id,
        'original_name' => 'my_report.pdf',
        'file_path' => $path,
        'disk' => 'public',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('file-manager.download', $file->id));

    $response->assertOk();
    $response->assertDownload('my_report.pdf');
});

test('api endpoint returns active files for picker', function () {
    $user = User::factory()->create();

    FileItem::factory()->image()->create(['name' => 'Avatar 1']);
    FileItem::factory()->image()->create(['name' => 'Banner Image']);
    FileItem::factory()->create(['name' => 'Text File', 'extension' => 'txt', 'mime_type' => 'text/plain']);

    $response = $this
        ->actingAs($user)
        ->getJson(route('file-manager.api', ['category' => 'image']));

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

test('customers can be created with image_url selected from file manager', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('customers.store'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'status' => 'active',
            'image_url' => '/storage/file-manager/sample_avatar.png',
        ]);

    $response->assertRedirect(route('customers.index'));
    $this->assertDatabaseHas('customers', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'image' => '/storage/file-manager/sample_avatar.png',
    ]);
});
