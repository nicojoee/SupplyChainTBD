<?php

namespace App\Http\Controllers;

use App\Models\Factory;
use App\Models\FactoryProduct;
use App\Models\SupplierProduct;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class FactoryController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $factory = $user->factory;
        
        if (!$factory) {
            return view('factory.setup');
        }

        $products = $factory->products()->with('product')->get();
        $availableSupplierProducts = SupplierProduct::with(['supplier', 'product'])->get();
        $orders = Order::where('buyer_type', 'factory')
            ->where('buyer_id', $factory->id)
            ->with('items.product')
            ->latest()
            ->get();
        
        return view('factory.index', compact('factory', 'products', 'availableSupplierProducts', 'orders'));
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
            'production_capacity' => 'required|integer|min:0',
        ]);

        Factory::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'description' => $request->description,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'phone' => $request->phone,
            'production_capacity' => $request->production_capacity,
        ]);

        return redirect()->route('factory.index')->with('success', 'Factory profile created successfully.');
    }

    public function buyFromSupplier(Request $request)
    {
        $request->validate([
            'supplier_product_id' => 'required|exists:supplier_products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $factory = auth()->user()->factory;
        $supplierProduct = SupplierProduct::with('supplier')->findOrFail($request->supplier_product_id);

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'buyer_type' => 'factory',
            'buyer_id' => $factory->id,
            'seller_type' => 'supplier',
            'seller_id' => $supplierProduct->supplier_id,
            'status' => 'pending',
            'total_amount' => $supplierProduct->price * $request->quantity,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $supplierProduct->product_id,
            'quantity' => $request->quantity,
            'unit_price' => $supplierProduct->price,
            'subtotal' => $supplierProduct->price * $request->quantity,
        ]);

        return back()->with('success', 'Order placed successfully.');
    }

    public function addProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'price' => 'required|numeric|min:0',
            'production_quantity' => 'required|integer|min:0',
        ]);

        $factory = auth()->user()->factory;

        FactoryProduct::updateOrCreate(
            ['factory_id' => $factory->id, 'product_id' => $request->product_id],
            ['price' => $request->price, 'production_quantity' => $request->production_quantity]
        );

        return back()->with('success', 'Product added/updated successfully.');
    }
}
