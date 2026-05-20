<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event; // ĐẢM BẢO PHẢI CÓ DÒNG NÀY ĐỂ KHÔNG BỊ LỖI CRASH ROUTE
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // 1. GET /api/categories
    public function index()
    {
        try {
            $categories = Category::withCount('events')->get();
            return response()->json([
                'categories' => $categories
            ], 200);
        } catch (\Exception $e) {
            // Bao phòng thủ nếu bảng events chưa có cấu trúc liên kết
            return response()->json([
                'categories' => Category::all()->map(function($cat) {
                    $cat->events_count = 0;
                    return $cat;
                })
            ], 200);
        }
    }

    // 2. POST /api/categories - Luật 1: Chặn trùng tên khi tạo
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name|max:255',
        ], [
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'name.unique' => 'Tên danh mục này đã tồn tại.',
        ]);

        $category = Category::create([
            'name' => $request->name
        ]);

        return response()->json([
            'data' => $category
        ], 201);
    }

    // 3. PUT /api/categories/{id} - Luật 3: Cho phép sửa tên nhưng chặn trùng với mục khác
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Không tìm thấy danh mục để chỉnh sửa.'], 404);
        }

        $request->validate([
            'name' => 'required|string|unique:categories,name,'.$id.'|max:255',
        ], [
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'name.unique' => 'Tên danh mục này đã tồn tại.',
        ]);

        $category->update([
            'name' => $request->name
        ]);

        return response()->json([
            'data' => $category
        ], 200);
    }

    // 4. DELETE /api/categories/{id} - Luật 2: Chặn xóa nếu có sự kiện thuộc danh mục (>0)
    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Không tìm thấy danh mục cần xóa.'], 404);
        }

        // Đếm an toàn số lượng sự kiện thuộc danh mục này
        $eventCount = 0;
        try {
            $eventCount = Event::where('category_id', $id)->count();
        } catch (\Exception $e) {
            $eventCount = 0; 
        }

        // Nếu có sự kiện gán vào danh mục này thì chặn ngay lập tức
        if ($eventCount > 0) {
            return response()->json([
                'message' => "Không thể xóa danh mục này! Hiện đang có {$eventCount} sự kiện thuộc loại danh mục này."
            ], 400); 
        }

        $category->delete();

        return response()->json([
            'message' => 'Xóa danh mục thành công!'
        ], 200);
    }
}