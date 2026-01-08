<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use Illuminate\Http\Request;

class ExpenseTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $expense_type = ExpenseType::query();

        if ($request->has("text_search")) {
            $expense_type->where("name", "like", "%" . $request->input("text_search") . "%");
        }

        $list = $expense_type->get();

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
            "name" => "required|string|unique:expense_types,name",
            "description" => "nullable|string",
        ]);
        $expense_type = ExpenseType::create($validate);
        return response()->json([
            "data" => $expense_type,
            "message" => "Data Created successfully"
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $expense_type = ExpenseType::find($id);
        if (!$expense_type) {
            return response()->json([
                "message" => "Data Not Found"
            ]);
        } else {
            return response()->json([
                "data" => $expense_type,
                "message" => "Data Found",
            ]);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $expense_type = ExpenseType::find($id);
        if (!$expense_type) {
            return response()->json([
                "message" => "Data Not Found"
            ]);
        } else {
            $validate = $request->validate([
                "name" => "required|string|unique:expense_types,name," . $id,
                "description" => "nullable|string",
            ]);
            $expense_type->update($validate);
            return response()->json([
                "data" => $expense_type,
                "message" => "Data Updated Successufully"
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $expense_type = ExpenseType::find($id);

        if (!$expense_type) {
            return response()->json([
                "message" => "Data Not Found"
            ], 404);
        }

        $expense_type->delete();

        return response()->json([
            "message" => "Data Deleted Successfully"
        ]);
    }
}
