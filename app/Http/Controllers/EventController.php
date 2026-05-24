<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Category;
use App\Models\Registration;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with('category')
            ->where('status', 'published')
            ->orderBy('date_time')
            ->get();

        return response()->json(['data' => $events], 200);
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
            'category_id' => 'required|exists:categories,id', // Kiểm tra ID có tồn tại trong bảng categories
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
            'category_id' => $request->category_id,
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

    public function show(Request $request, $id)
    {
        // Đã sửa: Load kèm cả organizer và category
        $event = Event::with(['organizer', 'category'])->find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $registrationsCount = Registration::where('event_id', $event->id)->count();
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

        $eventData = array_merge($event->toArray(), [
            'registrations_count' => $registrationsCount,
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
            return response()->json(['message' => 'Bạn đã đăng ký tham gia sự kiện này rồi!'], 400);
        }

        $registrationsCount = Registration::where('event_id', $event->id)->count();
        if ($registrationsCount >= $event->capacity) {
            return response()->json(['message' => 'Sự kiện đã hết ghế trống!'], 400);
        }

        $registration = Registration::create([
            'event_id' => $event->id,
            'attendee_id' => $user->id,
            'status' => 'Approved',
        ]);

        return response()->json([
            'message' => 'Đăng ký tham gia thành công!',
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

        $isRegistered = Registration::where('event_id', $event->id)
            ->where('attendee_id', $user->id)
            ->exists();

        if (!$isRegistered) {
            return response()->json(['message' => 'Bạn cần phải đăng ký tham gia sự kiện mới có thể đánh giá!'], 403);
        }

        $isReviewed = Review::where('event_id', $event->id)
            ->where('attendee_id', $user->id)
            ->exists();

        if ($isReviewed) {
            return response()->json(['message' => 'Bạn đã gửi đánh giá cho sự kiện này rồi!'], 400);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:300',
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.integer' => 'Đánh giá không hợp lệ.',
            'rating.min' => 'Đánh giá tối thiểu là 1 sao.',
            'rating.max' => 'Đánh giá tối đa là 5 sao.',
            'comment.required' => 'Vui lòng viết nhận xét đánh giá.',
            'comment.max' => 'Nhận xét không được vượt quá 300 ký tự.',
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
}
