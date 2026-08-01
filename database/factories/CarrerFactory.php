<?php

namespace Database\Factories;

use App\Models\Carrer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Carrer>
 */
class CarrerFactory extends Factory
{
    protected $model = Carrer::class;

    public function definition(): array
    {
        $title = fake()->jobTitle() . ' ' . fake()->numberBetween(1, 999);

        return [
            'title'       => $title,
            'slug'        => Str::slug($title),
            'description' => fake()->paragraph(),
            'salary'      => 'Rp 5.000.000 - Rp 10.000.000',
            'status'      => 'open',
            'time'        => fake()->randomElement(['Full Time', 'Part Time', 'Internship']),
            'apply_link'  => '#',
        ];
    }

    public function open(): static
    {
        return $this->state(['status' => 'open']);
    }

    public function closed(): static
    {
        return $this->state(['status' => 'closed']);
    }

    public function internship(): static
    {
        return $this->state(['time' => 'Internship']);
    }

    public function fullTime(): static
    {
        return $this->state(['time' => 'Full Time']);
    }
}
