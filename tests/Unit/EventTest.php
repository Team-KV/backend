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

class EventTest extends TestCase
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

    public function test_create()
    {
        $response = $this->
            postJson('/api/event', [
                'name' => 'Testovací událost',
                'start' => '2022-04-30 15:00:00',
                'end' => '2022-04-30 16:00:00',
                'note' => null,
                'event_type_id' => 1,
                'client_id' => 2,
                'staff_id' => 1
            ]);

        $response->assertStatus(200)->
            assertJsonPath('Event.name', 'Testovací událost')->
            assertJsonPath('Event.start', [
                'date' => '2022-04-30 15:00:00.000000',
                'timezone_type' => 3,
                'timezone' => 'UTC'])->
            assertJsonPath('Event.end',  [
                'date' => '2022-04-30 16:00:00.000000',
                'timezone_type' => 3,
                'timezone' => 'UTC'])->
            assertJsonPath('Event.note', null)->
            assertJsonPath('Event.event_type_id', 1)->
            assertJsonPath('Event.client_id', 2)->
            assertJsonPath('Event.staff_id', 1);
    }

    public function test_update()
    {
        $event = $this->createEvent();

        $response = $this->
            putJson('/api/event/'.$event->id, [
                'name' => 'Testovací událost',
                'start' => '2022-04-30 15:00:00',
                'end' => '2022-04-30 16:00:00',
                'note' => 'Poznámka',
                'event_type_id' => 1,
                'client_id' => 2,
                'staff_id' => 1
            ]);

        $response->assertStatus(200)->
            assertJsonPath('Event.name', 'Testovací událost')->
            assertJsonPath('Event.start', [
                'date' => '2022-04-30 15:00:00.000000',
                'timezone_type' => 3,
                'timezone' => 'UTC'])->
            assertJsonPath('Event.end',  [
                'date' => '2022-04-30 16:00:00.000000',
                'timezone_type' => 3,
                'timezone' => 'UTC'])->
            assertJsonPath('Event.note', 'Poznámka')->
            assertJsonPath('Event.event_type_id', 1)->
            assertJsonPath('Event.client_id', 2)->
            assertJsonPath('Event.staff_id', 1);
    }

    public function test_delete()
    {
        $event = $this->createEvent();

        $response = $this->delete('/api/event/'.$event->id);

        $response->assertStatus(204);

        $this->get('/api/event/'.$event->id)->
            assertStatus(404)->
            assertJson(fn (AssertableJson $json) => $json->has('message'));
    }

    public function test_check_free_time_1()
    {
        $this->createEvent();

        $this->postJson('/api/event', [
            'name' => 'Vnitřní událost',
            'start' => '2022-04-30 15:15:00',
            'end' => '2022-04-30 15:30:00',
            'note' => null,
            'event_type_id' => 1,
            'client_id' => 2,
            'staff_id' => 1])->
        assertStatus(409)->
        assertJson(fn (AssertableJson $json) => $json->has('message'));
    }

    public function test_check_free_time_2()
    {
        $this->createEvent();

        $this->postJson('/api/event', [
            'name' => 'Vnější událost',
            'start' => '2022-04-30 14:45:00',
            'end' => '2022-04-30 16:30:00',
            'note' => null,
            'event_type_id' => 1,
            'client_id' => 2,
            'staff_id' => 1])->
        assertStatus(409)->
        assertJson(fn (AssertableJson $json) => $json->has('message'));
    }

    public function test_check_free_time_3()
    {
        $this->createEvent();

        $this->postJson('/api/event', [
            'name' => 'Start uvnitř',
            'start' => '2022-04-30 15:45:00',
            'end' => '2022-04-30 16:30:00',
            'note' => null,
            'event_type_id' => 1,
            'client_id' => 2,
            'staff_id' => 1])->
        assertStatus(409)->
        assertJson(fn (AssertableJson $json) => $json->has('message'));
    }

    public function test_check_free_time_4()
    {
        $this->createEvent();

        $this->postJson('/api/event', [
            'name' => 'Konec uvnitř',
            'start' => '2022-04-30 14:45:00',
            'end' => '2022-04-30 15:30:00',
            'note' => null,
            'event_type_id' => 1,
            'client_id' => 2,
            'staff_id' => 1])->
        assertStatus(409)->
        assertJson(fn (AssertableJson $json) => $json->has('message'));
    }

    public function test_create_record()
    {
        $event = $this->createEvent();

        $response = $this->
            withHeader('Authorization', 'Bearer '.$this->token)->
            postJson('/api/event/'.$event->id.'/record', [
            'progress' => 2,
            'progress_note' => null,
            'exercise_note' => 'Lepší technika.',
            'text' => null]);

        $response->assertStatus(200)->
            assertJsonPath('Record.event_id', $event->id)->
            assertJsonPath('Record.progress', 2)->
            assertJsonPath('Record.progress_note', null)->
            assertJsonPath('Record.exercise_note', 'Lepší technika.')->
            assertJsonPath('Record.text', null);
    }

    public function test_exist_record()
    {
        $event = $this->createEvent();

        $this->createRecord($event->id);

        $this->withHeader('Authorization', 'Bearer '.$this->token)->
            postJson('/api/event/'.$event->id.'/record', [
            'progress' => 2,
            'progress_note' => null,
            'exercise_note' => 'Lepší technika.',
            'text' => null])->
        assertStatus(409)->
        assertJson(fn (AssertableJson $json) => $json->has('message'));
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
