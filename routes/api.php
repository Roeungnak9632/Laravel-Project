<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerTypeController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeePayrollController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PayrollMonthController;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('users', UserController::class);
    // Route Roles
    Route::apiResource('roles', RoleController::class);

    Route::post('products/{id}/reduce-stock', [ProductController::class, 'reduceStock']);
    // Route Categories
    Route::apiResource('categories', CategoryController::class);
    // Route Province
    Route::apiResource('provinces', ProvinceController::class);
    // Rotue Brnad
    Route::apiResource('brands', BrandController::class);
    // Rotue Product
    Route::apiResource('products', ProductController::class);
    Route::apiResource('employee', EmployeeController::class);
    Route::apiResource('payroll', PayrollMonthController::class);
    Route::apiResource('employee-payroll', EmployeePayrollController::class);
    Route::get('employee-payroll/payroll/{id}', [EmployeePayrollController::class, 'getByPayroll']);
    Route::apiResource('expense-type', ExpenseTypeController::class);
    Route::apiResource('expense', ExpenseController::class);
    Route::apiResource('customer/customer-type', CustomerTypeController::class);
    Route::apiResource('customer', CustomerController::class);
    Route::get('orders', [OrderController::class, 'getAllOrders']);
    Route::get('orders/{id}', [OrderController::class, 'getOrderById']);
    Route::post('orders/checkout', [OrderController::class, 'checkout']);

    Route::get('/search', [OrderController::class, 'searchOrders']);
    Route::put('/{id}/status', [OrderController::class, 'updateOrderStatus']);
    Route::apiResource('supplier', SupplierController::class);
    Route::get('/top-selling', [OrderController::class, 'topSelling']);
    Route::get('/order-stats', [OrderController::class, 'getOrderStats']);
    Route::get('expenses/total-summary', [ExpenseController::class, 'getTotalSummary']);
});
// Route Auth register
Route::post('/register', [AuthController::class, 'register']);
// Route Auth Login
Route::post('/login', [AuthController::class, 'login']);
