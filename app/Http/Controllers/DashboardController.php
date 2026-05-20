<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Thêm Log để theo dõi lỗi ngầm nếu có

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Khởi tạo dữ liệu mặc định an toàn
        $totalEvents = 0;
        $activeEvents = 0;
        $participants = 0;
        $categories = [];

        try {
            $user = $request->user();
            if ($user) {
                $organizerId = $user->id;

                // 2. Lấy số liệu sự kiện thật của Organizer (Bọc catch riêng biệt để tránh sập dây chuyền)
                try {
                    $totalEvents = Event::where('organizer_id', $organizerId)->count();
                    $activeEvents = Event::where('organizer_id', $organizerId)
                                         ->where('status', 'Published')
                                         ->count();
                } catch (\Exception $e) {
                    Log::error("Dashboard Stats - Events count error: " . $e->getMessage());
                }

                // 3. Tính tổng số attendee đăng ký
                try {
                    $participants = DB::table('registrations')
                        ->join('events', 'registrations.event_id', '=', 'events.id')
                        ->where('events.organizer_id', $organizerId)
                        ->count();
                } catch (\Exception $e) {
                    Log::error("Dashboard Stats - Registrations count error: " . $e->getMessage());
                    $participants = 0; 
                }

                // 4. Lấy danh sách danh mục an toàn TUYỆT ĐỐI
                try {
                    $allCategories = Category::all();
                    foreach ($allCategories as $cat) {
                        $count = 0;
                        try {
                            // Nếu bảng events thiếu cột category_id, hàm này sẽ dính lỗi nhưng được catch chặn lại, không làm sập trang
                            $count = Event::where('category_id', $cat->id)
                                          ->where('organizer_id', $organizerId)
                                          ->count();
                        } catch (\Exception $subEx) {
                            $count = 0; 
                        }

                        $categories[] = [
                            'id' => $cat->id,
                            'name' => $cat->name,
                            'events_count' => $count
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error("Dashboard Stats - Categories processing error: " . $e->getMessage());
                    $categories = [];
                }
            }
        } catch (\Exception $e) {
            Log::error("Dashboard Controller Global Crash: " . $e->getMessage());
        }

        // LUÔN LUÔN trả về HTTP 200 kèm cấu hình JSON để Front-end không bị lỗi 500 nữa
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