<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index()
    {
        // Fetch all published events
        $events = Event::where('status', 'Published')
            ->with('organizer')
            ->get();

        // Get registrations count for each event
        $registrationsCounts = DB::table('registrations')
            ->select('event_id', DB::raw('COUNT(*) as count'))
            ->groupBy('event_id')
            ->pluck('count', 'event_id');

        // Transform data to match frontend expectations
        $transformedEvents = $events->map(function ($event) use ($registrationsCounts) {
            return [
                'id' => $event->id,
                'name' => $event->name,
                'description' => $event->description,
                'category' => $event->category ?? 'Community',
                'location' => $event->location,
                'date_time' => $event->date_time ?? $event->event_date,
                'capacity' => $event->capacity,
                'event_type' => $event->event_type ?? 'Free',
                'status' => $event->status,
                'image_url' => $event->image_url ?? 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=500&h=300&fit=crop',
                'attendees' => $event->attendees ?? $registrationsCounts[$event->id] ?? 0,
                'rating' => $event->rating ?? 4.5,
                'price' => $event->price ?? ($event->event_type === 'Paid' ? 100000 : null),
                'organizer' => $event->organizer ? [
                    'id' => $event->organizer->id,
                    'name' => $event->organizer->name,
                ] : null,
            ];
        });

        return response()->json(['data' => $transformedEvents, 'count' => count($transformedEvents)], 200);
    }

    public function store(Request $request)
    {
        // Ensure user is authenticated via sanctum middleware
        $user = $request->user();
        if ($user->role !== 'organizer') {
            return response()->json(['message' => 'Forbidden: Only organizers can create events'], 403);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'event_date' => 'required|date',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:published,draft,cancelled',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $event = Event::create([
            'name' => $request->name,
            'description' => $request->description,
            'location' => $request->location,
            'event_date' => $request->event_date,
            'capacity' => $request->capacity,
            'status' => $request->status,
            'category_id' => $request->category_id,
            'organizer_id' => $user->id,
        ]);

        return response()->json(['data' => $event], 201);
    }
}
?>
