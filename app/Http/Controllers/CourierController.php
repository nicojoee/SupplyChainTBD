<?php

namespace App\Http\Controllers;

use App\Models\Courier;
use App\Models\Order;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Superadmin sees all couriers and all deliveries
        if ($user->role === 'superadmin') {
            $couriers = Courier::with('user')->get();
            $allOrders = Order::with(['items.product', 'courier'])
                ->whereNotNull('courier_id')
                ->latest()
                ->paginate(10);
            $pendingOrders = Order::with(['items.product'])
                ->whereNull('courier_id')
                ->where('status', 'confirmed')
                ->get();
            $inTransitCount = Order::whereIn('status', ['processing', 'shipped'])->count();
            
            return view('courier.admin-index', compact('couriers', 'allOrders', 'pendingOrders', 'inTransitCount'));
        }

        $courier = $user->courier;
        
        if (!$courier) {
            return view('courier.setup');
        }

        $assignedOrders = Order::where('courier_id', $courier->id)
            ->whereIn('status', ['confirmed', 'processing', 'shipped'])
            ->with('items.product')
            ->get();
        
        $completedOrders = Order::where('courier_id', $courier->id)
            ->where('status', 'delivered')
            ->with('items.product')
            ->latest()
            ->take(10)
            ->get();
        
        return view('courier.index', compact('courier', 'assignedOrders', 'completedOrders'));
    }

    public function setup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'vehicle_type' => 'nullable|string|max:100',
            'license_plate' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
        ]);

        Courier::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'vehicle_type' => $request->vehicle_type,
            'license_plate' => $request->license_plate,
            'phone' => $request->phone,
            'status' => 'idle',
        ]);

        return redirect()->route('courier.index')->with('success', 'Courier profile created successfully.');
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $courier = auth()->user()->courier;
        $courier->update([
            'current_latitude' => $request->latitude,
            'current_longitude' => $request->longitude,
        ]);

        return response()->json(['success' => true]);
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:processing,shipped,delivered',
        ]);

        $user = auth()->user();
        $courier = null;
        
        // Allow superadmin to update any order
        if ($user->role !== 'superadmin') {
            $courier = $user->courier;
            if ($order->courier_id !== $courier->id) {
                abort(403);
            }
        } else {
            // If superadmin, get the assigned courier to update their status if needed
            $courier = $order->courier;
        }

        $order->update(['status' => $request->status]);

        // If delivered, check if courier has other active orders
        if ($request->status === 'delivered' && $courier) {
            $hasActiveOrders = Order::where('courier_id', $courier->id)
                ->whereIn('status', ['processing', 'shipped'])
                ->exists();

            if (!$hasActiveOrders) {
                $courier->update(['status' => 'idle']);
            }
        }

        return back()->with('success', 'Order status updated successfully.');
    }

    public function assignCourier(Request $request, Order $order)
    {
        // Only superadmin can assign couriers
        if (auth()->user()->role !== 'superadmin') {
            abort(403);
        }

        $request->validate([
            'courier_id' => 'required|exists:couriers,id',
        ]);

        $order->update([
            'courier_id' => $request->courier_id,
            'status' => 'processing',
        ]);

        // Update courier status to busy
        Courier::find($request->courier_id)->update(['status' => 'busy']);

        return back()->with('success', 'Courier assigned successfully!');
    }
}

