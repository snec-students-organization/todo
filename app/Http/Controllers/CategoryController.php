<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\ActivityLog;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        // Load global categories AND user-owned categories
        $categories = Category::whereNull('user_id')
            ->orWhere('user_id', $user->id)
            ->orderByRaw('user_id IS NULL DESC, name ASC')
            ->get();

        return view('categories.index', compact('categories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7', // Hex color (e.g. #4F46E5)
            'icon' => 'required|string|max:50',  // FontAwesome class name
        ]);

        $user = $request->user();

        $category = Category::create([
            'user_id' => $user->id, // Regular users create owned categories
            'name' => $request->name,
            'color' => $request->color,
            'icon' => $request->icon,
        ]);

        ActivityLog::log($user->id, "Created Category: {$category->name}", "Color: {$category->color}");

        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:50',
        ]);

        $user = $request->user();

        // Security check: Only admins can edit global categories (where user_id is null)
        if ($category->user_id === null && !$user->isAdmin()) {
            return redirect()->route('categories.index')->with('error', 'You are not authorized to edit global categories.');
        }

        // Security check: Regular users can only edit their own categories
        if ($category->user_id !== null && $category->user_id !== $user->id && !$user->isAdmin()) {
            return redirect()->route('categories.index')->with('error', 'You do not own this category.');
        }

        $category->update($request->only('name', 'color', 'icon'));

        ActivityLog::log($user->id, "Updated Category: {$category->name}");

        return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Request $request, Category $category)
    {
        $user = $request->user();

        // Security check: Only admins can delete global categories
        if ($category->user_id === null && !$user->isAdmin()) {
            return redirect()->route('categories.index')->with('error', 'You are not authorized to delete global categories.');
        }

        // Security check: Regular users can only delete their own categories
        if ($category->user_id !== null && $category->user_id !== $user->id && !$user->isAdmin()) {
            return redirect()->route('categories.index')->with('error', 'You do not own this category.');
        }

        $categoryName = $category->name;
        $category->delete();

        ActivityLog::log($user->id, "Deleted Category: {$categoryName}");

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully!');
    }
}
