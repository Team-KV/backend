<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'date_born' => $this->faker->date(),
            'sex' => 1,
            'height' => null,
            'weight' => null,
            'personal_information_number' => null,
            'insurance_company' => null,
            'phone' => '+420 123 456 789',
            'contact_email' => $this->faker->unique()->safeEmail(),
            'street' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'sport' => null,
            'past_illnesses' => null,
            'injuries_suffered' => null,
            'anamnesis' => null,
            'note' => null,
            'client_id' => null
        ];
    }
}
