<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        try {

            $request->validate([
                'invoice_no'       => 'nullable|string',
                'customer_id'      => 'nullable|exists:customers,id',
                'sub_total'        => 'required|numeric',
                'tax'              => 'nullable|numeric',
                'discount'         => 'nullable|numeric',
                'total_amount'     => 'required|numeric',
                'paid_amount'      => 'nullable|numeric',
                'change_due'       => 'nullable|numeric',
                'payment_method'   => 'required|string',
                'note'             => 'nullable|string',
                'items'            => 'required|array|min:1',
                'items.*.id' => 'required|integer',
                'items.*.price'    => 'required|numeric',
                'items.*.qty'      => 'required|integer|min:1',
                'items.*.name'     => 'nullable|string',
            ]);

            $order = DB::transaction(function () use ($request) {

                // 2️ Generate invoice if not provided
                $invoice_no = $request->invoice_no ?? 'INV-' . date('Ymd-His');

                // 3️ Create Order
                $order = Order::create([
                    'invoice_no'     => $invoice_no,
                    'customer_id'    => $request->customer_id,
                    'sub_total'      => $request->sub_total,
                    'tax'            => $request->tax ?? 0,
                    'discount'       => $request->discount ?? 0,
                    'total_amount'   => $request->total_amount,
                    'paid_amount'    => $request->paid_amount,
                    'change_amount'  => $request->change_due,
                    'payment_method' => $request->payment_method,
                    'note'           => $request->note,
                    'status'         => 'paid',
                ]);

                // 4️ Process each item
                foreach ($request->items as $item) {

                    // Lock product to prevent overselling
                    $product = Product::where('id', $item['id'])->lockForUpdate()->firstOrFail();

                    if ($product->quantity < $item['qty']) {
                        throw new \Exception("Stock not enough for {$product->name}");
                    }

                    // 5️⃣ Create OrderItem
                    $order->items()->create([
                        'product_id' => $product->id,
                        'price'      => $item['price'],
                        'qty'        => $item['qty'],
                        'subtotal'   => $item['price'] * $item['qty'],
                    ]);

                    // 6️⃣ Reduce stock
                    $product->decrement('quantity', $item['qty']);
                }

                return $order;
            });

            // 7️⃣ Return success response with order details
            return response()->json([
                'status'  => true,
                'message' => 'Checkout successful',
                'order'   => $order->load('items.product') // include order items and products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // GET all orders with filters
    public function getAllOrders(Request $request)
    {
        try {
            // Start query
            $query = Order::with('items.product');

            // Text search filter (invoice_no or customer_id)
            if ($request->has('text_search') && !empty($request->text_search)) {
                $searchTerm = $request->text_search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('invoice_no', 'like', '%' . $searchTerm . '%')
                        ->orWhere('customer_id', 'like', '%' . $searchTerm . '%');
                });
            }

            // Status filter
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            // Date range filter (created_at)
            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Pagination parameters
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 50);
            $skip = ($page - 1) * $limit;

            // Get total count for pagination
            $total = $query->count();

            // Apply pagination
            $orders = $query->orderBy('created_at', 'desc')
                ->skip($skip)
                ->take($limit)
                ->get();

            return response()->json([
                'status' => true,
                'orders' => $orders,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => ceil($total / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching orders: ' . $e->getMessage()
            ], 500);
        }
    }

    // GET single order by ID
    public function getOrderById($id)
    {
        try {
            $order = Order::with('items.product')->find($id);

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching order: ' . $e->getMessage()
            ], 500);
        }
    }

    // In OrderController.php
    public function getOrderStats(Request $request)
    {
        try {
            $query = Order::query();

            // Apply filters
            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            $totalOrders = $query->count();
            $totalRevenue = (float) $query->whereIn('status', ['paid', 'completed'])->sum('total_amount');

            $today = Carbon::today();
            $todayOrders = Order::whereDate('created_at', $today)->count();
            $todayRevenue = (float) Order::whereDate('created_at', $today)
                ->whereIn('status', ['paid', 'completed'])
                ->sum('total_amount');

            return response()->json([
                'status' => true,
                'data' => [
                    'total_orders' => $totalOrders,
                    'total_revenue' => $totalRevenue,
                    'today_orders' => $todayOrders,
                    'today_revenue' => $todayRevenue,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    // Update order status
    public function updateOrderStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,paid,cancelled,refunded'
            ]);

            $order = Order::find($id);

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $order->status = $request->status;
            $order->save();

            return response()->json([
                'status' => true,
                'message' => 'Order status updated successfully',
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error updating order: ' . $e->getMessage()
            ], 500);
        }
    }


    public function searchOrders(Request $request)
    {
        try {
            $request->validate([
                'search_term' => 'required|string|min:1'
            ]);

            $searchTerm = $request->search_term;

            $orders = Order::with('items.product')
                ->where(function ($query) use ($searchTerm) {
                    $query->where('invoice_no', 'like', '%' . $searchTerm . '%')
                        ->orWhere('customer_id', 'like', '%' . $searchTerm . '%')
                        ->orWhereHas('items.product', function ($q) use ($searchTerm) {
                            $q->where('name', 'like', '%' . $searchTerm . '%');
                        });
                })
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            return response()->json([
                'status' => true,
                'orders' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error searching orders: ' . $e->getMessage()
            ], 500);
        }
    }



    public function topSelling(Request $request)
    {
        $limit = $request->get('limit', 10);
        $period = $request->get('period', 'month');

        $query = OrderItem::with(['product.category']);

        switch ($period) {
            case 'day':
                $query->whereDate('created_at', now());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
                break;
            case 'year':
                $query->whereYear('created_at', now()->year);
                break;
        }

        $data = $query
            ->select(
                'product_id',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(qty * price) as total_amount')
            )
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id'            => $item->product->id ?? null,
                    'prd_name'      => $item->product->prd_name ?? 'Deleted Product',
                    'sku'           => $item->product->sku ?? null,
                    'category_name' => $item->product->category->name ?? '-',
                    'total_qty'     => $item->total_qty,
                    'total_amount'  => $item->total_amount,
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
}
