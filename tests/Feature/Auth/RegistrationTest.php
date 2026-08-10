<?php

use App\Models\Firm;
use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'firm_name' => 'Tan & Associates',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $firm = Firm::where('slug', 'tan-associates')->sole();
    $user = User::where('email', 'test@example.com')->sole();

    expect($firm->name)->toBe('Tan & Associates');
    expect($user->firm_id)->toBe($firm->id);
    expect($user->username)->toBe('testuser');
});

test('a firm name already in use gets a distinct slug', function () {
    Firm::factory()->create(['slug' => 'tan-associates']);

    $this->post(route('register.store'), [
        'firm_name' => 'Tan & Associates',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'test@example.com')->sole();

    expect($user->firm->slug)->toBe('tan-associates-2');
});

test('registration requires a firm name', function () {
    $response = $this
        ->from(route('register'))
        ->post(route('register.store'), [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

    $response->assertSessionHasErrors('firm_name');
    $this->assertGuest();
    expect(Firm::count())->toBe(0);
});
