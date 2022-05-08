<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Record;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\CreatesApplication;
use Tests\TestCase;

class RecordTest extends TestCase
{
    use CreatesApplication, DatabaseMigrations, WithoutMiddleware;

    private string $token;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed');
        Artisan::call('db:seed --class=ClientSeeder');
        $this->token = $this->getToken();
    }

    public function test_update()
    {
        $event = $this->createEvent();

        $record = $this->createRecord($event->id);

        //Token is required for log
        $response = $this->
        withHeader('Authorization', 'Bearer '.$this->token)->
        putJson('/api/record/'.$record->id, [
            'progress' => 1,
            'progress_note' => 'Zlepšení.',
            'exercise_note' => 'Lepší technika.',
            'text' => 'Poznámka']);

        $response->assertStatus(200)->
        assertJsonPath('Record.event_id', $event->id)->
        assertJsonPath('Record.progress', 1)->
        assertJsonPath('Record.progress_note', 'Zlepšení.')->
        assertJsonPath('Record.exercise_note', 'Lepší technika.')->
        assertJsonPath('Record.text', 'Poznámka');
    }

    public function test_delete()
    {
        $event = $this->createEvent();

        $record = $this->createRecord($event->id);

        //Token is required for log
        $this->withHeader('Authorization', 'Bearer '.$this->token)->
        delete('/api/record/'.$record->id)->
        assertStatus(204);

        $this->get('/api/record/'.$record->id)->
        assertStatus(404)->
        assertJson(fn (AssertableJson $json) => $json->has('message'));;
    }

    private function createEvent(): Event
    {
        return Factory::factoryForModel(Event::class)->create([
            'name' => 'Testovací událost',
            'start' => '2022-04-30 15:00:00',
            'end' => '2022-04-30 16:00:00',
            'note' => null,
            'event_type_id' => 1,
            'client_id' => 1,
            'staff_id' => 1
        ]);
    }

    private function createRecord($event_id): Record
    {
        return Factory::factoryForModel(Record::class)->create([
            'progress' => 2,
            'progress_note' => null,
            'exercise_note' => 'Lepší technika.',
            'text' => null,
            'event_id' => $event_id
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
