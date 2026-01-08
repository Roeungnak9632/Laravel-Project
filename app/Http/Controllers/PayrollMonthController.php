<?php

namespace App\Http\Controllers;

use App\Models\PayrollMonth;
use Illuminate\Http\Request;

class PayrollMonthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            "list" => PayrollMonth::all()
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'approved_by' => 'nullable|in:admin,HR,cashier',
            'monthly'     => 'required|string|max:255',
            'date_month'  => 'required|date',
            'status'      => 'required|in:pending,approved,draft',
        ]);

        $payroll = PayrollMonth::create($validated);

        return response()->json([
            'data' => $payroll,
            'message' => 'Created data successfully'
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $payroll = PayrollMonth::find($id);
        if (!$payroll) {
            return response()->json([
                "message" => "Not Found Data"
            ]);
        } else {
            return response()->json([
                "data" => $payroll,
            ], 200);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'approved_by' => 'nullable|in:admin,HR,cashier',
            'monthly'     => 'required|string|max:255',
            'date_month'  => 'required|date',
            'status'      => 'required|in:pending,approved,draft',
        ]);

        $payroll = PayrollMonth::findOrFail($id);
        $payroll->update($validated);

        return response()->json([
            'data' => $payroll,
            'message' => 'Updated successfully'
        ]);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $payroll = PayrollMonth::find($id);
        if (!$payroll) {
            return response()->json([
                "message" => "Data Not Found"
            ]);
        } else {
            $payroll->delete();
            return response()->json([
                "data" => $payroll,
                "message" => "Data delected succefully"
            ]);
        }
    }
}
