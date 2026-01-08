<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $category = Category::query();
        if ($request->has("text_search")) {
            $category->where("name", "like", "%" . $request->input("text_search") . "%");
        }
        if ($request->has("status_filter")) {
            $category->where("status", "=", $request->input("status_filter"));
        }
        $list = $category
            ->select(['id', 'name', 'description', 'parent_id', 'status'])
            ->orderBy('id', 'desc')

            ->get();
        return response()->json([
            "list" =>  $list
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validation = $request->validate([
            "name" => "required|string|unique:categories,name",
            "description" => "nullable|string",
            "parent_id" => "nullable|numeric",
            "status" => "required|boolean"
        ]);
        $data = Category::create($validation);
        return response()->json([
            "data" => $data,
            "message" => "Data Inserted Successfully"
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $category = Category::find($category->id);
        if (!$category) {

            return response()->json([
                "error" => "Data Not Found",
                "status" => 404
            ]);
        } else {
            return response()->json([
                "data" => $category,
                "message" => "Data Found Successfully"
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $category = Category::find($category->id);
        if (!$category) {
            return [
                "message" => "Data Not Found",
                "status" => 404
            ];
        } else {
            $validation = $request->validate([
                "name" => "required|string|max:255|unique:categories,name," . $category->id,
                "description" => "nullable|string",
                "parent_id" => "nullable|numeric",
                "status" => "required|boolean"
            ]);
            $category->update($validation);
            return response()->json([
                "data" => $category,
                "message" => "Data Updated Successfully"
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category = Category::find($category->id);
        if (!$category) {
            return response()->json([
                "message" => "Data Not Found",
                "status" => 404
            ]);
        } else {
            $category->delete();
            return response()->json([
                "message" => "Data Deleted Successfully"
            ]);
        }
    }
}
