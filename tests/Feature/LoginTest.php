<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use PhpParser\Node\Scalar\String_;
use Tests\TestCase;

class LoginTest extends TestCase
{
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

    private function getToken(): String
    {
        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'admin'
        ]);
        return $response['Token'];
    }
}
