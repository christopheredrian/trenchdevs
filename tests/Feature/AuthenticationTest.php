<?php

namespace Tests\Feature;

use App\Account;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $now = date('Y-m-d H:i:s');


        DB::table('application_types')->insert([
            'name' => 'ecommerce',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('accounts')->insert([
            'application_type_id' => 1,
            'owner_user_id' => null,
            'business_name' => 'Test Commerce',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $user = new User([
            'email' => 'test@email.com',
            'password' => Hash::make('123456'),
            'account_id' => 1,
            'role' => 'business_owner',
        ]);

        $user->save();
    }

    /** @test */
    public function it_will_register_a_user()
    {
        $response = $this->post('api/register', [
            'first_name' => 'Test',
            'last_name' => 'User1',
            'email' => 'test2@email.com',
            'password' => '123456',
            'role' => 'business_owner',
            'account_id' => 1,
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

        $response->assertStatus(200);
        $this->assertNotEmpty($response->assertJson([
            'email' => 'test@email.com',
        ]));

        $response = $this->withHeader('Authorization', "Bearer $accessToken")
            ->json('get', 'api/user', []);

        $response->assertStatus(200);
        $this->assertNotEmpty($response->assertJson([
            'email' => 'test@email.com',
        ]));

    }
}
