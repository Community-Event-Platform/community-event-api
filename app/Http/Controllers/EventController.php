<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Category;
use App\Models\Registration;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
public function index(Request $request)
{
    $search = $request->query('search');
    $category = $request->query('category');

    $events = Event::with('category')
        ->withCount(['registrations as attendees' => function($q) {
            $q->whereNull('waitlist_position')
              ->whereNotIn('status', ['Cancelled', 'Rejected']);
        }])
        ->where('status', 'published')

        // SEARCH: name OR description
        ->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        })

        // FILTER CATEGORY
        ->when($category, function ($query, $category) {
            $query->where('category_id', $category);
        })

        ->orderBy('date_time')
        ->get();

    // add `image` attribute for frontend compatibility
    $events->each(function ($ev) {
        $ev->setAttribute('image', $ev->image_url ?? null);
    });

    return response()->json([
        'data' => $events
    ], 200);
}

public function store(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'organizer') {
            return response()->json(['message' => 'Forbidden: Only organizers can create events'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'location' => 'required|string|max:255',
            'date_time' => 'required|date',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:published,draft,cancelled,ended',
            'image' => 'nullable|image|max:5120',
            'event_type' => 'nullable|string|max:50',
            'require_additional_info' => 'nullable|boolean',
            'custom_form_spec' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'fees_and_taxes' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('events', 'public');
            $imageUrl = '/storage/' . $path;
        }

        $event = Event::create([
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'location' => $request->location,
            'date_time' => $request->date_time,
            'capacity' => $request->capacity,
            'status' => $request->status,
            'event_type' => $request->event_type ?? 'Free',
            'require_additional_info' => $request->boolean('require_additional_info'),
            'custom_form_spec' => $request->custom_form_spec,
            'organizer_id' => $user->id,
            'image_url' => $imageUrl,
            'price' => $request->price ?? 0,
            'fees_and_taxes' => $request->fees_and_taxes ?? 0,
        ]);

        // provide a friendly `image` attribute for frontend compatibility
        if ($imageUrl) {
            $event->setAttribute('image', $imageUrl);
        } else {
            $event->setAttribute('image', $event->image_url);
        }

        return response()->json(['data' => $event], 201);
    }

    public function show(Request $request, $id)
    {
        // Đã sửa: Load kèm cả organizer và category
        $event = Event::with(['organizer', 'category'])->find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $registrationsCount = Registration::where('event_id', $event->id)
            ->whereNotIn('status', ['Cancelled', 'Rejected'])
            ->count();
        $approvedCount = Registration::where('event_id', $event->id)
            ->where('status', 'Approved')
            ->count();
        $remainingSeats = max(0, $event->capacity - $registrationsCount);

        $reviewsCount = Review::where('event_id', $event->id)->count();
        $averageRating = Review::where('event_id', $event->id)->avg('rating') ?: 0.0;
        $averageRating = round($averageRating, 1);

        $ratingBreakdown = [
            '5' => Review::where('event_id', $event->id)->where('rating', 5)->count(),
            '4' => Review::where('event_id', $event->id)->where('rating', 4)->count(),
            '3' => Review::where('event_id', $event->id)->where('rating', 3)->count(),
            '2' => Review::where('event_id', $event->id)->where('rating', 2)->count(),
            '1' => Review::where('event_id', $event->id)->where('rating', 1)->count(),
        ];

        $reviews = Review::with('attendee')
            ->where('event_id', $event->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at,
                    'attendee' => [
                        'name' => $review->attendee->name ?? 'Người dùng',
                    ]
                ];
            });

        // ensure `image` attribute is present for frontend
        $event->setAttribute('image', $event->image_url ?? null);

        $eventData = array_merge($event->toArray(), [
            'registrations_count' => $registrationsCount,
            'participants_count' => $approvedCount,
            'remaining_seats' => $remainingSeats,
            'average_rating' => $averageRating,
            'reviews_count' => $reviewsCount,
            'rating_breakdown' => $ratingBreakdown,
            'reviews' => $reviews,
        ]);

        return response()->json(['data' => $eventData], 200);
    }

    public function register(Request $request, $id)
    {
        $user = $request->user();
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $exists = Registration::where('event_id', $event->id)
            ->where('attendee_id', $user->id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'You have already registered for this event!'], 400);
        }

        $registrationsCount = Registration::where('event_id', $event->id)->count();
        $isFull = $registrationsCount >= $event->capacity;
        $isPaidEvent = $event->price != null && $event->price > 0;

        // For paid events: reject if full
        if ($isPaidEvent && $isFull) {
            return response()->json(['message' => 'The event is fully booked!'], 400);
        }

        // For free events: allow waitlist if full
        $waitlistPosition = null;
        if ($isFull && !$isPaidEvent) {
            // Get the next waitlist position
            $maxPosition = Registration::where('event_id', $event->id)
                ->whereNotNull('waitlist_position')
                ->max('waitlist_position');
            $waitlistPosition = ($maxPosition ?? 0) + 1;
        }

        $registration = Registration::create([
            'event_id' => $event->id,
            'attendee_id' => $user->id,
            'status' => $isFull ? 'Waitlisted' : 'Approved',
            'waitlist_position' => $waitlistPosition,
        ]);

        return response()->json([
            'message' => $isFull ? 'Added to waitlist successfully!' : 'Registration completed successfully!',
            'data' => $registration
        ], 201);
    }

    public function storeReview(Request $request, $id)
    {
        $user = $request->user();
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Check if event has ended
        $eventEndTime = $event->ended_at ?? $event->date_time;
        if (now()->isBefore($eventEndTime)) {
            return response()->json(['message' => 'The event has not finished yet. You can only submit a review after it ends.'], 400);
        }

        $isRegistered = Registration::where('event_id', $event->id)
            ->where('attendee_id', $user->id)
            ->exists();

        if (!$isRegistered) {
            return response()->json(['message' => 'You must be registered for the event before you can leave a review.'], 403);
        }

        $isReviewed = Review::where('event_id', $event->id)
            ->where('attendee_id', $user->id)
            ->exists();

        if ($isReviewed) {
            return response()->json(['message' => 'You have already submitted a review for this event.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:300',
        ], [
            'rating.required' => 'Please choose a star rating.',
            'rating.integer' => 'The rating is invalid.',
            'rating.min' => 'The minimum rating is 1 star.',
            'rating.max' => 'The maximum rating is 5 stars.',
            'comment.required' => 'Please write a review comment.',
            'comment.max' => 'The review comment cannot exceed 300 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $review = Review::create([
            'event_id' => $event->id,
            'attendee_id' => $user->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Gửi đánh giá thành công!',
            'data' => $review
        ], 201);
    }

    public function featured()
    {
        $events = Event::with('category')
            ->where('status', 'published')
            ->orderBy('date_time')
            ->limit(6)
            ->get();

        // add `image` attribute for frontend compatibility
        $events->each(function ($ev) {
            $ev->setAttribute('image', $ev->image_url ?? null);
        });

        return response()->json(['data' => $events], 200);
    }

    public function search(Request $request)
    {
        $query = $request->query('q', '');

        $events = Event::with('category')
            ->where('status', 'published')
            ->when($query, function ($builder, $query) {
                $builder->where(function ($queryBuilder) use ($query) {
                    $queryBuilder->where('name', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhere('location', 'like', "%{$query}%")
                        ->orWhereHas('category', function ($categoryQuery) use ($query) {
                            $categoryQuery->where('name', 'like', "%{$query}%");
                        });
                });
            })
            ->orderBy('date_time')
            ->get();

        // add `image` attribute for frontend compatibility
        $events->each(function ($ev) {
            $ev->setAttribute('image', $ev->image_url ?? null);
        });

        return response()->json(['data' => $events], 200);
    }

    public function organizerEvents(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'organizer') {
            return response()->json(['message' => 'Forbidden: Only organizers can view their events'], 403);
        }

        $events = Event::with(['category', 'reviews.attendee'])
            ->where('organizer_id', $user->id)
            ->withCount(['registrations as registrations_count' => function ($query) {
                $query->whereNotIn('status', ['Cancelled', 'Rejected']);
            }])
            ->withCount(['registrations as participants_count' => function ($query) {
                $query->where('status', 'Approved');
            }])
            ->withCount(['reviews as reviews_count'])
            ->orderBy('date_time')
            ->get();

        // add `image` attribute for frontend compatibility and format reviews
        $events->each(function ($ev) {
            $ev->setAttribute('image', $ev->image_url ?? null);
            $ev->setAttribute('remaining_seats', max(0, $ev->capacity - ($ev->registrations_count ?? 0)));

            // Format reviews with user names
            if ($ev->reviews) {
                $ev->reviews = $ev->reviews->map(function ($review) {
                    $reviewerName = $review->attendee->name ?? $review->attendee_name ?? 'Anonymous';
                    return [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'user_name' => $reviewerName,
                        'reviewer_name' => $reviewerName,
                        'created_at' => $review->created_at,
                    ];
                });
            }
        });

        return response()->json(['data' => $events], 200);
    }

    public function endEvent(Request $request, $id)
    {
        $user = $request->user();
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        if ($event->organizer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden: Only the organizer can end this event'], 403);
        }

        if ($event->status === 'ended') {
            return response()->json(['message' => 'Event has already been ended'], 400);
        }

        $event->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);

        return response()->json(['message' => 'Event marked as ended successfully', 'data' => $event], 200);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        if ($event->organizer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden: Only the organizer can update this event'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'sometimes|required|exists:categories,id',
            'location' => 'sometimes|required|string|max:255',
            'date_time' => 'sometimes|required|date',
            'capacity' => 'sometimes|required|integer|min:1',
            'status' => 'sometimes|required|in:published,draft,cancelled,ended',
            'image' => 'nullable|image|max:5120',
            'event_type' => 'nullable|string|max:50',
            'require_additional_info' => 'nullable|boolean',
            'custom_form_spec' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('events', 'public');
            $imageUrl = '/storage/' . $path;
        } else {
            $imageUrl = $event->image_url;
        }

        $event->update([
            'name' => $request->name ?? $event->name,
            'description' => $request->description ?? $event->description,
            'category_id' => $request->category_id ?? $event->category_id,
            'location' => $request->location ?? $event->location,
            'date_time' => $request->date_time ?? $event->date_time,
            'capacity' => $request->capacity ?? $event->capacity,
            'status' => $request->status ?? $event->status,
            'event_type' => $request->event_type ?? $event->event_type,
            'require_additional_info' => $request->has('require_additional_info') ? $request->boolean('require_additional_info') : $event->require_additional_info,
            'custom_form_spec' => $request->custom_form_spec ?? $event->custom_form_spec,
            'image_url' => $imageUrl,
        ]);

        // ensure frontend can read `image` attribute
        $event->setAttribute('image', $imageUrl);

        return response()->json(['data' => $event], 200);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        if ($event->organizer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden: Only the organizer can delete this event'], 403);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully'], 200);
    }
}
