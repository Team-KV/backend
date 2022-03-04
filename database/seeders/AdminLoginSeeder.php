<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminLoginSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('logins')->insert([
            'email' => 'admin@test.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin'),
            'role' => 1,
            'remember_token' => Str::random(10)]);
    }
}
