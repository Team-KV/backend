<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Record>
 */
class RecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'progress' => 2,
            'progress_note' => null,
            'exercise_note' => 'Lepší technika.',
            'text' => null,
            'event_id' => 1
        ];
    }
}
