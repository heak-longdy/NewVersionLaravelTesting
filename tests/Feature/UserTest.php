<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to login from user routes', function () {
    $this->get(route('users.index'))->assertRedirect(route('login'));
    $this->get(route('users.create'))->assertRedirect(route('login'));
    $this->post(route('users.store'), [])->assertRedirect(route('login'));
});

test('authenticated users can view the user list', function () {
    $user = User::factory()->create();
    User::factory()->count(3)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('users.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('users/Index')
        ->has('users.data', 4) // current user + 3 created
    );
});

test('users can be searched by name or email', function () {
    $user = User::factory()->create(['name' => 'SearchAdmin', 'email' => 'admin@search.com']);
    User::factory()->create(['name' => 'Alice Wonder', 'email' => 'alice@example.com']);
    User::factory()->create(['name' => 'Bob Builder', 'email' => 'bob@example.com']);

    $response = $this
        ->actingAs($user)
        ->get(route('users.index', ['search' => 'Alice']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('users/Index')
        ->has('users.data', 1)
        ->where('users.data.0.name', 'Alice Wonder')
    );

    $responseByEmail = $this
        ->actingAs($user)
        ->get(route('users.index', ['search' => 'bob@example.com']));

    $responseByEmail->assertOk();
    $responseByEmail->assertInertia(fn (Assert $page) => $page
        ->component('users/Index')
        ->has('users.data', 1)
        ->where('users.data.0.email', 'bob@example.com')
    );
});

test('users can be created with valid data', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('users.store'), [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $response->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
    ]);

    $created = User::where('email', 'john.doe@example.com')->first();
    expect(Hash::check('password123', $created->password))->toBeTrue();
});

test('user creation requires valid and unique email', function () {
    $user = User::factory()->create();
    $existing = User::factory()->create(['email' => 'existing@example.com']);

    $response = $this
        ->actingAs($user)
        ->post(route('users.store'), [
            'name' => 'Duplicate Email',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $response->assertSessionHasErrors('email');
});

test('authenticated user can view edit page', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('users.edit', $targetUser));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('users/Edit')
        ->has('user')
        ->where('user.name', $targetUser->name)
        ->where('user.email', $targetUser->email)
    );
});

test('user details can be updated', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $response = $this
        ->actingAs($user)
        ->put(route('users.update', $targetUser), [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

    $response->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', [
        'id' => $targetUser->id,
        'name' => 'New Name',
        'email' => 'new@example.com',
    ]);
});

test('user password can be updated if provided', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->put(route('users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

    $response->assertRedirect(route('users.index'));

    $targetUser->refresh();
    expect(Hash::check('newpassword123', $targetUser->password))->toBeTrue();
});

test('users can be deleted', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('users.destroy', $targetUser));

    $response->assertRedirect(route('users.index'));
    $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
});

test('authenticated user cannot delete their own account via user management', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('users.destroy', $user));

    $response->assertSessionHasErrors('message');
    $this->assertDatabaseHas('users', ['id' => $user->id]);
});
