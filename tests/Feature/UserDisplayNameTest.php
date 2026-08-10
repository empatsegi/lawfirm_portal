<?php

use App\Models\User;
use App\Models\UserProfile;

test('the display name comes from the profile when there is one', function () {
    $user = User::factory()->create();

    UserProfile::factory()->create([
        'firm_id' => $user->firm_id,
        'user_id' => $user->id,
        'first_name' => 'Aisyah',
        'last_name' => 'Rahman',
    ]);

    expect($user->fresh()->name)->toBe('Aisyah Rahman');
});

test('the display name falls back to the username without a profile', function () {
    $user = User::factory()->create(['username' => 'arahman']);

    expect($user->name)->toBe('arahman');
});

test('the shared auth prop carries a display name', function () {
    $user = User::factory()->create(['username' => 'arahman']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('auth.user.name', 'arahman')
            ->where('auth.user.username', 'arahman')
        );
});
