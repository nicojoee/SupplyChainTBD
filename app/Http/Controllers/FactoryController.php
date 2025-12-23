<?php

namespace App\Http\Controllers;

use App\Models\Factory;
use App\Models\FactoryProduct;
use App\Models\SupplierProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Courier;
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
            'latitude' => $request->latitude ?? 0,
            'longitude' => $request->longitude ?? 0,
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

    // Update product price/quantity via AJAX
    public function updateProduct(Request $request)
    {
        $factory = auth()->user()->factory;
        
        if (!$factory) {
            return response()->json(['success' => false, 'message' => 'Factory not found']);
        }

        $factoryProduct = FactoryProduct::where('id', $request->product_id)
            ->where('factory_id', $factory->id)
            ->first();

        if (!$factoryProduct) {
            return response()->json(['success' => false, 'message' => 'Product not found']);
        }

        $factoryProduct->price = $request->price;
        $factoryProduct->production_quantity = $request->production_quantity;
        $factoryProduct->save();

        return response()->json(['success' => true, 'message' => 'Product updated successfully']);
    }

    // Delete product via AJAX
    public function deleteProduct(Request $request)
    {
        $factory = auth()->user()->factory;
        
        if (!$factory) {
            return response()->json(['success' => false, 'message' => 'Factory not found']);
        }

        $factoryProduct = FactoryProduct::where('id', $request->product_id)
            ->where('factory_id', $factory->id)
            ->first();

        if (!$factoryProduct) {
            return response()->json(['success' => false, 'message' => 'Product not found']);
        }

        $factoryProduct->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
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

    // AJAX purchase from map popup
    public function buyFromSupplierAjax(Request $request)
    {
        $request->validate([
            'seller_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $factory = auth()->user()->factory;
        
        if (!$factory) {
            return response()->json(['success' => false, 'message' => 'Factory profile not found'], 400);
        }

        $totalAmount = 0;
        foreach ($request->items as $item) {
            $totalAmount += $item['quantity'] * $item['price'];
        }

        // Create order with status 'pending' - waiting for supplier confirmation
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'buyer_type' => 'factory',
            'buyer_id' => $factory->id,
            'seller_type' => 'supplier',
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

    // Incoming orders from Distributors (Factory as seller)
    public function incomingOrders()
    {
        $factory = auth()->user()->factory;
        
        if (!$factory) {
            return redirect()->route('factory.index');
        }

        $incomingOrders = Order::where('seller_type', 'factory')
            ->where('seller_id', $factory->id)
            ->with(['items.product', 'buyerDistributor'])
            ->latest()
            ->get();

        return view('factory.orders', compact('factory', 'incomingOrders'));
    }

    // Update order status
    public function updateOrderStatus(Request $request, Order $order)
    {
        $factory = auth()->user()->factory;
        
        // Verify this order belongs to this factory
        if ($order->seller_type !== 'factory' || $order->seller_id !== $factory->id) {
            return back()->with('error', 'Unauthorized');
        }

        $request->validate([
            'status' => 'required|in:confirmed,processing,pickup,in_delivery,delivered,cancelled'
        ]);

        $order->update(['status' => $request->status]);

        // If status is 'confirmed', reduce stock (for outgoing orders to distributor)
        if ($request->status === 'confirmed') {
            foreach ($order->items as $item) {
                $factoryProduct = FactoryProduct::where('factory_id', $factory->id)
                    ->where('product_id', $item->product_id)
                    ->first();
                    
                if ($factoryProduct) {
                    $factoryProduct->decrement('production_quantity', $item->quantity);
                }
            }
        }

        return back()->with('success', 'Order status updated to ' . $request->status);
    }

    // Marketplace - view supplier products
    public function marketplace()
    {
        $factory = auth()->user()->factory;
        
        if (!$factory) {
            return redirect()->route('factory.index');
        }

        // Get all supplier products with distance calculation
        $marketplace = SupplierProduct::with(['supplier', 'product'])
            ->whereHas('supplier')
            ->get()
            ->map(function ($sp) use ($factory) {
                $distance = $this->calculateDistance(
                    $factory->latitude ?? 0, 
                    $factory->longitude ?? 0, 
                    $sp->supplier->latitude ?? 0, 
                    $sp->supplier->longitude ?? 0
                );
                return [
                    'id' => $sp->id,
                    'product_id' => $sp->product_id,
                    'product_name' => $sp->product->name ?? 'Unknown',
                    'price' => $sp->price,
                    'stock' => $sp->stock_quantity,
                    'seller_id' => $sp->supplier_id,
                    'seller_name' => $sp->supplier->name ?? 'Unknown',
                    'seller_type' => 'supplier',
                    'distance' => $distance,
                ];
            })
            ->sortBy('distance');

        return view('factory.marketplace', compact('factory', 'marketplace'));
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

    // My Orders (Factory as buyer from Supplier)
    public function myOrders()
    {
        $factory = auth()->user()->factory;
        
        if (!$factory) {
            return redirect()->route('factory.index');
        }

        $orders = Order::where('buyer_type', 'factory')
            ->where('buyer_id', $factory->id)
            ->with(['items.product', 'sellerSupplier'])
            ->latest()
            ->get();

        return view('factory.my-orders', compact('factory', 'orders'));
    }

    // Edit order quantity (only when pending)
    public function editOrder(Request $request, Order $order)
    {
        $factory = auth()->user()->factory;
        
        // Verify ownership and status
        if ($order->buyer_type !== 'factory' || $order->buyer_id !== $factory->id) {
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
        $factory = auth()->user()->factory;
        
        // Verify ownership and status
        if ($order->buyer_type !== 'factory' || $order->buyer_id !== $factory->id) {
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

    // Request courier for an order (marks order as ready for pickup)
    public function requestCourier(Order $order)
    {
        $user = auth()->user();
        $factory = $user->factory;

        if (!$factory) {
            return back()->with('error', 'Factory profile not found.');
        }

        // Check if order belongs to this factory (as seller)
        if ($order->seller_type !== 'factory' || $order->seller_id !== $factory->id) {
            return back()->with('error', 'You cannot request courier for this order.');
        }

        // Check if order already has a courier
        if ($order->courier_id) {
            return back()->with('error', 'A courier is already assigned to this order.');
        }

        // Check if factory location is set
        if (!$factory->latitude || !$factory->longitude) {
            return back()->with('error', 'Please set your factory location first.');
        }

        // Update order status to 'pickup' - ready for courier to accept
        $order->update([
            'status' => 'pickup',
        ]);

        return back()->with('success', 'Delivery request sent! Order is now available for couriers to accept.');
    }
}
