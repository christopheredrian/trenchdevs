<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $user = new User([
            'email' => 'test@email.com',
            'password' => '123456',
        ]);

        $user->save();
    }

    /** @test */
    public function it_will_register_a_user()
    {
        $response = $this->post('api/register', [
            'email' => 'test2@email.com',
            'password' => '123456'
        ]);

        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in'
        ]);
    }

    /** @test */
    public function it_will_log_a_user_in()
    {
        $response = $this->post('api/login', [
            'email' => 'test@email.com',
            'password' => '123456'
        ]);

        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in'
        ]);

        return $response['access_token'];
    }

    /** @test */
    public function it_will_not_log_an_invalid_user_in()
    {
        $response = $this->post('api/login', [
            'email' => 'test@email.com',
            'password' => 'notlegitpassword'
        ]);

        $response->assertJsonStructure([
            'error',
        ]);
    }

    /**
     * @test
     * @param string $accessToken
     * @depends it_will_log_a_user_in
     */
    public function it_will_get_user_details(string $accessToken)
    {
        $this->assertNotEmpty($accessToken);
        $response = $this->withHeader('Authorization', "Bearer $accessToken")
            ->json('post', 'api/me', []);
        
        $this->assertNotEmpty($response->assertJson([
            'email' => 'test@email.com',
        ]));
    }
}
