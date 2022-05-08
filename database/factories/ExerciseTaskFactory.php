<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExerciseTask>
 */
class ExerciseTaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'task_id' => 1,
            'exercise_id' => 1,
            'feedback' => null,
            'difficulty' => null,
            'repetitions' => 20,
            'duration' => 30
        ];
    }
}
