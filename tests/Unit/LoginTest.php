<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\CreatesApplication;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use CreatesApplication, DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed');
    }

    public function test_login()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'admin'
        ]);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('Token'));
    }

    public function test_bad_login()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'adminn@test.com',
            'password' => 'admin'
        ]);

        $response->assertStatus(401)->assertJson(fn (AssertableJson $json) => $json->has('message'));
    }

    public function test_logout()
    {
        $token = $this->getToken();

        $response1 = $this->withHeader('Authorization', 'Bearer '.$token)->get('/api/logout');

        $response1->assertStatus(204);
    }

    public function test_info()
    {
        $token = $this->getToken();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)->get('/api/info');

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->
        has('User', fn ($json) =>
        $json->hasAny('id', 'email', 'email_verified_at', 'role', 'staff_id', 'client_id', 'staff', 'client', 'created_at', 'updated_at')));

        $this->withHeader('Authorization', 'Bearer '.$token)->get('/api/logout');
    }

    public function test_update_user()
    {
        $token = $this->getToken();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)->putJson('/api/user/1', [
            'email' => 'adminn@test.com',
            'password' => '123456',
            'password_again' => '123456'
        ]);

        $response->assertStatus(200)->
            assertJsonPath('User.email', 'adminn@test.com');
    }

    private function getToken(): String
    {
        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'admin'
        ]);
        return $response['Token'];
    }
}
