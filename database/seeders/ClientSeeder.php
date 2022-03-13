<?php

namespace Database\Seeders;

use DateTime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('clients')->insert([
            'first_name' => 'Jarmila',
            'last_name' => 'Kučavíková',
            'date_born' => DateTime::createFromFormat('d.m.Y', '01.04.2000'),
            'sex' => 0,
            'height' => 165,
            'weight' => 66,
            'personal_information_number' => '0054011234',
            'insurance_company' => 213,
            'phone' => '+420 987 654 321',
            'street' => 'Jižní 6',
            'city' => 'Ostrov',
            'postal_code' => '205 40',
            'sport' => 'Gymnastika',
            'past_illnesses' => null,
            'injuries_suffered' => 'Zlomený kotník',
            'note' => '',
            'diag' => 'Rehabilitace zlomeného kotníku.',
            'created_at' => now()]);

        DB::table('clients')->insert([
            'first_name' => 'Matyáš',
            'last_name' => 'Rychlý',
            'date_born' => DateTime::createFromFormat('d.m.Y', '15.07.2015'),
            'sex' => 1,
            'height' => 105,
            'weight' => 41,
            'personal_information_number' => '1507156543',
            'insurance_company' => 213,
            'phone' => '+420 722 816 203',
            'street' => 'Hlavní třída 10',
            'city' => 'Brno',
            'postal_code' => '113 00',
            'sport' => 'Fotbal',
            'past_illnesses' => null,
            'injuries_suffered' => 'Zlomená pravá ruka',
            'note' => '',
            'diag' => 'Zvyšování zátěže pravé ruky po komplikované zlomenině.',
            'created_at' => now()]);
    }
}
