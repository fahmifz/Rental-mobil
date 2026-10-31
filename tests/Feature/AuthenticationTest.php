<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('a visitor can register only as a customer', function () {
    $this->post(route('register.store'), [
        'name' => 'Penyewa Baru',
        'email' => 'penyewa@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'admin',
    ])
        ->assertRedirect(route('account'));

    $user = User::query()->where('email', 'penyewa@example.com')->firstOrFail();

    expect($user->role)->toBe('pelanggan');
    expect(Hash::check('password', $user->password))->toBeTrue();
    $this->assertAuthenticatedAs($user);
});

test('a customer can log in with valid credentials', function () {
    $user = User::factory()->create();
    $user->role = 'pelanggan';
    $user->save();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertRedirect(route('account'));

    $this->assertAuthenticatedAs($user);
});

test('an admin is redirected to the dashboard after login', function () {
    $admin = User::factory()->create();
    $admin->role = 'admin';
    $admin->save();

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($admin);
});

test('login rejects invalid credentials', function () {
    $user = User::factory()->create();

    $this->from(route('login'))
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'not-the-password',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('an authenticated user can log out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();
});

test('guests are redirected to login from protected routes', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->get(route('mobil.index'))->assertRedirect(route('login'));
    $this->get(route('kategori-mobil.index'))->assertRedirect(route('login'));
    $this->get(route('account'))->assertRedirect(route('login'));
});

test('customers cannot access admin routes', function () {
    $customer = User::factory()->create();
    $customer->role = 'pelanggan';
    $customer->save();

    $this->actingAs($customer)->get(route('dashboard'))->assertForbidden();
    $this->actingAs($customer)->get(route('mobil.index'))->assertForbidden();
    $this->actingAs($customer)->get(route('kategori-mobil.index'))->assertForbidden();
    $this->actingAs($customer)->get(route('account'))->assertOk();
});

test('admins can access admin routes but not the customer account page', function () {
    $admin = User::factory()->create();
    $admin->role = 'admin';
    $admin->save();

    $this->actingAs($admin)->get(route('dashboard'))->assertOk();
    $this->actingAs($admin)->get(route('mobil.index'))->assertOk();
    $this->actingAs($admin)->get(route('kategori-mobil.index'))->assertOk();
    $this->actingAs($admin)->get(route('account'))->assertForbidden();
});

test('TailAdmin utility pages are restricted to administrators', function () {
    $customer = User::factory()->create();
    $customer->role = 'pelanggan';
    $customer->save();

    $this->get(route('calendar'))->assertRedirect(route('login'));
    $this->actingAs($customer)->get(route('calendar'))->assertForbidden();
    $this->actingAs($customer)->get(route('profile'))->assertForbidden();

    $admin = User::factory()->create();
    $admin->role = 'admin';
    $admin->save();

    $this->actingAs($admin)->get(route('calendar'))->assertOk();
    $this->actingAs($admin)->get(route('profile'))->assertOk();
});

test('an admin can view their own profile data', function () {
    $admin = User::factory()->create([
        'name' => 'Admin Awal Putra',
        'email' => 'admin@awalputra.test',
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->get(route('profile'))
        ->assertOk()
        ->assertSee('Admin Awal Putra')
        ->assertSee('admin@awalputra.test');
});

test('an admin can update their profile without changing their role', function () {
    $admin = User::factory()->create([
        'name' => 'Admin Lama',
        'email' => 'admin-lama@awalputra.test',
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->put(route('profile.update'), [
            'name' => 'Admin Baru',
            'email' => $admin->email,
            'role' => 'pelanggan',
        ])
        ->assertRedirect(route('profile'))
        ->assertSessionHas('success', 'Profil berhasil diperbarui.');

    $admin->refresh();

    expect($admin->name)->toBe('Admin Baru');
    expect($admin->email)->toBe('admin-lama@awalputra.test');
    expect($admin->role)->toBe('admin');
});

test('a profile email must be unique except for the current user', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@awalputra.test',
    ]);
    User::factory()->create([
        'role' => 'pelanggan',
        'email' => 'digunakan@awalputra.test',
    ]);

    $this->actingAs($admin)
        ->from(route('profile'))
        ->put(route('profile.update'), [
            'name' => 'Admin Awal Putra',
            'email' => 'digunakan@awalputra.test',
        ])
        ->assertRedirect(route('profile'))
        ->assertSessionHasErrors('email');

    expect($admin->fresh()->email)->toBe('admin@awalputra.test');

    $this->actingAs($admin)
        ->put(route('profile.update'), [
            'name' => 'Admin Awal Putra',
            'email' => 'admin@awalputra.test',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile'));
});
