<?php

namespace Database\Factories;

use App\Models\Candidate;
use App\Models\Carrer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Candidate>
 */
class CandidateFactory extends Factory
{
    protected $model = Candidate::class;

    public function definition(): array
    {
        return [
            'carrer_id'    => Carrer::factory(),
            'name'         => fake()->name(),
            'email'        => fake()->unique()->safeEmail(),
            'phone'        => '081' . fake()->numerify('#########'),
            'resume'       => null,
            'cover_letter' => fake()->paragraph(),
            'status'       => 'pending',
        ];
    }
}
