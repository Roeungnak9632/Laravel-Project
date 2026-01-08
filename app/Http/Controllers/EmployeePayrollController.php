<?php

namespace App\Http\Controllers;

use App\Models\EmployeePayroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeePayrollController extends Controller
{
    /**
     * Get payroll by payroll_id
     */
    public function getByPayroll($id)
    {
        $list = EmployeePayroll::where('payroll_id', $id)
            ->join('employees', 'employee_payrolls.employee_id', '=', 'employees.id')
            ->select(
                'employee_payrolls.*',
                DB::raw("CONCAT(employees.firstname, ' ', employees.lastname) AS employee_name")
            )
            ->get();

        return response()->json(['list' => $list]);
    }

    /**
     * List all payrolls
     */
    public function index()
    {
        $list = EmployeePayroll::with('employee')
            ->orderByDesc('id')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'employee_id' => $p->employee_id,
                'employee_name' => $p->employee->firstname . ' ' . $p->employee->lastname,
                'base_salary' => $p->base_salary,
                'ot' => $p->ot,
                'food' => $p->food,
                'transport' => $p->transport,
                'net_salary' => $p->net_salary,
            ]);

        return response()->json(['list' => $list]);
    }

    /**
     * Store payroll
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'payroll_id'  => 'required|exists:payroll_months,id',
            'employee_id' => 'required|exists:employees,id',
            'base_salary' => 'required|numeric|min:0',
            'ot'          => 'nullable|numeric|min:0',
            'food'        => 'nullable|numeric|min:0',
            'transport'   => 'nullable|numeric|min:0',
        ]);

        // prevent duplicate per month
        $exists = EmployeePayroll::where('payroll_id', $data['payroll_id'])
            ->where('employee_id', $data['employee_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Employee already has payroll for this month'
            ], 422);
        }

        $payroll = EmployeePayroll::create($data);

        return response()->json([
            'data' => $payroll,
            'message' => 'Payroll created'
        ], 201);
    }

    /**
     * Show payroll
     */
    public function show($id)
    {
        $payroll = EmployeePayroll::with('employee')->find($id);

        if (!$payroll) {
            return response()->json(['message' => 'Payroll not found'], 404);
        }

        return response()->json(['data' => $payroll]);
    }

    /**
     * Update payroll (THIS FIXES 422)
     */
    public function update(Request $request, $id)
    {
        $payroll = EmployeePayroll::find($id);

        if (!$payroll) {
            return response()->json(['message' => 'Payroll not found'], 404);
        }

        $data = $request->validate([
            'employee_id' => 'sometimes|required|exists:employees,id',
            'base_salary' => 'sometimes|required|numeric|min:0',
            'ot'          => 'nullable|numeric|min:0',
            'food'        => 'nullable|numeric|min:0',
            'transport'   => 'nullable|numeric|min:0',
        ]);

        $employeeId = $data['employee_id'] ?? $payroll->employee_id;

        // prevent duplicate payroll in same month
        $exists = EmployeePayroll::where('payroll_id', $payroll->payroll_id)
            ->where('employee_id', $employeeId)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Employee already has payroll for this month'
            ], 422);
        }

        $payroll->update($data);

        return response()->json([
            'data' => $payroll,
            'message' => 'Payroll updated'
        ]);
    }

    /**
     * Delete payroll
     */
    public function destroy($id)
    {
        $payroll = EmployeePayroll::find($id);

        if (!$payroll) {
            return response()->json(['message' => 'Payroll not found'], 404);
        }

        $payroll->delete();

        return response()->json(['message' => 'Payroll deleted']);
    }
}
