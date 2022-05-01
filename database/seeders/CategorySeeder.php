<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->insert([
            'name' => 'Hlava',
            'color' => null]);

        DB::table('categories')->insert([
            'name' => 'Ruce',
            'color' => null]);

        DB::table('categories')->insert([
            'name' => 'Nohy',
            'color' => null]);

        DB::table('categories')->insert([
            'name' => 'Záda',
            'color' => 'FFA591']);
    }
}
