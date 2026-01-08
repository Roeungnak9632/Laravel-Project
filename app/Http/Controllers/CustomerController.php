<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerType;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // LIST
    public function index(Request $request)
    {
        $query = Customer::with('customerType');

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
            'customer_type' => CustomerType::all()
        ]);
    }

    // STORE
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_type_id' => 'required|exists:customer_types,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'nullable|string|unique:customers,phone',
            'address' => 'nullable|string',
            'gender' => 'required|in:Male,Female,Other',
            'status' => 'required|in:active,inactive',
        ]);

        $customer = Customer::create($data);

        return response()->json([
            'message' => 'Customer created successfully',
            'data' => $customer
        ], 201);
    }

    // SHOW
    public function show($id)
    {
        $customer = Customer::with('customerType')->find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        return response()->json(['data' => $customer]);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $data = $request->validate([
            'customer_type_id' => 'required|exists:customer_types,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $id,
            'phone' => 'nullable|string|unique:customers,phone,' . $id,
            'address' => 'nullable|string',
            'gender' => 'required|in:Male,Female,Other',
            'status' => 'required|in:active,inactive',
        ]);

        $customer->update($data);

        return response()->json([
            'message' => 'Customer updated successfully',
            'data' => $customer
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully']);
    }
}
