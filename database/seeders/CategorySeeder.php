<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * 
     * NOTE: Categories table has been dropped.
     * Categories are now stored as VARCHAR in the events.category column.
     * Available categories: Conference, Workshop, Meetup, Webinar, Festival, Competition, Music, Sports
     */
    public function run(): void
    {
        // This seeder is no longer needed as categories are now part of events table
        // Categories are stored as string values in events.category column
    }
}
