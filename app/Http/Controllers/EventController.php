<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::where('status', 'published')
            ->orderBy('date_time')
            ->get();

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
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'date_time' => 'required|date',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:published,draft,cancelled',
            'event_type' => 'nullable|string|max:50',
            'require_additional_info' => 'nullable|boolean',
            'custom_form_spec' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $event = Event::create([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'location' => $request->location,
            'date_time' => $request->date_time,
            'capacity' => $request->capacity,
            'status' => $request->status,
            'event_type' => $request->event_type ?? 'Free',
            'require_additional_info' => $request->boolean('require_additional_info'),
            'custom_form_spec' => $request->custom_form_spec,
            'organizer_id' => $user->id,
        ]);

        return response()->json(['data' => $event], 201);
    }
}
