<?php

namespace App\Http\Controllers;

use App\Models\Distributor;
use App\Models\DistributorStock;
use App\Models\FactoryProduct;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class DistributorController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $distributor = $user->distributor;
        
        if (!$distributor) {
            return view('distributor.setup');
        }

        $stocks = $distributor->stocks()->with('product')->get();
        $availableFactoryProducts = FactoryProduct::with(['factory', 'product'])->get();
        $orders = Order::where('buyer_type', 'distributor')
            ->where('buyer_id', $distributor->id)
            ->with('items.product')
            ->latest()
            ->get();
        
        return view('distributor.index', compact('distributor', 'stocks', 'availableFactoryProducts', 'orders'));
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
            'warehouse_capacity' => 'required|integer|min:0',
        ]);

        Distributor::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'description' => $request->description,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'phone' => $request->phone,
            'warehouse_capacity' => $request->warehouse_capacity,
        ]);

        return redirect()->route('distributor.index')->with('success', 'Distributor profile created successfully.');
    }

    public function buyFromFactory(Request $request)
    {
        $request->validate([
            'factory_product_id' => 'required|exists:factory_products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $distributor = auth()->user()->distributor;
        $factoryProduct = FactoryProduct::with('factory')->findOrFail($request->factory_product_id);

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'buyer_type' => 'distributor',
            'buyer_id' => $distributor->id,
            'seller_type' => 'factory',
            'seller_id' => $factoryProduct->factory_id,
            'status' => 'pending',
            'total_amount' => $factoryProduct->price * $request->quantity,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $factoryProduct->product_id,
            'quantity' => $request->quantity,
            'unit_price' => $factoryProduct->price,
            'subtotal' => $factoryProduct->price * $request->quantity,
        ]);

        return back()->with('success', 'Order placed successfully.');
    }
}
