<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;
use App\Models\Event;
use App\Models\Category; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;

// ===== Public routes =====
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/test-events', function () {
    return Event::all();
});
Route::get('/events', [\App\Http\Controllers\EventController::class, 'index']);
Route::get('/events/search', [\App\Http\Controllers\EventController::class, 'search']);
Route::get('/events/featured', [\App\Http\Controllers\EventController::class, 'featured']);
Route::get('/events/{id}', [\App\Http\Controllers\EventController::class, 'show']);

// Public categories list for frontend dropdown - ĐÃ SỬ DỤNG NÀY ĐI, HẾT LỖI
Route::get('/categories', function () {
    // Thay vì select từ bảng Event (cột category đã xóa), ta lấy trực tiếp từ bảng Category
    return Category::select('id', 'name')
        ->orderBy('name')
        ->get();
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
    // Update event (Organizer only)
    Route::put('/events/{id}', [\App\Http\Controllers\EventController::class, 'update']);
    // Delete event (Organizer only)
    Route::delete('/events/{id}', [\App\Http\Controllers\EventController::class, 'destroy']);
    // Get organizer's events
    Route::get('/organizer/events', [\App\Http\Controllers\EventController::class, 'organizerEvents']);

    // Category management (organizer only)
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // CEP-83: Register for event (or join waitlist if full)
    Route::post('/events/{id}/register', [RegistrationController::class, 'register']);

    // CEP-85/86: Cancel a registration (triggers auto-promotion from waitlist)
    Route::patch('/registrations/{id}/cancel', [RegistrationController::class, 'cancel']);

    // CEP-84: Get participants for a specific event (organizer only)
    Route::get('/events/{id}/participants', [RegistrationController::class, 'eventParticipants']);

    // CEP-84: Get all participants across organizer's events (supports ?event_id=&status= filters)
    Route::get('/organizer/participants', [RegistrationController::class, 'allParticipants']);

    // Create review
    Route::post('/events/{id}/reviews', [\App\Http\Controllers\EventController::class, 'storeReview']);
    // Dashboard stats
    Route::get('/dashboard-stats', [DashboardController::class, 'index']);
});
