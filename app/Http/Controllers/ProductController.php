<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use GuzzleHttp\Handler\Proxy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::query();
        if ($request->has('category_id')) {
            $query->where('category_id', "=", $request->input('category_id'));
        }
        if ($request->has('brand_id')) {
            $query->where('brand_id', "=", $request->input('brand_id'));
        }
        if ($request->has('text_search')) {
            $query->where('prd_name', 'LIKE', '%' . $request->input('text_search') . '%');
        }
        if ($request->has('status_filter')) {
            $query->where('status', '=', $request->input('status_filter'));
        }
        $product = $query->with(['brand', 'category'])->get();
        return response()->json([
            'list' => $product,
            'category' => Category::all(),
            'brand' => Brand::all()
        ], 200);
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
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'prd_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'required|boolean'
        ]);
        $data = $request->all();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }
        $product = Product::create($data);
        return response()->json([
            "data" => $product,
            "message" => "Product created successfully",
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                "message" => "Product not found"
            ], 404);
        } else {
            return response()->json([
                "data" => $product->load(['brand', 'category']),
                "message" => "Product found"
            ], 200);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                "message" => "Product not found"
            ], 404);
        } else {

            $request->validate([
                'category_id' => 'required|exists:categories,id',
                'brand_id' => 'required|exists:brands,id',
                'prd_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'quantity' => 'required|integer',
                'price' => 'required|numeric',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'status' => 'required|boolean'
            ]);
            $data = $request->all();
            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::disk('public')->delete(($product->image));
                }
                $data['image'] = $request->file('image')->store('products', 'public');
            }
            $product = $product->update($data);
            return response()->json([
                "data" => $data,
                "message" => "Product updated successfully",
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                "message" => "Product not found"
            ], 404);
        } else {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->delete();
            return response()->json([
                "message" => "Product deleted successfully"
            ], 200);
        }
    }


    /**
     * Reduce product stock after POS checkout
     */
    public function reduceStock(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        // Prevent negative stock
        if ($product->quantity < $request->quantity) {
            return response()->json([
                'message' => 'Not enough stock available'
            ], 400);
        }

        //  Reduce stock
        $product->quantity -= $request->quantity;
        $product->save();

        return response()->json([
            'message' => 'Stock updated successfully',
            'data' => $product
        ], 200);
    }
}
