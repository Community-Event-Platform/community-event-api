<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Safe defaults
        $totalEvents = 0;
        $totalParticipants = 0;
        $activeEvents = 0;
        $categories = [];

        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'stats' => [
                        'total_events' => $totalEvents,
                        'total_participants' => $totalParticipants,
                        'active_events' => $activeEvents,
                    ],
                    'categories' => $categories
                ], 200);
            }

            $organizerId = $user->id;

            // 1) Total events for organizer
            try {
                $totalEvents = Event::where('organizer_id', $organizerId)->count();
            } catch (\Exception $e) {
                Log::error('Dashboard Stats Error (totalEvents): ' . $e->getMessage());
                $totalEvents = 0;
            }

            // 2) Active events (published)
            try {
                $activeEvents = Event::where('organizer_id', $organizerId)
                    ->where('status', 'published')
                    ->count();
            } catch (\Exception $e) {
                Log::error('Dashboard Stats Error (activeEvents): ' . $e->getMessage());
                $activeEvents = 0;
            }

            // 3) Total participants (unique attendees across organizer's events)
            try {
                $totalParticipants = DB::table('registrations')
                    ->join('events', 'registrations.event_id', '=', 'events.id')
                    ->where('events.organizer_id', $organizerId)
                    ->distinct()
                    ->count('registrations.attendee_id');
            } catch (\Exception $e) {
                Log::error('Dashboard Stats Error (participants): ' . $e->getMessage());
                $totalParticipants = 0;
            }

            // 4) Categories with event counts for this organizer
            try {
                $categories = Category::select('categories.id', 'categories.name')
                    ->withCount(['events' => function ($q) use ($organizerId) {
                        $q->where('organizer_id', $organizerId);
                    }])
                    ->get()
                    ->map(function ($c) {
                        return [
                            'id' => $c->id,
                            'name' => $c->name,
                            'events_count' => $c->events_count ?? 0,
                        ];
                    });
            } catch (\Exception $e) {
                Log::error('Dashboard Stats Error (categories): ' . $e->getMessage());
                $categories = [];
            }
        } catch (\Exception $e) {
            Log::error('Dashboard Controller Global Exception: ' . $e->getMessage());
        }

        return response()->json([
            'stats' => [
                'total_events' => $totalEvents,
                'total_participants' => $totalParticipants,
                'active_events' => $activeEvents,
            ],
            'categories' => $categories
        ], 200);
    }
}