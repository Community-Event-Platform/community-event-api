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
    Route::post('/events', [EventController::class, 'store']);
    
    // Dashboard stats (Gộp cả thống kê và danh sách danh mục)
    Route::get('/dashboard-stats', [DashboardController::class, 'index']);

    // Các hành động CRUD danh mục của Organizer
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
});