<?php

namespace App\Http\Controllers;

use App\Models\Province;
use Illuminate\Http\Request;


class ProvinceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $province = Province::query();
        if ($request->has("text_search")) {
            $province->where("name", "like", "%" . $request->input("text_search") . "%");
        }
        if ($request->has("status_filter")) {
            $province->where("status", "=", $request->input("status_filter"));
        }
        $list = $province->get();
        return response()->json([
            "list" =>  $list
        ]);
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
        $validation = $request->validate([
            "name" => "required|string",
            "code" => "required|string",
            "description" => "nullable|string",
            "distand_from_city" => "required|numeric",
            "status" => "required|boolean"
        ]);
        $data = Province::create($validation);
        return response()->json([
            "data" => $data,
            "message" => "Data Inserted Successfully",
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Province $province)
    {
        $province = Province::find($province->id);
        if (!$province) {
            return response()->json([
                "message" => "Data Not Found",
                "status" => 404
            ]);
        } else {

            return response()->json([
                "data" => $province,
                "message" => "Data Found Successfully"
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Province $province)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Province $province)
    {
        $update = Province::find($province->id);
        if (!$update) {
            return response()->json([
                "message" => "Data Not Found",
                "status" => 404
            ]);
        } else {
            $validation = $request->validate([
                "name" => "required|string",
                "code" => "required|string",
                "description" => "nullable|string",
                "distand_from_city" => "required|numeric",
                "status" => "required|boolean"
            ]);
            $province->update($validation);
            return response()->json([
                "data" => $province,
                "message" => "Data Updated Successfully",
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Province $province)
    {
        $delete = Province::find($province->id);
        if (!$delete) {
            return response()->json([
                "message" => "Data Not Found",
                "status" => 404
            ]);
        } else {
            $delete->delete();
            return response()->json([
                "message" => "Data Deleted Successfully"
            ]);
        }
    }
}
