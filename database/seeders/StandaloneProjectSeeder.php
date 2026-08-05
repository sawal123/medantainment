<?php

namespace Database\Seeders;

use App\Models\CategoryFilm;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Seeder;

class StandaloneProjectSeeder extends Seeder
{
    public function run(): void
    {
        $category = CategoryFilm::firstOrCreate([
            'slug' => 'film-independen',
        ], [
            'name' => 'Independent Film',
            'deskripsi' => 'Proyek film tunggal yang berdiri sendiri tanpa rangkaian episode.',
            'thumbnail' => null,
            'start' => 2024,
            'urutan' => 2,
            'is_active' => true,
        ]);

        $client = Client::firstOrCreate([
            'email' => 'client@indiefilm.local',
        ], [
            'name' => 'Indie Creators',
            'phone' => '+6289876543210',
            'address' => 'Jl. Independen No. 2, Bandung',
            'logo' => 'default-client.png',
            'status' => true,
            'urutan' => 2,
        ]);

        Project::firstOrCreate([
            'name' => 'Film Tunggal: Matahari Senja',
        ], [
            'client_id' => $client->id,
            'category_film_id' => $category->id,
            'series_id' => null,
            'type' => 'movie',
            'link' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw',
            'description' => 'Film tunggal bertema perjalanan emosional melintasi kota dan waktu.',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->subMonth()->addDays(1)->toDateString(),
            'urutan' => 1,
        ]);
    }
}
