<?php

namespace Database\Seeders;

use App\Models\CategoryFilm;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectSeries;
use Illuminate\Database\Seeder;

class ProjectSeriesSeeder extends Seeder
{
    public function run(): void
    {
        $category = CategoryFilm::firstOrCreate([
            'slug' => 'film-documentary',
        ], [
            'name' => 'Documentary',
            'deskripsi' => 'Koleksi film dokumenter yang menampilkan cerita nyata dan inspiratif.',
            'thumbnail' => null,
            'start' => 2024,
            'urutan' => 1,
            'is_active' => true,
        ]);

        $client = Client::firstOrCreate([
            'email' => 'production@medantainment.local',
        ], [
            'name' => 'Medantainment Studio',
            'phone' => '+6281234567890',
            'address' => 'Jl. Contoh No. 1, Jakarta',
            'logo' => 'default-client.png',
            'status' => true,
            'urutan' => 1,
        ]);

        $series = ProjectSeries::firstOrCreate([
            'slug' => 'kisah-tentang-kehidupan',
        ], [
            'category_film_id' => $category->id,
            'name' => 'Kisah Tentang Kehidupan',
            'description' => 'Playlist series berisi episode dokumenter tentang perjalanan manusia, budaya, dan lingkungan.',
            'thumbnail' => null,
            'urutan' => 1,
            'is_active' => true,
        ]);

        Project::firstOrCreate([
            'name' => 'Episode 1 - Awal Perjalanan',
        ], [
            'client_id' => $client->id,
            'category_film_id' => $category->id,
            'series_id' => $series->id,
            'type' => 'series',
            'link' => 'https://www.youtube.com/watch?v=ysz5S6PUM-U',
            'description' => 'Episode pembuka yang membawa penonton ke dalam dunia karakter utama dan latar cerita.',
            'start_date' => now()->subWeeks(3)->toDateString(),
            'end_date' => now()->subWeeks(3)->addDays(1)->toDateString(),
            'urutan' => 1,
        ]);

        Project::firstOrCreate([
            'name' => 'Episode 2 - Pertemuan Tak Terduga',
        ], [
            'client_id' => $client->id,
            'category_film_id' => $category->id,
            'series_id' => $series->id,
            'type' => 'series',
            'link' => 'https://www.youtube.com/watch?v=ScMzIvxBSi4',
            'description' => 'Kisah berlanjut saat tokoh utama bertemu dengan sosok penting yang mengubah arah cerita.',
            'start_date' => now()->subWeeks(2)->toDateString(),
            'end_date' => now()->subWeeks(2)->addDays(1)->toDateString(),
            'urutan' => 2,
        ]);
    }
}
