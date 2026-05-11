<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Event;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Get all guests for events belonging to this organizer
        $guests = Guest::whereHas('event', function($query) use ($user) {
            $query->where('organizer_id', $user->id);
        })->with('event')->get();

        return response()->json([
            'data' => $guests
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'status' => 'required|in:pending,confirmed,attended',
        ]);

        $guest = Guest::create($request->all());

        return response()->json([
            'message' => 'Guest added successfully',
            'data' => $guest
        ], 201);
    }
}
