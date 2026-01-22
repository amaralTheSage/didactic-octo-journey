<?php

use App\Enums\UserRole;

test('registration screen can be rendered', function () {
    $response = $this->get(route('filament.admin.auth.register'));

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'bio' => 'Bio teste',
        'role' => fake()->randomElement(UserRole::cases()),
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('filament.admin.pages.dashboard', absolute: false));
});
