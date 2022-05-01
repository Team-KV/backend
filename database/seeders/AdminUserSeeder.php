<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'email' => 'admin@test.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin'),
            'role' => 1,
            'staff_id' => 1,
            'client_id' => null]);
    }
}
