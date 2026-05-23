<?php

use App\Http\Controllers\AuthController;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/test-events', function () {
    return Event::all();
});
Route::get('/events', [\App\Http\Controllers\EventController::class, 'index']);
Route::get('/events/{id}', [\App\Http\Controllers\EventController::class, 'show']);

// Public categories list for frontend dropdown
Route::get('/categories', function () {
    return Event::select('category as name')
        ->whereNotNull('category')
        ->distinct()
        ->orderBy('category')
        ->get()
        ->values()
        ->map(fn ($category, $index) => [
            'id' => $index + 1,
            'name' => $category->name,
        ]);
});

// Google OAuth routes
Route::get('/auth/google/redirect', [GoogleController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // Create event (Organizer only)
    Route::post('/events', [\App\Http\Controllers\EventController::class, 'store']);
    // Register event
    Route::post('/events/{id}/register', [\App\Http\Controllers\EventController::class, 'register']);
    // Create review
    Route::post('/events/{id}/reviews', [\App\Http\Controllers\EventController::class, 'storeReview']);
    // Dashboard stats
    Route::get('/dashboard-stats', [\App\Http\Controllers\DashboardController::class, 'index']);
});
