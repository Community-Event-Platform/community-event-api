<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        
        // Lay cac su kien da ket thuc (date_time < now)
        $pastEvents = DB::table('events')
            ->where('date_time', '<', $now)
            ->limit(5)
            ->get(['id', 'name', 'date_time']);
        
        // Lay attendees
        $attendees = DB::table('users')
            ->where('role', '!=', 'organizer')
            ->limit(10)
            ->pluck('id')
            ->toArray();
        
        $reviewData = [];
        $registrationData = [];
        
        foreach ($pastEvents as $event) {
            $numRegistrations = min(rand(2, 5), count($attendees));
            
            for ($i = 0; $i < $numRegistrations; $i++) {
                $attendeeId = $attendees[$i % count($attendees)];
                
                // Kiem tra chua co registration truoc do
                $exists = DB::table('registrations')
                    ->where('event_id', $event->id)
                    ->where('attendee_id', $attendeeId)
                    ->exists();
                
                if (!$exists) {
                    $registrationData[] = [
                        'event_id' => $event->id,
                        'attendee_id' => $attendeeId,
                        'status' => 'Confirmed',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    
                    $ratings = [3, 4, 4, 5, 5, 5];
                    $comments = [
                        'Su kien rat tuyet voi, to chuc chu dao!',
                        'Khong khi tuyet voi, se quay lai vao dip khac.',
                        'Trai nghiem tot, cho doi them cac su kien tiep theo.',
                        'To chuc chuyen nghiep, thong tin cap nhat day du.',
                        'Thich hop voi moi nguoi, khong gian tot.',
                        'Rat bo ich, hoc duoc nhieu dieu moi.',
                    ];
                    
                    $reviewData[] = [
                        'event_id' => $event->id,
                        'attendee_id' => $attendeeId,
                        'rating' => $ratings[array_rand($ratings)],
                        'comment' => $comments[array_rand($comments)],
                        'created_at' => Carbon::parse($event->date_time)->addDays(rand(1, 3)),
                        'updated_at' => $now,
                    ];
                }
            }
        }
        
        if (!empty($registrationData)) {
            DB::table('registrations')->insert($registrationData);
        }
        
        if (!empty($reviewData)) {
            DB::table('reviews')->upsert($reviewData, ['event_id', 'attendee_id'], ['rating', 'comment', 'updated_at']);
        }
        
        $this->command->info('Created ' . count($reviewData) . ' reviews for ' . count($pastEvents) . ' past events.');
    }
}