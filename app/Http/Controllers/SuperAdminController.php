<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Supplier;
use App\Models\Factory;
use App\Models\Distributor;
use App\Models\Courier;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => User::count(),
            'suppliers' => Supplier::count(),
            'factories' => Factory::count(),
            'distributors' => Distributor::count(),
            'couriers' => Courier::count(),
            'products' => Product::count(),
            'orders' => Order::count(),
            'pendingOrders' => Order::where('status', 'pending')->count(),
        ];

        $recentOrders = Order::with('courier')->latest()->paginate(10, ['*'], 'orders_page');
        $users = User::latest()->paginate(10);

        return view('superadmin.index', compact('stats', 'recentOrders', 'users'));
    }

    public function users()
    {
        $users = User::paginate(20);
        return view('superadmin.users', compact('users'));
    }

    public function updateUserRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:superadmin,supplier,factory,distributor,courier',
        ]);

        $user->update(['role' => $request->role]);

        return back()->with('success', 'User role updated successfully.');
    }

    // Add Supplier Form
    public function addSupplierForm()
    {
        return view('superadmin.add-supplier');
    }

    public function storeSupplier(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'supplier',
        ]);

        Supplier::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'phone' => $request->phone,
        ]);

        return redirect()->route('superadmin.suppliers')->with('success', 'Supplier created with location! They can login with Google.');
    }

    // Add Factory Form
    public function addFactoryForm()
    {
        return view('superadmin.add-factory');
    }

    public function storeFactory(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'phone' => 'nullable|string|max:20',
            'production_capacity' => 'required|integer|min:0',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'factory',
        ]);

        Factory::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'phone' => $request->phone,
            'production_capacity' => $request->production_capacity,
        ]);

        return redirect()->route('superadmin.factories')->with('success', 'Factory created with location! They can login with Google.');
    }

    // Add Distributor Form
    public function addDistributorForm()
    {
        return view('superadmin.add-distributor');
    }

    public function storeDistributor(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'phone' => 'nullable|string|max:20',
            'warehouse_capacity' => 'required|integer|min:0',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'distributor',
        ]);

        Distributor::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'phone' => $request->phone,
            'warehouse_capacity' => $request->warehouse_capacity,
        ]);

        return redirect()->route('superadmin.distributors')->with('success', 'Distributor created with location! They can login with Google.');
    }

    // Courier Management
    public function couriers()
    {
        $couriers = Courier::with('user')->paginate(10);
        return view('superadmin.couriers', compact('couriers'));
    }

    public function addCourierForm()
    {
        return view('superadmin.add-courier');
    }

    public function storeCourier(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
        ]);

        // Extract name from email safely
        $emailParts = explode('@', $request->email);
        $name = $emailParts[0] ?? 'Courier';

        $user = User::create([
            'name' => $name,
            'email' => $request->email,
            'role' => 'courier',
        ]);

        // Also create Courier profile so they appear in management page and map
        Courier::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'status' => 'idle',
        ]);

        return redirect()->route('superadmin.couriers')->with('success', 'Courier account created! They can now login with Google.');
    }

    public function deleteCourier(Courier $courier)
    {
        try {
            // Check if courier has active orders
            $hasActiveOrders = \App\Models\Order::where('courier_id', $courier->id)
                ->whereIn('status', ['processing', 'shipped'])
                ->exists();
            
            if ($hasActiveOrders) {
                return redirect()->route('superadmin.couriers')
                    ->with('error', 'Cannot delete courier with active deliveries. Please complete all deliveries first.');
            }

            $user = $courier->user;
            
            // Unassign courier from completed orders (keep history)
            \App\Models\Order::where('courier_id', $courier->id)
                ->update(['courier_id' => null]);
            
            $courier->delete();
            
            // Delete user account if no other entities linked
            if ($user && !$user->supplier && !$user->factory && !$user->distributor) {
                $user->delete();
            }

            return redirect()->route('superadmin.couriers')->with('success', 'Courier deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('superadmin.couriers')
                ->with('error', 'Error deleting courier: ' . $e->getMessage());
        }
    }

    // Supplier Management
    public function suppliers()
    {
        $suppliers = Supplier::with(['user', 'products.product'])->paginate(10);
        return view('superadmin.suppliers', compact('suppliers'));
    }

    public function deleteSupplier(Supplier $supplier)
    {
        try {
            // Check if supplier has orders
            $hasOrders = \App\Models\Order::where('seller_type', 'supplier')
                ->where('seller_id', $supplier->id)
                ->exists();
            
            if ($hasOrders) {
                return redirect()->route('superadmin.suppliers')
                    ->with('error', 'Cannot delete supplier with existing orders. Please cancel or complete all orders first.');
            }

            $user = $supplier->user;
            
            // Delete related data first
            $supplier->products()->delete();
            $supplier->delete();
            
            // Delete user account if no other entities linked
            if ($user && !$user->factory && !$user->distributor && !$user->courier) {
                $user->delete();
            }

            return redirect()->route('superadmin.suppliers')->with('success', 'Supplier deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('superadmin.suppliers')
                ->with('error', 'Error deleting supplier: ' . $e->getMessage());
        }
    }

    // Factory Management
    public function factories()
    {
        $factories = Factory::with(['user', 'products.product'])->paginate(10);
        return view('superadmin.factories', compact('factories'));
    }

    public function deleteFactory(Factory $factory)
    {
        try {
            // Check if factory has orders (as buyer or seller)
            $hasOrders = \App\Models\Order::where(function($query) use ($factory) {
                $query->where('buyer_type', 'factory')->where('buyer_id', $factory->id)
                      ->orWhere(function($q) use ($factory) {
                          $q->where('seller_type', 'factory')->where('seller_id', $factory->id);
                      });
            })->exists();
            
            if ($hasOrders) {
                return redirect()->route('superadmin.factories')
                    ->with('error', 'Cannot delete factory with existing orders. Please cancel or complete all orders first.');
            }

            $user = $factory->user;
            
            // Delete related data first
            $factory->products()->delete();
            $factory->delete();
            
            // Delete user account if no other entities linked
            if ($user && !$user->supplier && !$user->distributor && !$user->courier) {
                $user->delete();
            }

            return redirect()->route('superadmin.factories')->with('success', 'Factory deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('superadmin.factories')
                ->with('error', 'Error deleting factory: ' . $e->getMessage());
        }
    }

    // Distributor Management
    public function distributors()
    {
        $distributors = Distributor::with(['user', 'stocks.product'])->paginate(10);
        return view('superadmin.distributors', compact('distributors'));
    }

    public function deleteDistributor(Distributor $distributor)
    {
        try {
            // Check if distributor has orders
            $hasOrders = \App\Models\Order::where('buyer_type', 'distributor')
                ->where('buyer_id', $distributor->id)
                ->exists();
            
            if ($hasOrders) {
                return redirect()->route('superadmin.distributors')
                    ->with('error', 'Cannot delete distributor with existing orders. Please cancel or complete all orders first.');
            }

            $user = $distributor->user;
            
            // Delete related data first
            $distributor->stocks()->delete();
            $distributor->delete();
            
            // Delete user account if no other entities linked
            if ($user && !$user->supplier && !$user->factory && !$user->courier) {
                $user->delete();
            }

            return redirect()->route('superadmin.distributors')->with('success', 'Distributor deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('superadmin.distributors')
                ->with('error', 'Error deleting distributor: ' . $e->getMessage());
        }
    }

    // AJAX Position Update Methods
    public function updateSupplierPosition(Request $request, Supplier $supplier)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $supplier->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json(['success' => true, 'message' => 'Supplier position updated']);
    }

    public function updateFactoryPosition(Request $request, Factory $factory)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $factory->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json(['success' => true, 'message' => 'Factory position updated']);
    }

    public function updateDistributorPosition(Request $request, Distributor $distributor)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $distributor->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json(['success' => true, 'message' => 'Distributor position updated']);
    }

    // AJAX Delete Methods
    public function deleteSupplierAjax(Supplier $supplier)
    {
        try {
            // Check if supplier has orders
            $hasOrders = \App\Models\Order::where('seller_type', 'supplier')
                ->where('seller_id', $supplier->id)
                ->exists();
            
            if ($hasOrders) {
                if (request()->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Cannot delete supplier with existing orders'], 400);
                }
                return redirect()->route('superadmin.suppliers')
                    ->with('error', 'Cannot delete supplier with existing orders. Please cancel or complete all orders first.');
            }

            $user = $supplier->user;
            $supplier->products()->delete();
            $supplier->delete();
            
            if ($user && !$user->factory && !$user->distributor && !$user->courier) {
                $user->delete();
            }

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Supplier deleted']);
            }
            return redirect()->route('superadmin.suppliers')->with('success', 'Supplier deleted successfully!');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->route('superadmin.suppliers')->with('error', 'Error deleting supplier: ' . $e->getMessage());
        }
    }

    public function deleteFactoryAjax(Factory $factory)
    {
        try {
            $hasOrders = \App\Models\Order::where(function($query) use ($factory) {
                $query->where('buyer_type', 'factory')->where('buyer_id', $factory->id)
                      ->orWhere(function($q) use ($factory) {
                          $q->where('seller_type', 'factory')->where('seller_id', $factory->id);
                      });
            })->exists();
            
            if ($hasOrders) {
                if (request()->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Cannot delete factory with existing orders'], 400);
                }
                return redirect()->route('superadmin.factories')
                    ->with('error', 'Cannot delete factory with existing orders.');
            }

            $user = $factory->user;
            $factory->products()->delete();
            $factory->delete();
            
            if ($user && !$user->supplier && !$user->distributor && !$user->courier) {
                $user->delete();
            }

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Factory deleted']);
            }
            return redirect()->route('superadmin.factories')->with('success', 'Factory deleted successfully!');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->route('superadmin.factories')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function deleteDistributorAjax(Distributor $distributor)
    {
        try {
            $hasOrders = \App\Models\Order::where('buyer_type', 'distributor')
                ->where('buyer_id', $distributor->id)
                ->exists();
            
            if ($hasOrders) {
                if (request()->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Cannot delete distributor with existing orders'], 400);
                }
                return redirect()->route('superadmin.distributors')
                    ->with('error', 'Cannot delete distributor with existing orders.');
            }

            $user = $distributor->user;
            $distributor->stocks()->delete();
            $distributor->delete();
            
            if ($user && !$user->supplier && !$user->factory && !$user->courier) {
                $user->delete();
            }

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Distributor deleted']);
            }
            return redirect()->route('superadmin.distributors')->with('success', 'Distributor deleted successfully!');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->route('superadmin.distributors')->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
