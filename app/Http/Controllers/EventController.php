<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
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
