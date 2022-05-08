<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Exercise;
use App\Models\ExerciseTask;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\CreatesApplication;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use CreatesApplication, DatabaseMigrations, WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed');
        Artisan::call('db:seed --class=ClientSeeder');
        Artisan::call('db:seed --class=ClientUserSeeder');
    }

    public function test_create()
    {
        $event = $this->createEvent();

        $response = $this->
        postJson('/api/task', [
            'text' => 'Cvičte správně.',
            'is_active' => true,
            'client_id' => 1,
            'event_id' => $event->id
        ]);

        $response->assertStatus(200)->
        assertJsonPath('Task.text', 'Cvičte správně.')->
        assertJsonPath('Task.is_active', true)->
        assertJsonPath('Task.client_id', 1)->
        assertJsonPath('Task.event_id', $event->id);
    }

    public function test_update()
    {
        $event = $this->createEvent();
        $task = $this->createTask(1, $event->id);

        $response = $this->
        putJson('/api/task/'.$task->id, [
            'text' => 'Cvičte pořádně.',
            'is_active' => false,
            'client_id' => 1,
            'event_id' => $event->id
        ]);

        $response->assertStatus(200)->
        assertJsonPath('Task.text', 'Cvičte pořádně.')->
        assertJsonPath('Task.is_active', false)->
        assertJsonPath('Task.client_id', 1)->
        assertJsonPath('Task.event_id', $event->id);
    }

    public function test_delete()
    {
        $event = $this->createEvent();
        $task = $this->createTask(1, $event->id);

        $this->delete('/api/task/'.$task->id)->
        assertStatus(204);

        $this->get('/api/task/'.$task->id)->
        assertStatus(404)->
        assertJson(fn (AssertableJson $json) => $json->has('message'));
    }

    public function test_change_status()
    {
        $event = $this->createEvent();
        $task = $this->createTask(1, $event->id);

        $this->patch('/api/task/'.$task->id.'/status')->
        assertStatus(200)->
        assertJsonPath('Task.is_active', false);
    }

    public function test_exercise_tasks()
    {
        $event = $this->createEvent();
        $exercise = $this->createExercise();
        $task = $this->createTask(1, $event->id);

        $response = $this->
        postJson('/api/task/'.$task->id.'/exercises', [
            'exerciseTasks' => [
                [
                    'exercise_id' => $exercise->id,
                    'feedback' => null,
                    'difficulty' => null,
                    'repetitions' => 20,
                    'duration' => 30
                ]
            ]
        ]);

        $response->assertStatus(200)->
        assertJsonPath('ExerciseTasks.0.feedback', null)->
        assertJsonPath('ExerciseTasks.0.difficulty', null)->
        assertJsonPath('ExerciseTasks.0.repetitions', 20)->
        assertJsonPath('ExerciseTasks.0.duration', 30)->
        assertJsonPath('ExerciseTasks.0.task_id', $task->id)->
        assertJsonPath('ExerciseTasks.0.exercise_id', $exercise->id);
    }

    public function test_update_exercise_task()
    {
        $event = $this->createEvent();
        $exercise = $this->createExercise();
        $task = $this->createTask(1, $event->id);
        $exerciseTask = $this->createExerciseTask($task->id, $exercise->id);

        //Need token to identify user role
        $response = $this->
        withHeader('Authorization', 'Bearer '.$this->getToken())->
        putJson('/api/exercise-task/'.$exerciseTask->id, [
            'repetitions' => 30,
            'duration' => 30,
            'exercise_id' => $exercise->id,
            'task_id' => $task->id
        ]);

        $response->assertStatus(200)->
        assertJsonPath('ExerciseTask.repetitions', 30)->
        assertJsonPath('ExerciseTask.duration', 30)->
        assertJsonPath('ExerciseTask.exercise_id', $exercise->id)->
        assertJsonPath('ExerciseTask.task_id', $task->id);
    }

    public function test_client_update_exercise_task()
    {
        $event = $this->createEvent();
        $exercise = $this->createExercise();
        $task = $this->createTask(1, $event->id);
        $exerciseTask = $this->createExerciseTask($task->id, $exercise->id);

        //Need token to identify user role
        $response = $this->
        withHeader('Authorization', 'Bearer '.$this->getTokenClient())->
        putJson('/api/exercise-task/'.$exerciseTask->id, [
            'feedback' => 'Bolest zad.',
            'difficulty' => 3
        ]);

        $response->assertStatus(200)->
        assertJsonPath('ExerciseTask.feedback', 'Bolest zad.')->
        assertJsonPath('ExerciseTask.difficulty', 3);
    }

    public function test_delete_exercise_task()
    {
        $event = $this->createEvent();
        $exercise = $this->createExercise();
        $task = $this->createTask(1, $event->id);
        $exerciseTask = $this->createExerciseTask($task->id, $exercise->id);

        $this->delete('/api/exercise-task/'.$exerciseTask->id)->
        assertStatus(204);

        $this->get('/api/task/'.$task->id)->
        assertStatus(200)->
        assertJsonPath('Task.exercises', []);
    }

    private function createTask($client_id, $event_id): Task
    {
        return Factory::factoryForModel(Task::class)->create([
            'text' => 'Cvičte správně.',
            'is_active' => true,
            'client_id' => $client_id,
            'event_id' => $event_id
        ]);
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

    private function createExercise(): Exercise
    {
        return Factory::factoryForModel(Exercise::class)->create([
            'name' => 'Testovací cvik',
            'description' => null,
            'url' => 'https://www.youtube.com',
            'category_id' => null
        ]);
    }

    private function createExerciseTask($task_id, $exercise_id): ExerciseTask
    {
        return Factory::factoryForModel(ExerciseTask::class)->create([
            'task_id' => $task_id,
            'exercise_id' => $exercise_id,
            'feedback' => null,
            'difficulty' => null,
            'repetitions' => 20,
            'duration' => 30
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

    private function getTokenClient(): String
    {
        $response = $this->postJson('/api/login', [
            'email' => 'client@test.com',
            'password' => 'client'
        ]);
        return $response['Token'];
    }
}
