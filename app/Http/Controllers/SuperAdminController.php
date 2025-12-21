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

        User::create([
            'name' => explode('@', $request->email)[0],
            'email' => $request->email,
            'role' => 'courier',
        ]);

        return redirect()->route('superadmin.couriers')->with('success', 'Courier account created! They can now login with Google.');
    }

    public function deleteCourier(Courier $courier)
    {
        $user = $courier->user;
        $courier->delete();
        if ($user) {
            $user->delete();
        }

        return redirect()->route('superadmin.couriers')->with('success', 'Courier deleted successfully!');
    }

    // Supplier Management
    public function suppliers()
    {
        $suppliers = Supplier::with(['user', 'products.product'])->paginate(10);
        return view('superadmin.suppliers', compact('suppliers'));
    }

    public function deleteSupplier(Supplier $supplier)
    {
        $user = $supplier->user;
        $supplier->products()->delete();
        $supplier->delete();
        if ($user) {
            $user->delete();
        }

        return redirect()->route('superadmin.suppliers')->with('success', 'Supplier deleted successfully!');
    }

    // Factory Management
    public function factories()
    {
        $factories = Factory::with(['user', 'products.product'])->paginate(10);
        return view('superadmin.factories', compact('factories'));
    }

    public function deleteFactory(Factory $factory)
    {
        $user = $factory->user;
        $factory->products()->delete();
        $factory->delete();
        if ($user) {
            $user->delete();
        }

        return redirect()->route('superadmin.factories')->with('success', 'Factory deleted successfully!');
    }

    // Distributor Management
    public function distributors()
    {
        $distributors = Distributor::with(['user', 'stocks.product'])->paginate(10);
        return view('superadmin.distributors', compact('distributors'));
    }

    public function deleteDistributor(Distributor $distributor)
    {
        $user = $distributor->user;
        $distributor->stocks()->delete();
        $distributor->delete();
        if ($user) {
            $user->delete();
        }

        return redirect()->route('superadmin.distributors')->with('success', 'Distributor deleted successfully!');
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
        $user = $supplier->user;
        $supplier->products()->delete();
        $supplier->delete();
        if ($user) {
            $user->delete();
        }

        return response()->json(['success' => true, 'message' => 'Supplier deleted']);
    }

    public function deleteFactoryAjax(Factory $factory)
    {
        $user = $factory->user;
        $factory->products()->delete();
        $factory->delete();
        if ($user) {
            $user->delete();
        }

        return response()->json(['success' => true, 'message' => 'Factory deleted']);
    }

    public function deleteDistributorAjax(Distributor $distributor)
    {
        $user = $distributor->user;
        $distributor->stocks()->delete();
        $distributor->delete();
        if ($user) {
            $user->delete();
        }

        return response()->json(['success' => true, 'message' => 'Distributor deleted']);
    }
}
