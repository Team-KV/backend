<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\CreatesApplication;
use Tests\TestCase;

class TagTest extends TestCase
{
    use CreatesApplication, DatabaseMigrations, WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed');
    }

    public function test_create()
    {
        $response = $this->postJson('/api/tag', [
            'name' => 'Testovací štítek',
            'color' => '#AA3939'
        ]);

        $response->
        assertStatus(200)->
        assertJsonPath('Tag.name', 'Testovací štítek')->
        assertJsonPath('Tag.color', '#AA3939');
    }

    public function test_update()
    {
        $tag = $this->createTag();

        $response = $this->putJson('/api/tag/'.$tag->id, [
            'name' => 'Kolenní vazy',
            'color' => null
        ]);

        $response->
        assertStatus(200)->
        assertJsonPath('Tag.name', 'Kolenní vazy')->
        assertJsonPath('Tag.color', null);
    }

    public function test_delete()
    {
        $tag = $this->createTag();

        $this->delete('/api/tag/'.$tag->id)->assertStatus(204);

        $this->get('/api/tag/'.$tag->id)->
        assertStatus(404)->
        assertJson(fn (AssertableJson $json) => $json->has('message'));
    }

    public function test_attach_and_detach()
    {
        $tag = $this->createTag();
        $client = $this->createClient();

        $response = $this->postJson('/api/client/'.$client->id.'/attach', [
            'tag_ids' => [$tag->id]
        ]);

        $response->
        assertStatus(200)->
        assertJsonPath('Client.tags.0.name', 'Testovací štítek')->
        assertJsonPath('Client.tags.0.color', '#AA3939');

        $response = $this->delete('/api/client/'.$client->id.'/detach/'.$tag->id);

        $response->
        assertStatus(200)->
        assertJsonPath('Client.tags', []);
    }

    public function test_search_by_tag()
    {
        $tag = $this->createTag();
        $client = $this->createClient();

        $this->postJson('/api/client/'.$client->id.'/attach', [
            'tag_ids' => [$tag->id]
        ]);

        $response = $this->get('/api/client/search/'.$tag->name);

        $response->
        assertStatus(200)->
        assertJsonPath('0.id', $client->id);
    }

    private function createTag(): Tag
    {
        return Factory::factoryForModel(Tag::class)->create([
            'name' => 'Testovací štítek',
            'color' => '#AA3939'
        ]);
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
}
