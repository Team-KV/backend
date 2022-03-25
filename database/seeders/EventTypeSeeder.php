<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('event_types')->insert([
            'name' => 'Úvodní schůzka']);

        DB::table('event_types')->insert([
            'name' => 'Schůzka']);

        DB::table('event_types')->insert([
            'name' => 'Školení']);
    }
}
