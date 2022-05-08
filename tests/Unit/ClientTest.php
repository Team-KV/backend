<?php

namespace Tests\Unit;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Artisan;
use Tests\CreatesApplication;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use CreatesApplication, DatabaseMigrations, WithoutMiddleware;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed');
        $this->token = $this->getToken();
    }

    public function test_create()
    {
        //Needs auth for log
        $response = $this->
        withHeader('Authorization', 'Bearer '.$this->token)->
        postJson('/api/client', [
            'first_name' => 'Jakub',
            'last_name' => 'Volák',
            'date_born' => '2000-05-09',
            'sex' => 1,
            'height' => null,
            'weight' => null,
            'personal_information_number' => null,
            'insurance_company' => 213,
            'phone' => '+420 722 618 302',
            'contact_email' => null,
            'street' => null,
            'city' => 'Ostrava',
            'postal_code' => null,
            'sport' => 'Hasičský sport',
            'past_illnesses' => null,
            'injuries_suffered' => 'Zlomenina levého zápěští',
            'anamnesis' => 'Špatné držení těla.',
            'note' => null,
            'no_czech' => false,
            'client_id' => null
        ]);

        $response->assertStatus(200)->
            assertJsonPath('Client.first_name', 'Jakub')->
            assertJsonPath('Client.last_name', 'Volák')->
            assertJsonPath('Client.date_born', '2000-05-09')->
            assertJsonPath('Client.sex', 1)->
            assertJsonPath('Client.height', null)->
            assertJsonPath('Client.weight', null)->
            assertJsonPath('Client.personal_information_number', null)->
            assertJsonPath('Client.insurance_company', 213)->
            assertJsonPath('Client.phone', '+420 722 618 302')->
            assertJsonPath('Client.contact_email', null)->
            assertJsonPath('Client.street', null)->
            assertJsonPath('Client.city', 'Ostrava')->
            assertJsonPath('Client.postal_code', null)->
            assertJsonPath('Client.sport', 'Hasičský sport')->
            assertJsonPath('Client.past_illnesses', null)->
            assertJsonPath('Client.injuries_suffered', 'Zlomenina levého zápěští')->
            assertJsonPath('Client.anamnesis', 'Špatné držení těla.')->
            assertJsonPath('Client.note', null)->
            assertJsonPath('Client.client_id', null);
    }

    public function test_update()
    {
        $client = $this->createClient();

        //Needs auth for log
        $response = $this->
        withHeader('Authorization', 'Bearer '.$this->token)->
        putJson('/api/client/'.$client->id, [
            'first_name' => 'Jakub',
            'last_name' => 'Volák',
            'date_born' => '2000-05-09',
            'sex' => 1,
            'height' => 178,
            'weight' => 61,
            'personal_information_number' => '0005095948',
            'insurance_company' => 213,
            'phone' => '+420 722 618 302',
            'contact_email' => 'jakub.volak@seznam.cz',
            'street' => null,
            'city' => 'Ostrava',
            'postal_code' => null,
            'sport' => 'Hasičský sport',
            'past_illnesses' => null,
            'injuries_suffered' => 'Zlomenina levého zápěští',
            'anamnesis' => 'Špatné držení těla.',
            'note' => null,
            'no_czech' => false,
            'client_id' => null
        ]);

        $response->assertStatus(200)->
        assertJsonPath('Client.first_name', 'Jakub')->
        assertJsonPath('Client.last_name', 'Volák')->
        assertJsonPath('Client.date_born', '2000-05-09')->
        assertJsonPath('Client.sex', 1)->
        assertJsonPath('Client.height', 178)->
        assertJsonPath('Client.weight', 61)->
        assertJsonPath('Client.personal_information_number', '0005095948')->
        assertJsonPath('Client.insurance_company', 213)->
        assertJsonPath('Client.phone', '+420 722 618 302')->
        assertJsonPath('Client.contact_email', 'jakub.volak@seznam.cz')->
        assertJsonPath('Client.street', null)->
        assertJsonPath('Client.city', 'Ostrava')->
        assertJsonPath('Client.postal_code', null)->
        assertJsonPath('Client.sport', 'Hasičský sport')->
        assertJsonPath('Client.past_illnesses', null)->
        assertJsonPath('Client.injuries_suffered', 'Zlomenina levého zápěští')->
        assertJsonPath('Client.anamnesis', 'Špatné držení těla.')->
        assertJsonPath('Client.note', null)->
        assertJsonPath('Client.client_id', null);
    }

    public function test_create_user()
    {
        $client = $this->createClientWithEmail();

        $response = $this->post('/api/client/'.$client->id.'/user');

        $response->assertStatus(200)->
            assertJsonPath('Client.user.email', 'jakub.volak@seznam.cz')->
            assertJsonPath('Client.user.role', 0)->
            assertJsonPath('Client.user.staff_id', null)->
            assertJsonPath('Client.user.client_id', 1);
    }

    public function test_delete()
    {
        $client = $this->createClient();

        $response = $this->
            withHeader('Authorization', 'Bearer '.$this->token)->
            delete('/api/client/'.$client->id);

        $response->assertStatus(204);

        $response = $this->get('/api/client/'.$client->id);

        $response->assertStatus(404);
    }

    private function createClient(): Client
    {
        return Factory::factoryForModel(Client::class)->create([
            'first_name' => 'Jakub',
            'last_name' => 'Volák',
            'date_born' => '2000-05-09',
            'sex' => 1,
            'height' => null,
            'weight' => null,
            'personal_information_number' => null,
            'insurance_company' => 213,
            'phone' => '+420 722 618 302',
            'contact_email' => null,
            'street' => null,
            'city' => 'Ostrava',
            'postal_code' => null,
            'sport' => 'Hasičský sport',
            'past_illnesses' => null,
            'injuries_suffered' => 'Zlomenina levého zápěští',
            'anamnesis' => 'Špatné držení těla.',
            'note' => null,
            'client_id' => null
        ]);
    }

    private function createClientWithEmail(): Client
    {
        return Factory::factoryForModel(Client::class)->create([
            'first_name' => 'Jakub',
            'last_name' => 'Volák',
            'date_born' => '2000-05-09',
            'sex' => 1,
            'height' => null,
            'weight' => null,
            'personal_information_number' => null,
            'insurance_company' => 213,
            'phone' => '+420 722 618 302',
            'contact_email' => 'jakub.volak@seznam.cz',
            'street' => null,
            'city' => 'Ostrava',
            'postal_code' => null,
            'sport' => 'Hasičský sport',
            'past_illnesses' => null,
            'injuries_suffered' => 'Zlomenina levého zápěští',
            'anamnesis' => 'Špatné držení těla.',
            'note' => null,
            'client_id' => null
        ]);
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
