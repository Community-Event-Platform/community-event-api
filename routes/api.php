<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
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

// Public categories list for frontend dropdown - ĐÃ SỬA DÒNG NÀY ĐỂ HẾT LỖI
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
    Route::post('/categories', [\App\Http\Controllers\CategoryController::class, 'store']);
    Route::put('/categories/{id}', [\App\Http\Controllers\CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [\App\Http\Controllers\CategoryController::class, 'destroy']);
    // Register event
    Route::post('/events/{id}/register', [\App\Http\Controllers\EventController::class, 'register']);
    // Create review
    Route::post('/events/{id}/reviews', [\App\Http\Controllers\EventController::class, 'storeReview']);
    // Dashboard stats
    Route::get('/dashboard-stats', [\App\Http\Controllers\DashboardController::class, 'index']);
});
