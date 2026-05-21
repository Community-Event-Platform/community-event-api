<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ===== Public routes =====
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/test-events', function () {
    return Event::all();
});

Route::get('/events', [EventController::class, 'index']);

// Public categories list for frontend dropdown
Route::get('/categories', [CategoryController::class, 'index']);


// ===== Protected routes (Yêu cầu đăng nhập qua Sanctum) =====
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
    // Dashboard stats
    Route::get('/dashboard-stats', [\App\Http\Controllers\DashboardController::class, 'index']);
});