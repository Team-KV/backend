<?php

namespace Tests\Unit;

use App\Models\Exercise;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\CreatesApplication;
use Tests\TestCase;

class ExerciseTest extends TestCase
{
    use CreatesApplication, DatabaseMigrations, WithoutMiddleware;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed');
        Storage::fake('exercises');
    }

    public function test_create()
    {
        $response = $this->
        postJson('/api/exercise/', [
            'name' => 'Testovací cvik',
            'description' => null,
            'url' => 'https://www.youtube.com',
            'category_id' => null]);

        $response->assertStatus(200)->
        assertJsonPath('Exercise.name', 'Testovací cvik')->
        assertJsonPath('Exercise.description', null)->
        assertJsonPath('Exercise.url', 'https://www.youtube.com')->
        assertJsonPath('Exercise.category_id', null);
    }

    public function test_update()
    {
        $exercise = $this->createExercise();

        $response = $this->
        putJson('/api/exercise/'.$exercise->id, [
            'name' => 'Testovací cvik',
            'description' => 'Popisek',
            'url' => null,
            'category_id' => null]);

        $response->assertStatus(200)->
        assertJsonPath('Exercise.name', 'Testovací cvik')->
        assertJsonPath('Exercise.description', 'Popisek')->
        assertJsonPath('Exercise.url', null)->
        assertJsonPath('Exercise.category_id', null);
    }

    public function test_delete()
    {
        $exercise = $this->createExercise();

        $this->
        delete('/api/exercise/'.$exercise->id)->
        assertStatus(204);

        $this->get('/api/exercise/'.$exercise->id)->
        assertStatus(404)->
        assertJson(fn (AssertableJson $json) => $json->has('message'));
    }

    private function createExercise(): Exercise
    {
        return Factory::factoryForModel(Exercise::class)->create([
            'name' => 'Testovací cvik',
            'description' => null,
            'url' => 'https://www.youtube.com',
            'category_id' => null
        ]);
    }
}
