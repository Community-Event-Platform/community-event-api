<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index(Request $request)
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

    /**
     * Search and filter events
     * GET /api/events/search
     */
    public function search(Request $request)
    {
        $query = Event::where('status', 'Published')->with('organizer');

        // Search by keyword (name, description, location)
        if ($request->has('q') && $request->q) {
            $searchTerm = '%' . $request->q . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('location', 'like', $searchTerm);
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Filter by event_type (Free/Paid)
        if ($request->has('event_type') && $request->event_type) {
            $query->where('event_type', $request->event_type);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('date_time', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('date_time', '<=', $request->date_to . ' 23:59:59');
        }

        // Sort by field (default: date_time)
        $sortField = $request->get('sort_by', 'date_time');
        $sortOrder = $request->get('sort_order', 'asc');
        $allowedSortFields = ['date_time', 'name', 'rating', 'attendees'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortOrder === 'desc' ? 'desc' : 'asc');
        }

        // Pagination
        $perPage = $request->get('per_page', 12);
        $events = $query->paginate($perPage);

        // Get registrations count
        $registrationsCounts = DB::table('registrations')
            ->select('event_id', DB::raw('COUNT(*) as count'))
            ->groupBy('event_id')
            ->pluck('count', 'event_id');

        // Transform data
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

        return response()->json([
            'data' => $transformedEvents,
            'count' => $events->total(),
            'current_page' => $events->currentPage(),
            'last_page' => $events->lastPage(),
            'per_page' => $events->perPage(),
        ], 200);
    }

    /**
     * Get a single event by ID
     * GET /api/events/{id}
     */
    public function show($id)
    {
        $event = Event::where('status', 'Published')
            ->with('organizer')
            ->find($id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        // Get registration count for this event
        $registrationCount = DB::table('registrations')
            ->where('event_id', $id)
            ->count();

        $transformedEvent = [
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
            'attendees' => $event->attendees ?? $registrationCount,
            'rating' => $event->rating ?? 4.5,
            'price' => $event->price ?? ($event->event_type === 'Paid' ? 100000 : null),
            'organizer' => $event->organizer ? [
                'id' => $event->organizer->id,
                'name' => $event->organizer->name,
            ] : null,
        ];

        return response()->json(['data' => $transformedEvent], 200);
    }

    /**
     * Get featured events (top by rating/attendees)
     * GET /api/events/featured
     */
    public function featured()
    {
        $events = Event::where('status', 'Published')
            ->with('organizer')
            ->orderBy('rating', 'desc')
            ->orderBy('attendees', 'desc')
            ->limit(4)
            ->get();

        $registrationsCounts = DB::table('registrations')
            ->select('event_id', DB::raw('COUNT(*) as count'))
            ->groupBy('event_id')
            ->pluck('count', 'event_id');

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