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
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/search', [EventController::class, 'search']);
Route::get('/events/featured', [EventController::class, 'featured']);
Route::get('/events/{id}', [EventController::class, 'show']);

// Public categories
Route::get('/categories', function () {
    return Category::select('id', 'name')->orderBy('name')->get();
});

// Google OAuth routes
Route::get('/auth/google/redirect', [GoogleController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) { return $request->user(); });

    // Event management
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{id}', [EventController::class, 'update']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);
    Route::get('/organizer/events', [EventController::class, 'organizerEvents']);

    // Category management
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Registration
    Route::post('/events/{id}/register', [RegistrationController::class, 'register']);
    Route::get('/registrations', [RegistrationController::class, 'getMyRegistrations']);
    Route::get('/user/profile', [RegistrationController::class, 'getProfileWithRegistrations']);
    Route::patch('/registrations/{id}/cancel', [RegistrationController::class, 'cancelRegistration']);
    Route::patch('/registrations/{id}/approve', [RegistrationController::class, 'organizerApprove']);
    Route::patch('/registrations/{id}/reject', [RegistrationController::class, 'organizerReject']);
    Route::get('/events/{id}/registration-status', [RegistrationController::class, 'checkRegistration']);

    // Participants
    Route::get('/events/{id}/participants', [RegistrationController::class, 'eventParticipants']);
    Route::get('/organizer/participants', [RegistrationController::class, 'allParticipants']);

    // Reviews
    Route::post('/events/{id}/reviews', [EventController::class, 'storeReview']);

    // Dashboard & Notifications
    Route::get('/dashboard-stats', [DashboardController::class, 'index']);
    Route::get('/notifications', [RegistrationController::class, 'getNotifications']);
});
