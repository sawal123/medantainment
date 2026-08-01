<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Blog>
 */
class BlogFactory extends Factory
{
    protected $model = Blog::class;

    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->randomNumber(4),
            'content' => '<p>'.fake()->paragraphs(3, true).'</p>',
            'image' => null,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'seo_title' => Str::limit($title, 60),
            'seo_description' => fake()->sentence(20),
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft', 'published_at' => null]);
    }

    public function published(): static
    {
        return $this->state(['status' => 'published', 'published_at' => now()->subDay()]);
    }
}
