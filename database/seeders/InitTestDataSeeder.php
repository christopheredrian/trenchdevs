<?php

namespace Database\Seeders;

use App\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InitTestDataSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        DB::table('application_types')->insert([
            'name' => 'core',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('application_types')->insert([
            'name' => 'ecommerce',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('accounts')->insert([
            'application_type_id' => 1,
            'owner_user_id' => null,
            'business_name' => Account::TRENCHDEVS_BUSINESS_NAME,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('accounts')->insert([
            'application_type_id' => 2,
            'owner_user_id' => null,
            'business_name' => 'Test Commerce',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('users')->insert([
            'account_id' => 2,
            'role' => 'admin',
            'first_name' => 'Test',
            'is_active' => 1,
            'email_verified_at' => mysql_now(),
            'last_name' => 'Owner',
            'email' => 'tcommerce@test.com',
            'password' => Hash::make('password'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

    }
}
