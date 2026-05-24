<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Conference', 'Workshop', 'Meetup', 'Webinar', 'Festival',
            'Competition', 'Music', 'Sports', 'Technology', 'Community',
            'Food', 'Art', 'Education', 'Networking', 'Entertainment'
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->updateOrInsert(
                ['name' => $cat],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
