<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Total Events
        $totalEvents = Event::where('organizer_id', $user->id)->count();

        // 2. Total Participants (Unique registrations across all organizer's events)
        $totalParticipants = DB::table('registrations')
            ->join('events', 'registrations.event_id', '=', 'events.id')
            ->where('events.organizer_id', $user->id)
            ->distinct()
            ->count('registrations.user_id');

        // 3. Active Events (Published)
        $activeEvents = Event::where('organizer_id', $user->id)
            ->where('status', 'published')
            ->count();

        // 4. Categories with event counts for THIS organizer
        $categories = Event::select('category as name', DB::raw('COUNT(*) as events_count'))
            ->where('organizer_id', $user->id)
            ->groupBy('category')
            ->orderBy('category')
            ->get()
            ->values()
            ->map(function ($category, $index) {
                return [
                    'id' => $index + 1,
                    'name' => $category->name,
                    'events_count' => $category->events_count,
                ];
            });

        return response()->json([
            'stats' => [
                'total_events' => $totalEvents,
                'total_participants' => $totalParticipants,
                'active_events' => $activeEvents,
            ],
            'categories' => $categories
        ]);
    }
}
