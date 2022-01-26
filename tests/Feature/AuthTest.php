<?php

namespace Tests\Feature;

use App\Notifications\UserCreatedNotification;
use App\User;
use Illuminate\Support\Facades\Notification;

class AuthTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $users = User::whereIn('email', [
            'test.1@example.com',
            'test.2@example.com'
        ])->get();
        foreach ($users as $user)
            $user->delete();
    }

    // TODO : Test logout

    public function test_register_user_valid()
    {
        $response = $this->json('POST', '/api/auth/register', [
            'email' => 'test.1@example.com',
            'password' => 'MAJmin957'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'stripe_login_link',
                'token'
            ]);

        $this->json('POST', '/api/auth/register', [
            'email' => 'test.2@example.com',
            'password' => 'autre mot de passe9A',
            'add_to_newsletter' => true
        ])->assertStatus(200)
            ->assertJsonStructure([
                'stripe_login_link',
                'token'
            ]);

        $newUser1 = User::where('email', 'test.1@example.com')->first();
        $newUser2 = User::where('email', 'test.2@example.com')->first();

        Notification::assertSentTo($newUser1, UserCreatedNotification::class);
        Notification::assertSentTo($newUser2, UserCreatedNotification::class);

    }

    public function test_register_user_invalid_already_exists()
    {
        $this->json('POST', '/api/auth/register', [
            'email' => 'test.1@example.com',
            'password' => 'MAJmin957'
        ]);

        $response = $this->json('POST', '/api/auth/register', [
            'email' => 'test.1@example.com',
            'password' => 'MAJmin957'
        ]);

        $response->
        assertStatus(422);
    }

    public function test_register_user_invalid_email()
    {
        $response = $this->json('POST', '/api/auth/register', [
            'email' => 'invalid_email',
            'password' => 'MAJmin957'
        ]);

        $response->
        assertStatus(422);
    }

    public function test_login_user_valid()
    {
        $this->json('POST', '/api/auth/register', [
            'email' => 'test.1@example.com',
            'password' => 'MAJmin957'
        ]);

        $response = $this->json('POST', '/api/auth/login', [
            'email' => 'test.1@example.com',
            'password' => 'MAJmin957'
        ]);

        $response->
        assertStatus(200)
            ->assertJsonStructure([
                'token'
            ]);
    }

    public function test_login_user_invalid_password()
    {
        $this->json('POST', '/api/auth/register', [
            'email' => 'test.1@example.com',
            'password' => 'MAJmin957'
        ]);

        $response = $this->json('POST', '/api/auth/login', [
            'email' => 'test.1@example.com',
            'password' => 'WRONGpass1'
        ]);

        $response->
        assertStatus(401);
    }

    public function test_login_user_invalid_email()
    {
        $response = $this->json('POST', '/api/auth/login', [
            'email' => 'non.existant@example.com',
            'password' => 'passswrod377'
        ]);

        $response->
        assertStatus(401);
    }
}
