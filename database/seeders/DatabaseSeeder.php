<?php

namespace Database\Seeders;

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
        // Seed users first
        $this->call(UserSeeder::class);
        
        // Then seed events
        $this->call(EventSeeder::class);
        
        // Seed registrations, reviews, and notifications
        $this->call(RegistrationSeeder::class);
        $this->call(ReviewSeeder::class);
        $this->call(NotificationSeeder::class);
    }
}
