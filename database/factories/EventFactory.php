<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => 'Testovací událost',
            'start' => $this->faker->dateTime('2022-04-29 16:00:00'),
            'end' => $this->faker->dateTime('2022-04-30 16:00:00'),
            'note' => null,
            'event_type_id' => 1,
            'client_id' => 1,
            'staff_id' => 1
        ];
    }
}
