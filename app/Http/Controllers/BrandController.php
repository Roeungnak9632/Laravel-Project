<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $brand = Brand::query();
        if ($request->has("text_search")) {
            $brand->where("name", "like", "%" . $request->input("text_search") . "%");
        }
        if ($request->has("status_filter")) {
            $brand->where("status", "=", $request->input("status_filter"));
        }

        $list = $brand
            ->select(['id', 'name', 'code', 'from_country', 'status', 'image'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            "list" => $list
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|string|max:255",
            "code" => "required|string|max:255|unique:brands,code",
            "from_country" => "required|string|max:255",
            "image" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048",  ///1024 = 1MB
            "status" => "required|in:active,inactive"
        ]);
        $imagePaht = null;
        if ($request->hasFile('image')) {
            $imagePaht = $request->file('image')->store('brands', 'public');
        }
        $brand = Brand::create([
            'name' => $request->name,
            'code' => $request->code,
            'from_country' => $request->from_country,
            'image' => $imagePaht,
            'status' => $request->status
        ]);

        return response()->json([
            "data" => $brand,
            "message" => "Data Inserted Successfully",
            "status" => 200
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json([
                "message" => "Data Not Found",
                "status" => 404
            ], 404);
        } else {
            return response()->json([
                "data" => $brand,
                "message" => "Data Found Successfully",
                "status" => 200
            ], 200);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)

    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json([
                "message" => "Data Not Found",
                "status" => 404
            ], 404);
        } else {
            $request->validate([
                "name" => "required|string|max:255",
                "code" => "required|string|max:255|unique:brands,code," . $id,
                "from_country" => "required|string|max:255",
                "image" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048",  ///1024 = 1MB
                "status" => "required|in:active,inactive",
                "image_remove" => "nullable|string"
            ]);
            // Handle the image upload
            if ($request->hasFile('image')) {
                /// Delete the old image if it exitsts
                if ($brand->image) {
                    Storage::disk('public')->delete($brand->image);
                }
                // Store the new image
                $imagePaht = $request->file('image')->store('brands', 'public');
                $brand->image = $imagePaht;
            } else if ($request->image_remove) {
                //Deelet the old image if it exitsts
                Storage::disk('public')->delete($request->image_remove);
                $brand->image = null;
            }
            $brand->update([
                'name' => $request->name,
                'code' => $request->code,
                'from_country' => $request->from_country,
                'status' => $request->status
            ]);
            return response()->json([
                'image_remove' => $request->image_remove,
                "data" => $brand,
                "message" => "Data Updated Successfully",
                "status" => 200
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json([
                "message" => "Data Not Found",
                "status" => 404
            ], 404);
        }

        // delete image if exists
        if (!empty($brand->image)) {
            Storage::disk('public')->delete($brand->image);
        }

        // delete database row
        $brand->delete();

        return response()->json([
            "message" => "Data Deleted Successfully",
            "status" => 200
        ], 200);
    }
}
