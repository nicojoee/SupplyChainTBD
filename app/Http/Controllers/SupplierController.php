<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\Product;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $supplier = $user->supplier;
        
        if (!$supplier) {
            return view('supplier.setup');
        }

        $products = $supplier->products()->with('product')->get();
        
        return view('supplier.index', compact('supplier', 'products'));
    }

    public function setup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'phone' => 'nullable|string|max:20',
        ]);

        $supplier = Supplier::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'description' => $request->description,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'phone' => $request->phone,
        ]);

        return redirect()->route('supplier.index')->with('success', 'Supplier profile created successfully.');
    }

    public function addProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
        ]);

        $supplier = auth()->user()->supplier;

        SupplierProduct::updateOrCreate(
            ['supplier_id' => $supplier->id, 'product_id' => $request->product_id],
            ['price' => $request->price, 'stock_quantity' => $request->stock_quantity]
        );

        return back()->with('success', 'Product added/updated successfully.');
    }
}
