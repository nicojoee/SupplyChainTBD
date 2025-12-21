<?php

namespace App\Http\Controllers;

use App\Models\Courier;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\Factory;
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

        // Get 3 nearest locations with pending orders
        $nearbyLocations = [];
        $courierLat = $courier->current_latitude;
        $courierLng = $courier->current_longitude;

        if ($courierLat && $courierLng) {
            // Get suppliers with pending pickup orders
            $suppliers = Supplier::whereHas('products')->get();
            foreach ($suppliers as $supplier) {
                if ($supplier->latitude && $supplier->longitude) {
                    // Count pending orders where this supplier is the seller
                    $pendingCount = Order::whereNull('courier_id')
                        ->where('seller_type', 'supplier')
                        ->where('seller_id', $supplier->id)
                        ->whereIn('status', ['confirmed', 'processing', 'pickup'])
                        ->count();

                    $distance = $this->calculateDistance($courierLat, $courierLng, $supplier->latitude, $supplier->longitude);
                    $nearbyLocations[] = [
                        'type' => 'Supplier',
                        'name' => $supplier->name,
                        'address' => $supplier->address ?? 'No address',
                        'latitude' => $supplier->latitude,
                        'longitude' => $supplier->longitude,
                        'distance' => $distance,
                        'pending_orders' => $pendingCount,
                        'phone' => $supplier->phone ?? '-',
                    ];
                }
            }

            // Get factories with pending pickup orders
            $factories = Factory::whereHas('products')->get();
            foreach ($factories as $factory) {
                if ($factory->latitude && $factory->longitude) {
                    // Count pending orders where this factory is the seller
                    $pendingCount = Order::whereNull('courier_id')
                        ->where('seller_type', 'factory')
                        ->where('seller_id', $factory->id)
                        ->whereIn('status', ['confirmed', 'processing', 'pickup'])
                        ->count();

                    $distance = $this->calculateDistance($courierLat, $courierLng, $factory->latitude, $factory->longitude);
                    $nearbyLocations[] = [
                        'type' => 'Factory',
                        'name' => $factory->name,
                        'address' => $factory->address ?? 'No address',
                        'latitude' => $factory->latitude,
                        'longitude' => $factory->longitude,
                        'distance' => $distance,
                        'pending_orders' => $pendingCount,
                        'phone' => $factory->phone ?? '-',
                    ];
                }
            }

            // Sort by distance and take top 3
            usort($nearbyLocations, fn($a, $b) => $a['distance'] <=> $b['distance']);
            $nearbyLocations = array_slice($nearbyLocations, 0, 3);
        }
        
        return view('courier.index', compact('courier', 'assignedOrders', 'completedOrders', 'nearbyLocations'));
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
            'is_gps_active' => 'nullable|boolean',
        ]);

        $courier = auth()->user()->courier;
        $isGpsActive = $request->boolean('is_gps_active', false);
        
        $updateData = [
            'current_latitude' => $request->latitude,
            'current_longitude' => $request->longitude,
            'is_gps_active' => $isGpsActive,
        ];
        
        // Only update location_updated_at when GPS is active
        if ($isGpsActive) {
            $updateData['location_updated_at'] = now();
        }
        
        $courier->update($updateData);

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

    // Available deliveries for courier to accept (sorted by distance)
    public function availableDeliveries(Request $request)
    {
        $user = auth()->user();
        $courier = $user->courier;

        if (!$courier) {
            return redirect()->route('courier.index');
        }

        // Get orders ready for pickup (status 'pickup' or 'confirmed') without courier assigned
        $orders = Order::with(['items.product', 'sellerSupplier', 'sellerFactory'])
            ->whereNull('courier_id')
            ->whereIn('status', ['pickup', 'confirmed'])
            ->get();

        // Calculate distance from courier's current location
        $courierLat = $courier->current_latitude;
        $courierLng = $courier->current_longitude;

        if ($courierLat && $courierLng) {
            $orders = $orders->map(function ($order) use ($courierLat, $courierLng) {
                // Get seller location based on seller_type
                $sellerLat = null;
                $sellerLng = null;

                if ($order->seller_type === 'supplier' && $order->sellerSupplier) {
                    $sellerLat = $order->sellerSupplier->latitude;
                    $sellerLng = $order->sellerSupplier->longitude;
                } elseif ($order->seller_type === 'factory' && $order->sellerFactory) {
                    $sellerLat = $order->sellerFactory->latitude;
                    $sellerLng = $order->sellerFactory->longitude;
                }

                // Calculate distance using Haversine formula
                if ($sellerLat && $sellerLng) {
                    $order->distance = $this->calculateDistance($courierLat, $courierLng, $sellerLat, $sellerLng);
                } else {
                    $order->distance = 999999; // Unknown location, put at end
                }

                return $order;
            })->sortBy('distance');
        }

        // Paginate manually
        $page = $request->get('page', 1);
        $perPage = 10;
        $total = $orders->count();
        $orders = $orders->slice(($page - 1) * $perPage, $perPage)->values();

        return view('courier.available-deliveries', [
            'orders' => $orders,
            'courier' => $courier,
            'currentPage' => $page,
            'totalPages' => ceil($total / $perPage),
            'total' => $total,
        ]);
    }

    // Accept a delivery
    public function acceptDelivery(Order $order)
    {
        $user = auth()->user();
        $courier = $user->courier;

        if (!$courier) {
            return back()->with('error', 'Courier profile not found.');
        }

        // Check if order is available
        if ($order->courier_id !== null || !in_array($order->status, ['pickup', 'confirmed'])) {
            return back()->with('error', 'This delivery is no longer available.');
        }

        // Assign courier to order
        $order->update([
            'courier_id' => $courier->id,
            'status' => 'processing',
        ]);

        // Update courier status
        $courier->update(['status' => 'busy']);

        return redirect()->route('courier.index')->with('success', 'Delivery accepted successfully!');
    }

    // Calculate distance between two coordinates (Haversine formula)
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

