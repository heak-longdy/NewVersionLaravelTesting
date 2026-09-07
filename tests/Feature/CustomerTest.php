<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to login from customer routes', function () {
    $this->get(route('customers.index'))->assertRedirect(route('login'));
    $this->get(route('customers.create'))->assertRedirect(route('login'));
    $this->post(route('customers.store'), [])->assertRedirect(route('login'));
});

test('authenticated users can view the customer list', function () {
    $user = User::factory()->create();
    Customer::factory()->count(3)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('customers.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('customers/Index')
        ->has('customers.data', 3)
    );
});

test('customers can be searched by name or email', function () {
    $user = User::factory()->create();
    Customer::factory()->create(['name' => 'Alice Wonderland', 'email' => 'alice@example.com']);
    Customer::factory()->create(['name' => 'Bob Builder', 'email' => 'bob@example.com']);

    $response = $this
        ->actingAs($user)
        ->get(route('customers.index', ['search' => 'Alice']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('customers/Index')
        ->has('customers.data', 1)
        ->where('customers.data.0.name', 'Alice Wonderland')
    );
});

test('customers can be filtered by status', function () {
    $user = User::factory()->create();
    Customer::factory()->create(['status' => 'active']);
    Customer::factory()->create(['status' => 'inactive']);

    $response = $this
        ->actingAs($user)
        ->get(route('customers.index', ['status' => 'inactive']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('customers/Index')
        ->has('customers.data', 1)
        ->where('customers.data.0.status', 'inactive')
    );
});

test('authenticated users can view the create customer page', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('customers.create'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('customers/Create')
    );
});

test('customers can be created with valid data', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('customers.store'), [
            'name' => 'Acme Client',
            'email' => 'client@acme.com',
            'phone' => '+1 555-0199',
            'company' => 'Acme Corp',
            'address' => '123 Business Way',
            'status' => 'active',
            'notes' => 'Preferred client',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('customers.index'));

    $this->assertDatabaseHas('customers', [
        'name' => 'Acme Client',
        'email' => 'client@acme.com',
        'company' => 'Acme Corp',
        'status' => 'active',
    ]);
});

test('customer creation requires name, email, and status', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('customers.store'), [
            'name' => '',
            'email' => 'invalid-email',
            'status' => 'not-a-valid-status',
        ]);

    $response->assertSessionHasErrors(['name', 'email', 'status']);
});

test('customer email must be unique upon creation', function () {
    $user = User::factory()->create();
    Customer::factory()->create(['email' => 'duplicate@example.com']);

    $response = $this
        ->actingAs($user)
        ->post(route('customers.store'), [
            'name' => 'Another User',
            'email' => 'duplicate@example.com',
            'status' => 'active',
        ]);

    $response->assertSessionHasErrors(['email']);
});

test('authenticated users can view the edit customer page', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('customers.edit', $customer));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('customers/Edit')
        ->where('customer.id', $customer->id)
    );
});

test('customer can be updated', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
        'status' => 'active',
    ]);

    $response = $this
        ->actingAs($user)
        ->put(route('customers.update', $customer), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'status' => 'inactive',
            'company' => 'New Corp',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('customers.index'));

    $customer->refresh();
    expect($customer->name)->toBe('Updated Name');
    expect($customer->email)->toBe('updated@example.com');
    expect($customer->status)->toBe('inactive');
    expect($customer->company)->toBe('New Corp');
});

test('customer update allows keeping the same email', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['email' => 'keep@example.com']);

    $response = $this
        ->actingAs($user)
        ->put(route('customers.update', $customer), [
            'name' => 'Updated Name Only',
            'email' => 'keep@example.com',
            'status' => 'active',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('customers.index'));
});

test('customer can be deleted', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('customers.destroy', $customer));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('customers.index'));

    $this->assertDatabaseMissing('customers', [
        'id' => $customer->id,
    ]);
});

test('customer can be created with an image', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

    $response = $this
        ->actingAs($user)
        ->post(route('customers.store'), [
            'name' => 'Image Customer',
            'email' => 'image_client@example.com',
            'status' => 'active',
            'image' => $file,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('customers.index'));

    $customer = Customer::where('email', 'image_client@example.com')->firstOrFail();
    expect($customer->image)->not->toBeNull();
    expect($customer->image)->toStartWith('/storage/customers/');

    $storedPath = str_replace('/storage/', '', $customer->image);
    Storage::disk('public')->assertExists($storedPath);
});

test('customer image can be replaced and removed', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $file1 = UploadedFile::fake()->image('old.jpg');
    $path1 = $file1->store('customers', 'public');

    $customer = Customer::factory()->create([
        'image' => '/storage/'.$path1,
    ]);

    // Test replacing image
    $file2 = UploadedFile::fake()->image('new.jpg');
    $response = $this
        ->actingAs($user)
        ->put(route('customers.update', $customer), [
            'name' => $customer->name,
            'email' => $customer->email,
            'status' => $customer->status,
            'image' => $file2,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('customers.index'));

    $customer->refresh();
    Storage::disk('public')->assertMissing($path1);

    $path2 = str_replace('/storage/', '', $customer->image);
    Storage::disk('public')->assertExists($path2);

    // Test removing image
    $response = $this
        ->actingAs($user)
        ->put(route('customers.update', $customer), [
            'name' => $customer->name,
            'email' => $customer->email,
            'status' => $customer->status,
            'remove_image' => true,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('customers.index'));

    $customer->refresh();
    expect($customer->image)->toBeNull();
    Storage::disk('public')->assertMissing($path2);
});
