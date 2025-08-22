<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Retrieve all categories for authenticated user
    public function index(Request $request)
    {
        $user = $request->user();

        $categories = Category::where('user_id', $user->id)->get();

        return response()->json($categories);
    }

    // Create a new category/project
    public function store(Request $request)
    {
        $user = $request->user();

        $fields = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        $category = new Category();
        $category->user_id = $user->id;
        $category->name = $fields['name'];
        $category->save();

        return response()->json($category, 201);
    }

    // Update an existing category/project by ID
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $category = Category::where('id', $id)
                            ->where('user_id', $user->id)
                            ->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $fields = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
        ]);

        $category->name = $fields['name'];
        $category->save();

        return response()->json($category);
    }

    // Delete a category/project by ID
    public function destroy($id)
    {
        $user = auth()->user();

        $category = Category::where('id', $id)
                            ->where('user_id', $user->id)
                            ->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
