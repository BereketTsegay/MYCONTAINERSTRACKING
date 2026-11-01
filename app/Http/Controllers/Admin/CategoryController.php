<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support; //- Support for Str helpers;

class CategoryController extends Controller
{
      public function index() { return response()->json(Category::latest()->get()); }

    public function store(Request $request) {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:pklist,house_bill,edpass'
        ]);
        $data['slug'] = \Illuminate\Support\Str::slug($data['name']) . '-' . rand(100,999);
        $category = Category::create($data);
        return response()->json(['status' => 'success', 'data' => $category], 201);
    }

    public function destroy(Category $category) {
        $category->delete(); return response()->json(['status' => 'success']);
    }
}
