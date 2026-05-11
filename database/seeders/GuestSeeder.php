<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Guest;
use Illuminate\Database\Seeder;

class GuestSeeder extends Seeder
{
    public function run(): void
    {
        $event = Event::first();
        if (!$event) return;

        for ($i = 0; $i < 20; $i++) {
            Guest::create([
                'event_id' => $event->id,
                'name' => 'Guest ' . ($i + 1),
                'email' => 'guest' . ($i + 1) . '@example.com',
                'phone' => '090123456' . $i,
                'status' => $i % 3 === 0 ? 'confirmed' : ($i % 3 === 1 ? 'attended' : 'pending'),
            ]);
        }
    }
}
