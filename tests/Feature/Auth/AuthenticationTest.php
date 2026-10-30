<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Volt;

beforeEach(function () {
    RateLimiter::clear('login:test@example.com|127.0.0.1');
    $this->withoutMiddleware([
        VerifyCsrfToken::class,
        ValidateCsrfToken::class,
    ]);
});

it('renders the login screen', function () {
    $this->get('/login')->assertOk();
});

it('authenticates a user with valid credentials', function () {
    $user = User::factory()->create(['password' => 'secret-password']);

    Volt::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'secret-password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

it('rejects an invalid password', function () {
    $user = User::factory()->create(['password' => 'secret-password']);

    Volt::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertHasErrors(['email']);

    $this->assertGuest();
});

it('logs the user out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/');

    $this->assertGuest();
});

it('logs in with one click when the demo is enabled', function () {
    config()->set('demo.enabled', true);

    $user = User::factory()->create([
        'email' => 'admin@gestordecontactos.test',
        'role' => UserRole::Admin,
    ]);

    $this->post('/demo/login/admin')->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('does not allow demo login for unknown roles or when disabled', function () {
    config()->set('demo.enabled', true);
    $this->post('/demo/login/hacker')->assertNotFound();

    config()->set('demo.enabled', false);
    $this->post('/demo/login/admin')->assertNotFound();
});

it('redirects guests away from the dashboard', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});
