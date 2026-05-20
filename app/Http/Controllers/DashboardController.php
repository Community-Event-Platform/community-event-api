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
        // Khởi tạo các giá trị mặc định an toàn cho Front-end
        $totalEvents = 0;
        $activeEvents = 0;
        $participants = 0;
        $categories = [];

        try {
            $user = $request->user();
            if ($user) {
                $organizerId = $user->id;

                // 1. Đếm số lượng sự kiện của Organizer
                try {
                    $totalEvents = Event::where('organizer_id', $organizerId)->count();
                    $activeEvents = Event::where('organizer_id', $organizerId)
                                         ->where('status', 'Published')
                                         ->count();
                } catch (\Exception $e) {
                    Log::error("Dashboard Stats Error (Events Count): " . $e->getMessage());
                }

                // 2. Tính số lượng người tham gia đăng ký (Bọc riêng để chống sập nếu sai tên bảng)
                try {
                    $participants = DB::table('registrations')
                        ->join('events', 'registrations.event_id', '=', 'events.id')
                        ->where('events.organizer_id', $organizerId)
                        ->count();
                } catch (\Exception $e) {
                    Log::error("Dashboard Stats Error (Participants Join Count): " . $e->getMessage());
                    $participants = 0; // Trả về 0 thay vì làm sập cả API
                }

                // 3. Lấy danh sách danh mục kèm đếm số lượng sự kiện
                try {
                    $allCategories = Category::all();
                    foreach ($allCategories as $cat) {
                        $count = 0;
                        try {
                            $count = Event::where('category_id', $cat->id)
                                          ->where('organizer_id', $organizerId)
                                          ->count();
                        } catch (\Exception $subEx) {
                            Log::error("Dashboard Stats Sub-Error (Counting category ID {$cat->id} failed): " . $subEx->getMessage());
                            $count = 0; 
                        }

                        $categories[] = [
                            'id' => $cat->id,
                            'name' => $cat->name,
                            'events_count' => $count
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error("Dashboard Stats Error (Category main loop failed): " . $e->getMessage());
                    // Nếu lỗi nặng, cố gắng trả về mảng danh mục thô không kèm đếm số lượng
                    try {
                        $categories = Category::all()->map(function($c) {
                            return [
                                'id' => $c->id,
                                'name' => $c->name,
                                'events_count' => 0
                            ];
                        })->toArray();
                    } catch (\Exception $fallbackEx) {
                        $categories = [];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Dashboard Controller Global Crash: " . $e->getMessage());
        }

        // LUÔN LUÔN trả về HTTP 200 kèm cấu trúc JSON sạch để Front-end không bị lỗi 500
        return response()->json([
            'stats' => [
                'total_events' => $totalEvents,
                'total_participants' => $participants,
                'active_events' => $activeEvents,
            ],
            'categories' => $categories
        ], 200);
    }
}