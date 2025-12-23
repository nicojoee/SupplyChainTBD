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
            'latitude' => $request->latitude ?? 0,
            'longitude' => $request->longitude ?? 0,
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

    // AJAX purchase from map popup
    public function buyFromFactoryAjax(Request $request)
    {
        $request->validate([
            'seller_id' => 'required|exists:factories,id',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $distributor = auth()->user()->distributor;
        
        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'Distributor profile not found'], 400);
        }

        $totalAmount = 0;
        foreach ($request->items as $item) {
            $totalAmount += $item['quantity'] * $item['price'];
        }

        // Create order with status 'pending' - waiting for factory confirmation
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'buyer_type' => 'distributor',
            'buyer_id' => $distributor->id,
            'seller_type' => 'factory',
            'seller_id' => $request->seller_id,
            'status' => 'pending',
            'total_amount' => $totalAmount,
        ]);

        // Create order items
        foreach ($request->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'subtotal' => $item['quantity'] * $item['price'],
            ]);
        }

        return response()->json([
            'success' => true,
            'order_id' => $order->order_number,
            'message' => 'Order placed successfully'
        ]);
    }

    // My Orders - order history
    public function myOrders()
    {
        $distributor = auth()->user()->distributor;
        
        if (!$distributor) {
            return redirect()->route('distributor.index');
        }

        $orders = Order::where('buyer_type', 'distributor')
            ->where('buyer_id', $distributor->id)
            ->with(['items.product', 'sellerFactory'])
            ->latest()
            ->get();

        return view('distributor.orders', compact('distributor', 'orders'));
    }

    // Marketplace - view factory products
    public function marketplace()
    {
        $distributor = auth()->user()->distributor;
        
        if (!$distributor) {
            return redirect()->route('distributor.index');
        }

        // Get all factory products with distance calculation
        $marketplace = FactoryProduct::with(['factory', 'product'])
            ->whereHas('factory')
            ->get()
            ->map(function ($fp) use ($distributor) {
                $distance = $this->calculateDistance(
                    $distributor->latitude ?? 0, 
                    $distributor->longitude ?? 0, 
                    $fp->factory->latitude ?? 0, 
                    $fp->factory->longitude ?? 0
                );
                return [
                    'id' => $fp->id,
                    'product_id' => $fp->product_id,
                    'product_name' => $fp->product->name ?? 'Unknown',
                    'price' => $fp->price,
                    'stock' => $fp->production_quantity,
                    'seller_id' => $fp->factory_id,
                    'seller_name' => $fp->factory->name ?? 'Unknown',
                    'seller_type' => 'factory',
                    'distance' => $distance,
                ];
            })
            ->sortBy('distance');

        return view('distributor.marketplace', compact('distributor', 'marketplace'));
    }

    // Haversine distance calculation
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return round($R * $c, 1);
    }

    // Edit order quantity (only when pending)
    public function editOrder(Request $request, Order $order)
    {
        $distributor = auth()->user()->distributor;
        
        // Verify ownership and status
        if ($order->buyer_type !== 'distributor' || $order->buyer_id !== $distributor->id) {
            return back()->with('error', 'Unauthorized action.');
        }
        
        if ($order->status !== 'pending') {
            return back()->with('error', 'Can only edit pending orders.');
        }

        $request->validate([
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:1',
        ]);

        $totalAmount = 0;
        foreach ($order->items as $item) {
            if (isset($request->quantities[$item->id])) {
                $newQty = $request->quantities[$item->id];
                $item->quantity = $newQty;
                $item->subtotal = $newQty * $item->unit_price;
                $item->save();
                $totalAmount += $item->subtotal;
            }
        }

        $order->total_amount = $totalAmount;
        $order->save();

        return back()->with('success', 'Order updated successfully.');
    }

    // Cancel order (only when pending)
    public function cancelOrder(Order $order)
    {
        $distributor = auth()->user()->distributor;
        
        // Verify ownership and status
        if ($order->buyer_type !== 'distributor' || $order->buyer_id !== $distributor->id) {
            return back()->with('error', 'Unauthorized action.');
        }
        
        if ($order->status !== 'pending') {
            return back()->with('error', 'Can only cancel pending orders.');
        }

        // Delete order items first
        $order->items()->delete();
        // Delete order
        $order->delete();

        return back()->with('success', 'Order cancelled successfully.');
    }
}
