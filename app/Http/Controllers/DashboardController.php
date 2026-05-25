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

            // 4. Categories with event counts for THIS organizer
            // Return ALL categories but include a count of events belonging to this organizer.
            $categoryQuery = Category::withCount(['events' => function($query) use ($organizerId) {
                    $query->where('organizer_id', $organizerId);
                }])
                ->orderBy('id');

            $categoriesPage = $request->query('categories_page');
            $categoriesPerPage = $request->query('categories_per_page');
            $categoriesMeta = [
                'page' => 1,
                'per_page' => null,
                'total' => 0,
                'total_pages' => 1,
            ];

            $totalCategories = $categoryQuery->count();
            $categoriesMeta['total'] = $totalCategories;

            if ($categoriesPage !== null && $categoriesPerPage !== null) {
                $page = max(1, (int) $categoriesPage);
                $perPage = max(1, min((int) $categoriesPerPage, 100));
                $categoriesMeta['page'] = $page;
                $categoriesMeta['per_page'] = $perPage;
                $categoriesMeta['total_pages'] = (int) ceil($totalCategories / $perPage);

                $categories = $categoryQuery
                    ->skip(($page - 1) * $perPage)
                    ->take($perPage)
                    ->get();
            } else {
                $categories = $categoryQuery->get();
                $categoriesMeta['per_page'] = $categories->count();
            }

            $categories = $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'events_count' => $category->events_count ?? 0,
                ];
            });

        } catch (\Exception $e) {
            Log::error('Dashboard Controller Global Exception: ' . $e->getMessage());
        }

        return response()->json([
            'stats' => [
                'total_events' => $totalEvents,
                'total_participants' => $totalParticipants,
                'active_events' => $activeEvents,
            ],
            'categories' => $categories,
            'categories_meta' => $categoriesMeta ?? [
                'page' => 1,
                'per_page' => count($categories),
                'total' => count($categories),
                'total_pages' => 1,
            ],
        ], 200);
    }
}
