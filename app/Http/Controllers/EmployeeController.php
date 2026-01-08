<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $query = Employee::query();

        // Text search
        if ($request->filled('text_search')) {
            $query->where(function ($q) use ($request) {
                $q->where('firstname', 'like', '%' . $request->text_search . '%')
                    ->orWhere('lastname', 'like', '%' . $request->text_search . '%');
            });
        }


        $list = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'list' => $list
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'card_id'   => 'required|string|max:255|unique:employees,card_id',
            'firstname' => 'required|string',
            'lastname'  => 'required|string',
            'dob'       => 'nullable|date',
            'email'     => 'required|string|email',
            'telephone' => 'nullable|string',
            'position'  => 'required|string',
            'salary'    => 'required|numeric',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address'   => 'nullable|string',
        ]);
        $data = $request->all();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('employees', 'public');
        }
        $employee = Employee::create($data);
        return response()->json([
            "data" => $employee,
            "message" => "Created Employee Successfully"
        ]);
    }


    public function show($id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json([
                "meessage" => "Employee Not found"
            ]);
        } else {
            return response()->json([
                "data" => $employee,
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::find($id);
        $request->validate([
            'card_id'   => 'required|string|max:255|unique:employees,card_id,' . $id,
            'firstname' => 'required|string',
            'lastname'  => 'required|string',
            'dob'       => 'nullable|date',
            'email'     => 'required|string|email',
            'telephone' => 'nullable|string',
            'position'  => 'required|string',
            'salary'    => 'required|numeric',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address'   => 'nullable|string',
        ]);
        $data = $request->all();
        if ($request->hasFile('image')) {
            if ($employee->image) {
                Storage::disk('public')->delete($employee->image);
            }
            $data['image'] = $request->file('image')->store('employees', 'public');
        }
        $employee->update($data);
        return response()->json([
            "data" => $employee,
            "message" => "Updated Employee Successfully"
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json([
                "message" => "Employee Not Found"
            ], 404);
        }

        // Delete image if exists
        if ($employee->image && Storage::disk('public')->exists($employee->image)) {
            Storage::disk('public')->delete($employee->image);
        }

        $employee->delete();

        return response()->json([
            "message" => "Employee Deleted Successfully"
        ], 200);
    }
}
