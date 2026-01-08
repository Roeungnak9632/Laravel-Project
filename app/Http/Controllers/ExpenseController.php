<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $query = Expense::with('expenseType');
        if ($request->filled('text_search')) {
            $query->where('name', 'like', '%' . $request->text_search . '%');
        }
        if ($request->filled('status_filter')) {
            $query->where('expense_status', $request->status_filter);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('expense_date', [
                $request->start_date,
                $request->end_date,
            ]);
        }

        $list = $query->orderBy('expense_date', 'desc')

            ->get();
        $expenseType = ExpenseType::all();

        return response()->json([
            'list' => $list,
            'expense_type' => $expenseType
        ]);
    }

    /**
     * Get expense summary for dashboard charts
     */
    public function summary(Request $request)
    {
        $filterBy = $request->get('filter_by', 'month'); // day, month, or year
        $day = $request->get('day');
        $month = $request->get('month');
        $year = $request->get('year', date('Y'));

        $query = Expense::query();

        // Apply filters based on filter_by
        if ($filterBy === 'day' && $day && $month) {
            // Filter by specific day
            $query->whereYear('expense_date', $year)
                ->whereMonth('expense_date', $month)
                ->whereDay('expense_date', $day);

            $labels = [date('Y-m-d', mktime(0, 0, 0, $month, $day, $year))];
        } elseif ($filterBy === 'month' && $month) {
            // Filter by specific month
            $query->whereYear('expense_date', $year)
                ->whereMonth('expense_date', $month);

            $labels = [];
            for ($i = 1; $i <= 31; $i++) {
                $date = date('Y-m-d', mktime(0, 0, 0, $month, $i, $year));
                if (date('m', strtotime($date)) == $month) {
                    $labels[] = 'Day ' . $i;
                } else {
                    break;
                }
            }
        } else {
            // Filter by year (all months)
            $query->whereYear('expense_date', $year);

            $labels = [
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December'
            ];
        }

        // Get data grouped by date/month
        $data = [];

        if ($filterBy === 'day' && $day && $month) {
            // Single day
            $total = $query->sum('amount');
            $data = [$total ?? 0];
        } elseif ($filterBy === 'month' && $month) {
            // Days in a month
            for ($i = 1; $i <= 31; $i++) {
                $date = date('Y-m-d', mktime(0, 0, 0, $month, $i, $year));
                if (date('m', strtotime($date)) == $month) {
                    $total = Expense::whereDate('expense_date', $date)->sum('amount');
                    $data[] = $total ?? 0;
                } else {
                    break;
                }
            }
        } else {
            // All months in year
            for ($i = 1; $i <= 12; $i++) {
                $total = Expense::whereYear('expense_date', $year)
                    ->whereMonth('expense_date', $i)
                    ->sum('amount');
                $data[] = $total ?? 0;
            }
        }

        return response()->json([
            'status' => true,
            'labels' => $labels,
            'data' => $data
        ]);
    }

    /**
     * Get total expense summary (for dashboard cards)
     */
    public function getTotalSummary(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month');

        $query = Expense::whereYear('expense_date', $year);

        if ($month) {
            $query->whereMonth('expense_date', $month);
        }

        $total = $query->sum('amount');
        $count = $query->count();

        return response()->json([
            'status' => true,
            'total' => $total ?? 0,
            'count' => $count ?? 0
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            "name" => "required|string",
            "descrition" => "required|string",
            "amount" => "required|numeric",
            "expenseType_id" => "required|exists:expense_types,id",
            "expense_status" => "required|in:pending,paid,cancel",
            "expense_date" => "required|date",
            "create_by" => "required|string",
        ]);

        $expense = Expense::create($validate);

        return response()->json([
            "status" => true,
            "data" => $expense,
            "message" => "Data Created successfully"
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $expense = Expense::find($id);

        if (!$expense) {
            return response()->json([
                "status" => false,
                "message" => "Data Not Found"
            ], 404);
        }

        return response()->json([
            "status" => true,
            "data" => $expense,
            "message" => "Data Found"
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $expense = Expense::find($id);

        if (!$expense) {
            return response()->json([
                "status" => false,
                "message" => "Data Not Found"
            ], 404);
        }

        $validate = $request->validate([
            "name" => "required|string",
            "descrition" => "required|string",
            "amount" => "required|numeric",
            "expenseType_id" => "required|exists:expense_types,id",
            "expense_status" => "required|in:pending,paid,cancel",
            "expense_date" => "required|date",
            "create_by" => "required|string",
        ]);

        $expense->update($validate);

        return response()->json([
            "status" => true,
            "data" => $expense,
            "message" => "Data Updated Successfully"
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $expense = Expense::find($id);

        if (!$expense) {
            return response()->json([
                "status" => false,
                "message" => "Data Not Found"
            ], 404);
        }

        $expense->delete();

        return response()->json([
            "status" => true,
            "data" => $expense,
            "message" => "Data Deleted Successfully"
        ]);
    }
}
