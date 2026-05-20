<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event; // Import model Event để kiểm tra số lượng sự kiện
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // 1. GET /api/categories - Lấy danh sách danh mục kèm số lượng sự kiện
    public function index()
    {
        // Sử dụng với withCount('events') để trả về events_count cho Front-end
        $categories = Category::withCount('events')->get();

        return response()->json([
            'categories' => $categories
        ], 200);
    }

    // 2. POST /api/categories - LUẬT 1: Thêm mới danh mục (Chặn trùng tên)
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

    // 3. PUT /api/categories/{id} - LUẬT 3: Sửa tên danh mục (Chặn trùng với các mục khác trừ chính nó)
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

    // 4. DELETE /api/categories/{id} - LUẬT 2: Xóa danh mục (Chặn nếu có sự kiện > 0)
    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Không tìm thấy danh mục cần xóa.'], 404);
        }

        // Đếm xem có bao nhiêu sự kiện đang gán vào danh mục này
        $eventCount = Event::where('category_id', $id)->count();

        // Nếu có sự kiện lớn hơn 0 thì chặn ngay lập tức
        if ($eventCount > 0) {
            return response()->json([
                'message' => "Không thể xóa danh mục này! Hiện đang có {$eventCount} sự kiện thuộc loại danh mục này."
            ], 400); // Trả về lỗi 400 Bad Request để kích hoạt Toast đỏ bên React
        }

        $category->delete();

        return response()->json([
            'message' => 'Xóa danh mục thành công!'
        ], 200);
    }
}