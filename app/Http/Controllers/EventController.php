<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index()
    {
        // Fetch published events with their categories for the public facing organizer dashboard
        $events = Event::with('category')->where('status', 'published')->latest()->take(10)->get();
        return response()->json(['data' => $events], 200);
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
            'date_time' => 'required|date',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:published,draft,cancelled,Draft',
            'category_id' => 'required|exists:categories,id',
            'event_type' => 'required|in:Free,Paid',
            'require_additional_info' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $event = Event::create([
            'name' => $request->name,
            'description' => $request->description,
            'location' => $request->location,
            'date_time' => $request->date_time,
            'capacity' => $request->capacity,
            'status' => $request->status,
            'category_id' => $request->category_id,
            'event_type' => $request->event_type,
            'require_additional_info' => $request->require_additional_info ?? false,
            'organizer_id' => $user->id,
        ]);

        return response()->json(['data' => $event], 201);
    }
}
?>
