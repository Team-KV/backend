<?php

namespace Database\Seeders;

use DateTime;
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
            'date_born' => DateTime::createFromFormat('d.m.Y', '01.04.1985'),
            'sex' => 0,
            'height' => 165,
            'weight' => 66,
            'personal_information_number' => '8554011234',
            'insurance_company' => 213,
            'phone' => '+420 987 654 321',
            'contact_email' => 'jarmilka.kucavikova@email.cz',
            'street' => 'Jižní 6',
            'city' => 'Ostrov',
            'postal_code' => '205 40',
            'sport' => null,
            'past_illnesses' => null,
            'injuries_suffered' => 'Zlomený kotník',
            'note' => null,
            'diag' => 'Rehabilitace zlomeného kotníku.',
            'client_id' => null,
            'created_at' => now()]);

        DB::table('clients')->insert([
            'first_name' => 'Matyáš',
            'last_name' => 'Kučavík',
            'date_born' => DateTime::createFromFormat('d.m.Y', '15.07.2015'),
            'sex' => 1,
            'height' => 105,
            'weight' => 41,
            'personal_information_number' => '1507156543',
            'insurance_company' => 213,
            'phone' => null,
            'contact_email' => null,
            'street' => 'Jižní 6',
            'city' => 'Ostrov',
            'postal_code' => '205 40',
            'sport' => 'Fotbal',
            'past_illnesses' => null,
            'injuries_suffered' => 'Zlomená pravá ruka',
            'note' => null,
            'diag' => 'Zvyšování zátěže pravé ruky po komplikované zlomenině.',
            'client_id' => 1,
            'created_at' => now()]);
    }
}
