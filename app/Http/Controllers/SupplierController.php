<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Supplier::query();
        if ($request->filled('text_search')) {
            $query->where('name', 'like', '%' . $request->text_search . '%');
        }

        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }
        if ($request->filled('gender_filter')) {
            $query->where('gender', $request->gender_filter);
        }

        return response()->json([
            'list' => $query->orderBy('id', 'desc')->get(),

        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the input
        $data = $request->validate([
            "name" => "required|string",
            "image" => "nullable|mimes:jpeg,jpg,png,svg,gif|max:2048",
            "phone" => "nullable|string",
            "address" => "nullable|string",
            "email" => "nullable|email",
            "website" => "nullable|url",
            "status" => "required|in:active,inactive",
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('suppliers', 'public');
        }

        // Create supplier
        $supplier = Supplier::create($data);

        return response()->json([
            "data" => $supplier,
            "message" => "Data Created Successfully"
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return response()->json([
                "message" => "Data Not Found"
            ]);
        } else {
            return response()->json([
                "data" => $supplier,
                "message" => "Data Found"
            ]);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return response()->json([
                "message" => "Data Not Found"
            ], 404);
        }

        $data = $request->validate([
            "name" => "required|string",
            "image" => "nullable|mimes:jpeg,jpg,png,svg,gif|max:2048",
            "phone" => "nullable|string",
            "address" => "nullable|string",
            "email" => "nullable|email",
            "website" => "nullable|url",
            "status" => "required|in:active,inactive",
        ]);

        if ($request->hasFile('image')) {
            if ($supplier->image) {
                Storage::disk('public')->delete($supplier->image);
            }
            $data['image'] = $request->file('image')->store('suppliers', 'public');
        }

        $supplier->update($data);

        return response()->json([
            "data" => $supplier->fresh(),
            "message" => "Data Updated Successfully"
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return response()->json([
                "message" => "Data Not Found"
            ]);
        } else {
            if ($supplier->image) {
                Storage::disk('public')->delete($supplier->image);
            }
            $supplier->delete();
            return response()->json([
                "message" => "Supplier deleted successfully"
            ]);
        }
    }
}
