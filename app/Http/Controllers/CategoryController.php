<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // 1. GET /api/categories
    public function index()
    {
        try {
            // Lấy danh mục kèm số lượng event dựa trên quan hệ category_id
            $categories = Category::withCount('events')->orderByDesc('id')->get();

            // Nếu Frontend của bạn yêu cầu mảng JSON trực tiếp, hãy dùng: return response()->json($categories);
            return response()->json([
                'categories' => $categories
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'categories' => Category::orderByDesc('id')->get()->map(function($cat) {
                    $cat->events_count = 0;
                    return $cat;
                })
            ], 200);
        }
    }

    // 2. POST /api/categories - Chặn trùng tên khi tạo
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name|max:255',
        ], [
            'name.required' => 'Please enter a category name.',
            'name.unique' => 'This category name already exists.',
        ]);

        $category = Category::create([
            'name' => $request->name
        ]);

        return response()->json([
            'data' => $category
        ], 201);
    }

    // 3. PUT /api/categories/{id} - Cho phép sửa tên nhưng chặn nếu đã có event
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        $organizerId = $request->user()->id;
        $eventCount = Event::where('category_id', $id)
            ->where('organizer_id', $organizerId)
            ->count();

        if ($eventCount > 0) {
            return response()->json([
                'message' => 'Cannot edit this category because it has assigned events.'
            ], 400);
        }

        $request->validate([
            'name' => 'required|string|unique:categories,name,'.$id.'|max:255',
        ], [
            'name.required' => 'Please enter a category name.',
            'name.unique' => 'This category name already exists.',
        ]);

        $category->update([
            'name' => $request->name
        ]);

        return response()->json([
            'data' => $category
        ], 200);
    }

    // 4. DELETE /api/categories/{id} - Chặn xóa nếu có sự kiện thuộc danh mục
    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        $organizerId = request()->user()->id;
        $eventCount = Event::where('category_id', $id)
            ->where('organizer_id', $organizerId)
            ->count();

        if ($eventCount > 0) {
            return response()->json([
                'message' => "Cannot delete this category because it has {$eventCount} assigned events."
            ], 400);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully!'
        ], 200);
    }
}
