<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // 1. GET /api/categories - Lấy danh sách danh mục cho dropdown hoặc bảng
    public function index()
    {
        // Lấy danh mục kèm đếm số lượng sự kiện. 
        // Nếu Model Category chưa setup quan hệ với Event, hãy đổi thành: Category::all();
        $categories = Category::withCount('events')->get();

        return response()->json([
            'categories' => $categories
        ], 200);
    }

    // 2. POST /api/categories - Thêm mới danh mục (Sẽ fix triệt để cái lỗi Đỏ lòm ở Front-end)
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

        // Trả về đúng cấu trúc response mà Front-end React đang chờ (.data.data)
        return response()->json([
            'data' => $category
        ], 201);
    }

    // 3. PUT /api/categories/{id} - Chỉnh sửa danh mục
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Không tìm thấy danh mục'], 404);
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

    // 4. DELETE /api/categories/{id} - Xóa danh mục
    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Không tìm thấy danh mục'], 404);
        }

        $category->delete();

        return response()->json([
            'message' => 'Xóa danh mục thành công!'
        ], 200);
    }
}