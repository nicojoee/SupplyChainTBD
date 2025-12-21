<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Factory;
use App\Models\Distributor;
use App\Models\Courier;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::with('products.product')->paginate(3, ['*'], 'suppliers_page');
        $factories = Factory::with('products.product')->paginate(3, ['*'], 'factories_page');
        $distributors = Distributor::with('stocks.product')->paginate(3, ['*'], 'distributors_page');
        $couriers = Courier::paginate(3, ['*'], 'couriers_page');

        // Get self entity data for map highlighting
        $user = auth()->user();
        $user->load(['supplier', 'factory', 'distributor', 'courier']); // Eager load relationships
        $selfEntity = null;
        
        if ($user->role === 'supplier' && $user->supplier) {
            $selfEntity = [
                'type' => 'supplier',
                'id' => $user->supplier->id,
                'name' => $user->supplier->name,
                'email' => $user->email,
                'latitude' => (float) $user->supplier->latitude,
                'longitude' => (float) $user->supplier->longitude,
            ];
        } elseif ($user->role === 'factory' && $user->factory) {
            $selfEntity = [
                'type' => 'factory',
                'id' => $user->factory->id,
                'name' => $user->factory->name,
                'email' => $user->email,
                'latitude' => (float) $user->factory->latitude,
                'longitude' => (float) $user->factory->longitude,
            ];
        } elseif ($user->role === 'distributor' && $user->distributor) {
            $selfEntity = [
                'type' => 'distributor',
                'id' => $user->distributor->id,
                'name' => $user->distributor->name,
                'email' => $user->email,
                'latitude' => (float) $user->distributor->latitude,
                'longitude' => (float) $user->distributor->longitude,
            ];
        } elseif ($user->role === 'courier' && $user->courier) {
            $selfEntity = [
                'type' => 'courier',
                'id' => $user->courier->id,
                'name' => $user->courier->name,
                'email' => $user->email,
                'latitude' => (float) $user->courier->current_latitude,
                'longitude' => (float) $user->courier->current_longitude,
            ];
        }

        // Get user's products (for selling tab)
        $myProducts = collect();
        if ($user->role === 'supplier' && $user->supplier) {
            $myProducts = $user->supplier->products()->with('product')->get();
        } elseif ($user->role === 'factory' && $user->factory) {
            $myProducts = $user->factory->products()->with('product')->get();
        } elseif ($user->role === 'distributor' && $user->distributor) {
            $myProducts = $user->distributor->stocks()->with('product')->get();
        }

        // Get user's orders (for buying tab)
        $myOrders = collect();
        if ($user->role === 'factory' && $user->factory) {
            $myOrders = \App\Models\Order::where('buyer_type', 'factory')
                ->where('buyer_id', $user->factory->id)
                ->with(['items.product', 'sellerSupplier'])
                ->latest()
                ->take(10)
                ->get();
        } elseif ($user->role === 'distributor' && $user->distributor) {
            $myOrders = \App\Models\Order::where('buyer_type', 'distributor')
                ->where('buyer_id', $user->distributor->id)
                ->with(['items.product', 'sellerFactory'])
                ->latest()
                ->take(10)
                ->get();
        }

        // Get marketplace products (products user can buy)
        $marketplace = collect();
        $userLat = $selfEntity['latitude'] ?? 0;
        $userLng = $selfEntity['longitude'] ?? 0;
        
        if ($user->role === 'factory' && $user->factory) {
            // Factory can buy from Suppliers
            $marketplace = \App\Models\SupplierProduct::with(['supplier', 'product'])
                ->whereHas('supplier')
                ->get()
                ->map(function ($sp) use ($userLat, $userLng) {
                    $distance = $this->calculateDistance($userLat, $userLng, $sp->supplier->latitude ?? 0, $sp->supplier->longitude ?? 0);
                    return [
                        'id' => $sp->id,
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
        } elseif ($user->role === 'distributor' && $user->distributor) {
            // Distributor can buy from Factories
            $marketplace = \App\Models\FactoryProduct::with(['factory', 'product'])
                ->whereHas('factory')
                ->get()
                ->map(function ($fp) use ($userLat, $userLng) {
                    $distance = $this->calculateDistance($userLat, $userLng, $fp->factory->latitude ?? 0, $fp->factory->longitude ?? 0);
                    return [
                        'id' => $fp->id,
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
        }

        return view('dashboard', compact('suppliers', 'factories', 'distributors', 'couriers', 'selfEntity', 'myProducts', 'myOrders', 'marketplace'));
    }

    // Haversine distance calculation (returns km)
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371; // Earth's radius in km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return round($R * $c, 1);
    }

    public function mapData()
    {
        $features = [];

        $suppliers = Supplier::with('products.product')->get();
        foreach ($suppliers as $supplier) {
            $products = $supplier->products->map(function ($sp) {
                return [
                    'name' => $sp->product->name ?? 'Unknown',
                    'price' => number_format($sp->price, 2),
                    'stock' => $sp->stock_quantity,
                ];
            });

            $features[] = [
                'type' => 'Feature',
                'properties' => [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'type' => 'supplier',
                    'description' => $supplier->description,
                    'address' => $supplier->address,
                    'phone' => $supplier->phone,
                    'products' => $products,
                ],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float)$supplier->longitude, (float)$supplier->latitude],
                ],
            ];
        }

        $factories = Factory::with('products.product')->get();
        foreach ($factories as $factory) {
            $products = $factory->products->map(function ($fp) {
                return [
                    'name' => $fp->product->name ?? 'Unknown',
                    'price' => number_format($fp->price, 2),
                    'quantity' => $fp->production_quantity,
                ];
            });

            $features[] = [
                'type' => 'Feature',
                'properties' => [
                    'id' => $factory->id,
                    'name' => $factory->name,
                    'type' => 'factory',
                    'description' => $factory->description,
                    'address' => $factory->address,
                    'phone' => $factory->phone,
                    'capacity' => $factory->production_capacity,
                    'products' => $products,
                ],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float)$factory->longitude, (float)$factory->latitude],
                ],
            ];
        }

        $distributors = Distributor::with('stocks.product')->get();
        foreach ($distributors as $distributor) {
            $stocks = $distributor->stocks->map(function ($ds) {
                return [
                    'name' => $ds->product->name ?? 'Unknown',
                    'quantity' => $ds->quantity,
                    'min_level' => $ds->min_stock_level,
                ];
            });

            $features[] = [
                'type' => 'Feature',
                'properties' => [
                    'id' => $distributor->id,
                    'name' => $distributor->name,
                    'type' => 'distributor',
                    'description' => $distributor->description,
                    'address' => $distributor->address,
                    'phone' => $distributor->phone,
                    'capacity' => $distributor->warehouse_capacity,
                    'stocks' => $stocks,
                ],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float)$distributor->longitude, (float)$distributor->latitude],
                ],
            ];
        }

        // Show couriers - only those with saved position
        // Couriers without position (never tracked) won't appear on map
        $couriers = Courier::all();
        foreach ($couriers as $courier) {
            // Skip couriers without position
            if ($courier->current_latitude === null || $courier->current_longitude === null) {
                continue;
            }
            
            $lat = (float) $courier->current_latitude;
            $lng = (float) $courier->current_longitude;
            $isGpsActive = (bool) $courier->is_gps_active;
            
            // Calculate time since last update
            $lastSeen = null;
            if ($courier->location_updated_at) {
                $lastSeen = $courier->location_updated_at->diffForHumans();
            }
            
            $features[] = [
                'type' => 'Feature',
                'properties' => [
                    'id' => $courier->id,
                    'name' => $courier->name,
                    'type' => 'courier',
                    'vehicle' => $courier->vehicle_type,
                    'license_plate' => $courier->license_plate,
                    'phone' => $courier->phone,
                    'status' => $courier->status,
                    'is_gps_active' => $isGpsActive,
                    'last_seen' => $lastSeen,
                ],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$lng, $lat],
                ],
            ];
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    // AJAX Pagination Endpoints
    public function suppliersAjax(Request $request)
    {
        $page = $request->get('page', 1);
        $suppliers = Supplier::with('products.product')->paginate(3, ['*'], 'page', $page);
        
        return response()->json([
            'html' => view('partials.suppliers-list', compact('suppliers'))->render(),
            'current_page' => $suppliers->currentPage(),
            'last_page' => $suppliers->lastPage(),
            'total' => $suppliers->total(),
        ]);
    }

    public function factoriesAjax(Request $request)
    {
        $page = $request->get('page', 1);
        $factories = Factory::with('products.product')->paginate(3, ['*'], 'page', $page);
        
        return response()->json([
            'html' => view('partials.factories-list', compact('factories'))->render(),
            'current_page' => $factories->currentPage(),
            'last_page' => $factories->lastPage(),
            'total' => $factories->total(),
        ]);
    }

    public function distributorsAjax(Request $request)
    {
        $page = $request->get('page', 1);
        $distributors = Distributor::with('stocks.product')->paginate(3, ['*'], 'page', $page);
        
        return response()->json([
            'html' => view('partials.distributors-list', compact('distributors'))->render(),
            'current_page' => $distributors->currentPage(),
            'last_page' => $distributors->lastPage(),
            'total' => $distributors->total(),
        ]);
    }

    public function couriersAjax(Request $request)
    {
        $page = $request->get('page', 1);
        $couriers = Courier::paginate(3, ['*'], 'page', $page);
        
        return response()->json([
            'html' => view('partials.couriers-list', compact('couriers'))->render(),
            'current_page' => $couriers->currentPage(),
            'last_page' => $couriers->lastPage(),
            'total' => $couriers->total(),
        ]);
    }

    // Search entities by name for map auto-locate
    public function searchEntities(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = [];

        // Search Suppliers
        $suppliers = Supplier::where('name', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'address', 'latitude', 'longitude')
            ->limit(5)
            ->get();

        foreach ($suppliers as $supplier) {
            $results[] = [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'type' => 'supplier',
                'icon' => '📦',
                'address' => $supplier->address,
                'latitude' => (float) $supplier->latitude,
                'longitude' => (float) $supplier->longitude,
            ];
        }

        // Search Factories
        $factories = Factory::where('name', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'address', 'latitude', 'longitude')
            ->limit(5)
            ->get();

        foreach ($factories as $factory) {
            $results[] = [
                'id' => $factory->id,
                'name' => $factory->name,
                'type' => 'factory',
                'icon' => '🏭',
                'address' => $factory->address,
                'latitude' => (float) $factory->latitude,
                'longitude' => (float) $factory->longitude,
            ];
        }

        // Search Distributors
        $distributors = Distributor::where('name', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'address', 'latitude', 'longitude')
            ->limit(5)
            ->get();

        foreach ($distributors as $distributor) {
            $results[] = [
                'id' => $distributor->id,
                'name' => $distributor->name,
                'type' => 'distributor',
                'icon' => '🚚',
                'address' => $distributor->address,
                'latitude' => (float) $distributor->latitude,
                'longitude' => (float) $distributor->longitude,
            ];
        }

        // Search Couriers
        $couriers = Courier::where('name', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'phone', 'current_latitude', 'current_longitude', 'status')
            ->limit(5)
            ->get();

        foreach ($couriers as $courier) {
            $results[] = [
                'id' => $courier->id,
                'name' => $courier->name,
                'type' => 'courier',
                'icon' => '🛵',
                'address' => 'Status: ' . ucfirst($courier->status),
                'latitude' => (float) ($courier->current_latitude ?? -6.2088),
                'longitude' => (float) ($courier->current_longitude ?? 106.8456),
            ];
        }

        return response()->json($results);
    }
}
