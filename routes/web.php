<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\FactoryController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\CourierController;
use App\Http\Middleware\RoleMiddleware;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::get('/logout', [AuthController::class, 'logoutConfirm'])->name('logout.confirm');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/map-data', [DashboardController::class, 'mapData'])->name('api.map-data');
    
    // AJAX pagination routes for dashboard
    Route::get('/api/suppliers', [DashboardController::class, 'suppliersAjax'])->name('api.suppliers');
    Route::get('/api/factories', [DashboardController::class, 'factoriesAjax'])->name('api.factories');
    Route::get('/api/distributors', [DashboardController::class, 'distributorsAjax'])->name('api.distributors');
    Route::get('/api/couriers', [DashboardController::class, 'couriersAjax'])->name('api.couriers');

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
        Route::delete('/suppliers/{supplier}', [SuperAdminController::class, 'deleteSupplier'])->name('delete.supplier');
        
        // Factory management
        Route::get('/factories', [SuperAdminController::class, 'factories'])->name('factories');
        Route::delete('/factories/{factory}', [SuperAdminController::class, 'deleteFactory'])->name('delete.factory');
        
        // Distributor management
        Route::get('/distributors', [SuperAdminController::class, 'distributors'])->name('distributors');
        Route::delete('/distributors/{distributor}', [SuperAdminController::class, 'deleteDistributor'])->name('delete.distributor');
        
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
    });

    // Factory routes
    Route::middleware([RoleMiddleware::class . ':factory,superadmin'])->prefix('factory')->name('factory.')->group(function () {
        Route::get('/', [FactoryController::class, 'index'])->name('index');
        Route::post('/setup', [FactoryController::class, 'setup'])->name('setup');
        Route::post('/products', [FactoryController::class, 'addProduct'])->name('products.add');
        Route::post('/buy', [FactoryController::class, 'buyFromSupplier'])->name('buy');
    });

    // Distributor routes
    Route::middleware([RoleMiddleware::class . ':distributor,superadmin'])->prefix('distributor')->name('distributor.')->group(function () {
        Route::get('/', [DistributorController::class, 'index'])->name('index');
        Route::post('/setup', [DistributorController::class, 'setup'])->name('setup');
        Route::post('/buy', [DistributorController::class, 'buyFromFactory'])->name('buy');
    });

    // Courier routes
    Route::middleware([RoleMiddleware::class . ':courier,superadmin'])->prefix('courier')->name('courier.')->group(function () {
        Route::get('/', [CourierController::class, 'index'])->name('index');
        Route::post('/setup', [CourierController::class, 'setup'])->name('setup');
        Route::post('/status', [CourierController::class, 'updateStatus'])->name('status');
        Route::post('/location', [CourierController::class, 'updateLocation'])->name('location');
        Route::patch('/orders/{order}/status', [CourierController::class, 'updateOrderStatus'])->name('orders.status');
        Route::post('/orders/{order}/assign', [CourierController::class, 'assignCourier'])->name('assign');
    });
});
