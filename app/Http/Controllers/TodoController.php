<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    // Retrieve all to-dos for authenticated user
    public function index(Request $request)
    {
        $user = $request->user();

        // Optional filters
        $query = Todo::where('user_id', $user->id);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('dueDate')) {
            $query->whereDate('dueDate', $request->dueDate);
        }

        $todos = $query->get();

        return response()->json($todos);

    }

    // Retrieve details of a specific to-do by ID
    public function show($id)
    {
        $user = auth()->user();

        $todo = Todo::where('id', $id)
                    ->where('user_id', $user->id)
                    ->first();

        if (!$todo) {
            return response()->json(['message' => 'To-Do not found'], 404);
        }

        return response()->json($todo);
    }

    // Create a new to-do item
    public function store(Request $request)
    {
        $user = $request->user();

        $fields = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'dueDate' => 'nullable|date',
            'priority' => 'nullable|string|in:low,medium,high',
            'status' => 'nullable|string|in:pending,in-progress,completed',
            'category' => 'nullable|string|max:255',
        ]);

        $todo = new Todo();
        $todo->user_id = $user->id;
        $todo->title = $fields['title'];
        $todo->description = $fields['description'] ?? null;
        $todo->dueDate = $fields['dueDate'] ?? null;
        $todo->priority = $fields['priority'] ?? 'medium';
        $todo->status = $fields['status'] ?? 'pending';
        $todo->category = $fields['category'] ?? null;

        $todo->save();

        return response()->json($todo, 201);
    }

    // Update an existing to-do item by ID
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $todo = Todo::where('id', $id)
                    ->where('user_id', $user->id)
                    ->first();

        if (!$todo) {
            return response()->json(['message' => 'To-Do not found'], 404);
        }

        $fields = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'dueDate' => 'nullable|date',
            'priority' => 'nullable|string|in:low,medium,high',
            'status' => 'nullable|string|in:pending,in-progress,completed',
            'category' => 'nullable|string|max:255',
        ]);

        $todo->fill($fields);
        $todo->save();

        return response()->json($todo);
    }

    // Delete a to-do item by ID
    public function destroy($id)
    {
        $user = auth()->user();

        $todo = Todo::where('id', $id)
                    ->where('user_id', $user->id)
                    ->first();

        if (!$todo) {
            return response()->json(['message' => 'To-Do not found'], 404);
        }

        $todo->delete();

        return response()->json(['message' => 'To-Do deleted successfully']);
    }

    // Mark a to-do as completed
    public function markComplete($id)
    {
        $user = auth()->user();

        $todo = Todo::where('id', $id)
                    ->where('user_id', $user->id)
                    ->first();

        if (!$todo) {
            return response()->json(['message' => 'To-Do not found'], 404);
        }

        $todo->status = 'completed';
        $todo->save();

        return response()->json(['message' => 'To-Do marked as completed', 'todo' => $todo]);
    }
}
