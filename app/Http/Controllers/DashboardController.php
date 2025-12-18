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

        return view('dashboard', compact('suppliers', 'factories', 'distributors', 'couriers', 'selfEntity'));
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

        // Show all couriers, use default location if not set
        $couriers = Courier::all();
        foreach ($couriers as $courier) {
            $lat = $courier->current_latitude ?? -6.2088;  // Default: Jakarta
            $lng = $courier->current_longitude ?? 106.8456;
            
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
                    'has_location' => $courier->current_latitude !== null,
                ],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float)$lng, (float)$lat],
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
}
