<?php

use App\Http\Controllers\AuthController;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/test-events', function () {
    return Event::all();
});
Route::get('/events', [\App\Http\Controllers\EventController::class, 'index']);

// Public categories list for frontend dropdown
Route::get('/categories', function () {
    return \App\Models\Category::all();
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // Create event (Organizer only)
    Route::post('/events', [\App\Http\Controllers\EventController::class, 'store']);
    // Dashboard stats
    Route::get('/dashboard-stats', [\App\Http\Controllers\DashboardController::class, 'index']);
    // Guest management
    Route::get('/guests', [\App\Http\Controllers\GuestController::class, 'index']);
    Route::post('/guests', [\App\Http\Controllers\GuestController::class, 'store']);
});
