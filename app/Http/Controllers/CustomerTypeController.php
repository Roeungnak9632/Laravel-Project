<?php

namespace App\Http\Controllers;

use App\Models\CustomerType;
use Illuminate\Http\Request;

class CustomerTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $customer_type = CustomerType::query();

        if ($request->has("text_search")) {
            $customer_type->where("name", "like", "%" . $request->input("text_search") . "%");
        }

        $list = $customer_type->get();
        return response()->json([
            "list" => $list
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            "name" => "required|string:unique:customer_types,name",
            "description" => "nullable|string",
            "status" => "in:active,inactive"
        ]);
        $customerType = CustomerType::create($validate);
        return response()->json([
            "data" => $customerType,
            "message" => "Customer Type created successfully",
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $customerType = CustomerType::find($id);
        if (!$customerType) {
            return response()->json([
                "message" => "Customer Type not found"
            ], 404);
        }
        return response()->json([
            "data" => $customerType
        ]);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $customerType = CustomerType::find($id);
        if (!$customerType) {
            return response()->json([
                "message" => "Customer Type not found"
            ], 404);
        }
        $validate = $request->validate([
            "name" => "required|string:unique:customer_types,name," . $id,
            "description" => "nullable|string",
            "status" => "in:active,inactive"
        ]);
        $customerType->update($validate);
        return response()->json([
            "data" => $customerType,
            "message" => "Customer Type updated successfully",
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $customerType = CustomerType::find($id);
        if (!$customerType) {
            return response()->json([
                "message" => "Customer Type not found"
            ], 404);
        }
        $customerType->delete();
        return response()->json([
            "message" => "Customer Type deleted successfully"
        ]);
    }
}
