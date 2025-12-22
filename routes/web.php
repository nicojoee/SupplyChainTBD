<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\FactoryController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\ChatController;
use App\Http\Middleware\RoleMiddleware;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Debug route - TEMPORARY for Vercel troubleshooting
Route::get('/debug-db', function () {
    return response()->json([
        'couriers_count' => \App\Models\Courier::count(),
        'users_courier_role' => \App\Models\User::where('role', 'courier')->count(),
        'suppliers_count' => \App\Models\Supplier::count(),
        'factories_count' => \App\Models\Factory::count(),
        'distributors_count' => \App\Models\Distributor::count(),
        'sample_couriers' => \App\Models\Courier::take(3)->get(['id', 'name', 'user_id']),
        'sample_users_courier' => \App\Models\User::where('role', 'courier')->take(3)->get(['id', 'name', 'email', 'role']),
        'db_connection' => config('database.default'),
        'db_host' => config('database.connections.mysql.host'),
    ]);
});

// Fix orphan courier users - creates Courier profiles for users with role courier but no profile
Route::get('/fix-couriers', function () {
    $fixed = 0;
    $users = \App\Models\User::where('role', 'courier')->get();
    
    foreach ($users as $user) {
        $exists = \App\Models\Courier::where('user_id', $user->id)->exists();
        if (!$exists) {
            \App\Models\Courier::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'status' => 'idle',
            ]);
            $fixed++;
        }
    }
    
    return response()->json([
        'message' => "Fixed $fixed courier(s)",
        'total_users_courier' => $users->count(),
        'couriers_now' => \App\Models\Courier::count(),
    ]);
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::get('/logout', [AuthController::class, 'logoutConfirm'])->name('logout.confirm');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/map-data', [DashboardController::class, 'mapData'])->name('api.map-data');
    
    // AJAX pagination routes for dashboard
    Route::get('/suppliers-ajax', [DashboardController::class, 'suppliersAjax'])->name('api.suppliers');
    Route::get('/factories-ajax', [DashboardController::class, 'factoriesAjax'])->name('api.factories');
    Route::get('/distributors-ajax', [DashboardController::class, 'distributorsAjax'])->name('api.distributors');
    Route::get('/couriers-ajax', [DashboardController::class, 'couriersAjax'])->name('api.couriers');
    Route::get('/search-entities', [DashboardController::class, 'searchEntities'])->name('api.search');

    // Chat routes
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('index');
        Route::get('/contacts', [ChatController::class, 'getContacts'])->name('contacts');
        Route::get('/messages/{conversationId}', [ChatController::class, 'getMessages'])->name('messages');
        Route::post('/send', [ChatController::class, 'sendMessage'])->name('send');
        Route::post('/unsend/{messageId}', [ChatController::class, 'unsendMessage'])->name('unsend');
        Route::post('/broadcast', [ChatController::class, 'sendBroadcast'])->name('broadcast');
        Route::get('/broadcasts', [ChatController::class, 'getBroadcasts'])->name('broadcasts');
        Route::get('/{userId}', [ChatController::class, 'show'])->name('show');
    });

    // Superadmin routes
    Route::middleware([RoleMiddleware::class . ':superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
        Route::get('/', [SuperAdminController::class, 'index'])->name('index');
        Route::get('/users', [SuperAdminController::class, 'users'])->name('users');
        Route::patch('/users/{user}/role', [SuperAdminController::class, 'updateUserRole'])->name('users.role');
        
        // Add entity routes (from map click) - fixed location only
        Route::get('/add/supplier', [SuperAdminController::class, 'addSupplierForm'])->name('add.supplier');
        Route::post('/add/supplier', [SuperAdminController::class, 'storeSupplier'])->name('store.supplier');
        Route::get('/add/factory', [SuperAdminController::class, 'addFactoryForm'])->name('add.factory');
        Route::post('/add/factory', [SuperAdminController::class, 'storeFactory'])->name('store.factory');
        Route::get('/add/distributor', [SuperAdminController::class, 'addDistributorForm'])->name('add.distributor');
        Route::post('/add/distributor', [SuperAdminController::class, 'storeDistributor'])->name('store.distributor');
        
        // Supplier management
        Route::get('/suppliers', [SuperAdminController::class, 'suppliers'])->name('suppliers');
        Route::patch('/suppliers/{supplier}/position', [SuperAdminController::class, 'updateSupplierPosition'])->name('update.supplier.position');
        Route::delete('/suppliers/{supplier}', [SuperAdminController::class, 'deleteSupplierAjax'])->name('delete.supplier');
        
        // Factory management
        Route::get('/factories', [SuperAdminController::class, 'factories'])->name('factories');
        Route::patch('/factories/{factory}/position', [SuperAdminController::class, 'updateFactoryPosition'])->name('update.factory.position');
        Route::delete('/factories/{factory}', [SuperAdminController::class, 'deleteFactoryAjax'])->name('delete.factory');
        
        // Distributor management
        Route::get('/distributors', [SuperAdminController::class, 'distributors'])->name('distributors');
        Route::patch('/distributors/{distributor}/position', [SuperAdminController::class, 'updateDistributorPosition'])->name('update.distributor.position');
        Route::delete('/distributors/{distributor}', [SuperAdminController::class, 'deleteDistributorAjax'])->name('delete.distributor');
        
        // Courier account management
        Route::get('/couriers', [SuperAdminController::class, 'couriers'])->name('couriers');
        Route::get('/couriers/add', [SuperAdminController::class, 'addCourierForm'])->name('add.courier');
        Route::post('/couriers', [SuperAdminController::class, 'storeCourier'])->name('store.courier');
        Route::delete('/couriers/{courier}', [SuperAdminController::class, 'deleteCourier'])->name('delete.courier');
    });

    // Supplier routes
    Route::middleware([RoleMiddleware::class . ':supplier,superadmin'])->prefix('supplier')->name('supplier.')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::post('/setup', [SupplierController::class, 'setup'])->name('setup');
        Route::post('/products', [SupplierController::class, 'addProduct'])->name('products.add');
        Route::post('/products/update', [SupplierController::class, 'updateProduct'])->name('products.update');
        Route::post('/products/delete', [SupplierController::class, 'deleteProduct'])->name('products.delete');
        // Order management for Supplier
        Route::get('/orders', [SupplierController::class, 'incomingOrders'])->name('orders');
        Route::patch('/orders/{order}/status', [SupplierController::class, 'updateOrderStatus'])->name('orders.status');
        Route::post('/orders/{order}/request-courier', [SupplierController::class, 'requestCourier'])->name('orders.request-courier');
    });

    // Factory routes
    Route::middleware([RoleMiddleware::class . ':factory,superadmin'])->prefix('factory')->name('factory.')->group(function () {
        Route::get('/', [FactoryController::class, 'index'])->name('index');
        Route::post('/setup', [FactoryController::class, 'setup'])->name('setup');
        Route::post('/products', [FactoryController::class, 'addProduct'])->name('products.add');
        Route::post('/products/update', [FactoryController::class, 'updateProduct'])->name('products.update');
        Route::post('/products/delete', [FactoryController::class, 'deleteProduct'])->name('products.delete');
        Route::post('/buy', [FactoryController::class, 'buyFromSupplier'])->name('buy');
        // AJAX purchase from map
        Route::post('/buy-from-supplier-ajax', [FactoryController::class, 'buyFromSupplierAjax'])->name('buy-from-supplier');
        // Order management for Factory (incoming from Distributor)
        Route::get('/orders', [FactoryController::class, 'incomingOrders'])->name('orders');
        Route::patch('/orders/{order}/status', [FactoryController::class, 'updateOrderStatus'])->name('orders.status');
        Route::post('/orders/{order}/request-courier', [FactoryController::class, 'requestCourier'])->name('orders.request-courier');
        // My orders (as buyer from Supplier)
        Route::get('/my-orders', [FactoryController::class, 'myOrders'])->name('my-orders');
        Route::patch('/my-orders/{order}/edit', [FactoryController::class, 'editOrder'])->name('my-orders.edit');
        Route::delete('/my-orders/{order}/cancel', [FactoryController::class, 'cancelOrder'])->name('my-orders.cancel');
        // Marketplace (buy from suppliers)
        Route::get('/marketplace', [FactoryController::class, 'marketplace'])->name('marketplace');
    });

    // Distributor routes
    Route::middleware([RoleMiddleware::class . ':distributor,superadmin'])->prefix('distributor')->name('distributor.')->group(function () {
        Route::get('/', [DistributorController::class, 'index'])->name('index');
        Route::post('/setup', [DistributorController::class, 'setup'])->name('setup');
        Route::post('/buy', [DistributorController::class, 'buyFromFactory'])->name('buy');
        // AJAX purchase from map
        Route::post('/buy-from-factory-ajax', [DistributorController::class, 'buyFromFactoryAjax'])->name('buy-from-factory');
        // Order history (my orders)
        Route::get('/orders', [DistributorController::class, 'myOrders'])->name('orders');
        Route::patch('/orders/{order}/edit', [DistributorController::class, 'editOrder'])->name('orders.edit');
        Route::delete('/orders/{order}/cancel', [DistributorController::class, 'cancelOrder'])->name('orders.cancel');
        // Marketplace (buy from factories)
        Route::get('/marketplace', [DistributorController::class, 'marketplace'])->name('marketplace');
    });

    // Courier routes
    Route::middleware([RoleMiddleware::class . ':courier,superadmin'])->prefix('courier')->name('courier.')->group(function () {
        Route::get('/', [CourierController::class, 'index'])->name('index');
        Route::post('/setup', [CourierController::class, 'setup'])->name('setup');
        Route::post('/status', [CourierController::class, 'updateStatus'])->name('status');
        Route::post('/location', [CourierController::class, 'updateLocation'])->name('location');
        Route::patch('/orders/{order}/status', [CourierController::class, 'updateOrderStatus'])->name('orders.status');
        Route::post('/orders/{order}/assign', [CourierController::class, 'assignCourier'])->name('assign');
        // Available deliveries for courier to accept
        Route::get('/available-deliveries', [CourierController::class, 'availableDeliveries'])->name('available-deliveries');
        Route::post('/accept/{order}', [CourierController::class, 'acceptDelivery'])->name('accept');
    });
});
