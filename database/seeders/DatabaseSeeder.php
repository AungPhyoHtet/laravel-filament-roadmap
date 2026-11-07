<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            User::factory()->raw([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'Test@1234',
                'is_admin' => true,
            ]),
        );

        User::factory(10)->create();

        Feature::factory(10)->create();

        $this->call([
            CommentSeeder::class,
            VoteSeeder::class,
        ]);
    }
}
