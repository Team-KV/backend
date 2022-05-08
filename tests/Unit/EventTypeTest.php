<?php

namespace Tests\Unit;

use App\Models\EventType;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\CreatesApplication;
use Tests\TestCase;

class EventTypeTest extends TestCase
{
    use CreatesApplication, DatabaseMigrations, WithoutMiddleware;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed');
    }

    public function test_create()
    {
        $response = $this->
        postJson('/api/event-type', [
            'name' => 'Testovací schůzka'
        ]);

        $response->assertStatus(200)->
        assertJsonPath('EventType.name', 'Testovací schůzka');
    }

    public function test_update()
    {
        $eventType = $this->createEventType();

        $response = $this->
        putJson('/api/event-type/'.$eventType->id, [
            'name' => 'Porada'
        ]);

        $response->assertStatus(200)->
        assertJsonPath('EventType.name', 'Porada');
    }

    public function test_delete()
    {
        $eventType = $this->createEventType();

        $response = $this->
        delete('/api/event-type/'.$eventType->id);

        $response->assertStatus(204);

        $this->get('/api/event-type/'.$eventType->id)->
            assertStatus(404)->
            assertJson(fn (AssertableJson $json) => $json->has('message'));
    }

    public function test_unique_name()
    {
        $this->createEventType();

        $response = $this->
        postJson('/api/event-type', [
            'name' => 'Testovací schůzka'
        ]);

        $response->assertStatus(409)->
            assertJson(fn (AssertableJson $json) => $json->has('message'));
    }

    private function createEventType(): EventType
    {
        return Factory::factoryForModel(EventType::class)->create([
            'name' => 'Testovací schůzka'
        ]);
    }
}
