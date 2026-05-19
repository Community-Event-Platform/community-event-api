<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
   public function index(Request $request)
    {
        $totalEvents = Event::count(); 
        $activeEvents = Event::where('status', 'Published')->count(); // Hoặc điều kiện active của bạn
        $participants = 256; 

        // 2. Lấy danh sách danh mục kèm số lượng event của từng danh mục
        $categories = Category::withCount('events')->get(); 
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
