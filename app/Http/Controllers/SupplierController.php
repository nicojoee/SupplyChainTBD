<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Courier;
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
        
        // Get incoming orders from Factories
        $incomingOrders = Order::where('seller_type', 'supplier')
            ->where('seller_id', $supplier->id)
            ->with(['items.product', 'buyerFactory'])
            ->latest()
            ->get();
        
        return view('supplier.index', compact('supplier', 'products', 'incomingOrders'));
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
            'latitude' => $request->latitude ?? 0,
            'longitude' => $request->longitude ?? 0,
            'phone' => $request->phone,
        ]);

        return redirect()->route('supplier.index')->with('success', 'Supplier profile created successfully.');
    }

    public function addProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'price' => 'required|numeric|min:0|max:999999999999', // Max ~1 trillion Rupiah
            'stock_quantity' => 'required|numeric|min:0|max:9999999999', // Max ~10 billion tons (reasonable limit)
        ]);

        // Check if product is a finished product (only Factory can sell these)
        $product = Product::find($request->product_id);
        if ($product && $product->isFinishedProduct()) {
            return back()->with('error', 'Suppliers cannot sell finished products. Only factories can sell Refined Cooking Oil and Premium Olive Oil.');
        }

        $supplier = auth()->user()->supplier;

        SupplierProduct::updateOrCreate(
            ['supplier_id' => $supplier->id, 'product_id' => $request->product_id],
            ['price' => $request->price, 'stock_quantity' => $request->stock_quantity]
        );

        return back()->with('success', 'Product added/updated successfully.');
    }

    // Update product price/stock via AJAX
    public function updateProduct(Request $request)
    {
        $supplier = auth()->user()->supplier;
        
        if (!$supplier) {
            return response()->json(['success' => false, 'message' => 'Supplier not found']);
        }

        $supplierProduct = SupplierProduct::where('id', $request->product_id)
            ->where('supplier_id', $supplier->id)
            ->first();

        if (!$supplierProduct) {
            return response()->json(['success' => false, 'message' => 'Product not found']);
        }

        $supplierProduct->price = $request->price;
        $supplierProduct->stock_quantity = $request->stock_quantity;
        $supplierProduct->save();

        return response()->json(['success' => true, 'message' => 'Product updated successfully']);
    }

    // Delete product via AJAX
    public function deleteProduct(Request $request)
    {
        $supplier = auth()->user()->supplier;
        
        if (!$supplier) {
            return response()->json(['success' => false, 'message' => 'Supplier not found']);
        }

        $supplierProduct = SupplierProduct::where('id', $request->product_id)
            ->where('supplier_id', $supplier->id)
            ->first();

        if (!$supplierProduct) {
            return response()->json(['success' => false, 'message' => 'Product not found']);
        }

        $supplierProduct->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
    }

    // Incoming orders from Factories
    public function incomingOrders()
    {
        $supplier = auth()->user()->supplier;
        
        if (!$supplier) {
            return redirect()->route('supplier.index');
        }

        $incomingOrders = Order::where('seller_type', 'supplier')
            ->where('seller_id', $supplier->id)
            ->with(['items.product', 'buyerFactory'])
            ->latest()
            ->get();

        return view('supplier.orders', compact('supplier', 'incomingOrders'));
    }

    // Update order status
    public function updateOrderStatus(Request $request, Order $order)
    {
        $supplier = auth()->user()->supplier;
        
        // Verify this order belongs to this supplier
        if ($order->seller_type !== 'supplier' || $order->seller_id !== $supplier->id) {
            return back()->with('error', 'Unauthorized');
        }

        $request->validate([
            'status' => 'required|in:confirmed,processing,pickup,in_delivery,delivered,cancelled'
        ]);

        $order->update(['status' => $request->status]);

        // If status is 'confirmed', reduce stock from supplier products
        if ($request->status === 'confirmed') {
            foreach ($order->items as $item) {
                $supplierProduct = SupplierProduct::where('supplier_id', $supplier->id)
                    ->where('product_id', $item->product_id)
                    ->first();
                    
                if ($supplierProduct) {
                    $supplierProduct->decrement('stock_quantity', $item->quantity);
                }
            }
        }

        return back()->with('success', 'Order status updated to ' . $request->status);
    }

    // Request courier for an order (marks order as ready for pickup)
    public function requestCourier(Order $order)
    {
        $user = auth()->user();
        $supplier = $user->supplier;

        if (!$supplier) {
            return back()->with('error', 'Supplier profile not found.');
        }

        // Check if order belongs to this supplier (seller)
        if ($order->seller_type !== 'supplier' || $order->seller_id !== $supplier->id) {
            return back()->with('error', 'You cannot request courier for this order.');
        }

        // Check if order already has a courier
        if ($order->courier_id) {
            return back()->with('error', 'A courier is already assigned to this order.');
        }

        // Check if supplier location is set
        if (!$supplier->latitude || !$supplier->longitude) {
            return back()->with('error', 'Please set your supplier location first.');
        }

        // Update order status to 'pickup' - ready for courier to accept
        $order->update([
            'status' => 'pickup',
        ]);

        return back()->with('success', 'Delivery request sent! Order is now available for couriers to accept.');
    }

    // Haversine formula to calculate distance
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
