<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $categories = [
            ['name' => 'Conference'],
            ['name' => 'Workshop'],
            ['name' => 'Meetup'],
            ['name' => 'Webinar'],
            ['name' => 'Festival'],
            ['name' => 'Competition'],
        ];
        foreach ($categories as $cat) {
            Category::create($cat);
        }
    }
}
