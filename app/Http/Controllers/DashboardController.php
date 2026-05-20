<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Category;
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
            ->count('registrations.attendee_id');

        // 3. Active Events (Published)
        $activeEvents = Event::where('organizer_id', $user->id)
            ->where('status', 'published')
            ->count();

        // 4. Categories with event counts for THIS organizer
        $categories = Category::select('categories.id', 'categories.name')
            ->withCount(['events' => function($query) use ($user) {
                $query->where('organizer_id', $user->id);
            }])
            ->get();

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
