<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Factory;
use App\Models\Distributor;
use App\Models\Courier;
use App\Models\SupplierProduct;
use App\Models\FactoryProduct;
use App\Models\DistributorStock;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create products
        $products = [
            ['name' => 'Raw Palm Oil', 'category' => 'Raw Materials', 'unit' => 'liters', 'base_price' => 15.00],
            ['name' => 'Refined Cooking Oil', 'category' => 'Finished Goods', 'unit' => 'liters', 'base_price' => 25.00],
            ['name' => 'Premium Olive Oil', 'category' => 'Premium', 'unit' => 'liters', 'base_price' => 50.00],
            ['name' => 'Coconut Oil', 'category' => 'Specialty', 'unit' => 'liters', 'base_price' => 30.00],
            ['name' => 'Sunflower Oil', 'category' => 'Standard', 'unit' => 'liters', 'base_price' => 20.00],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // Create sample supplier user and profile
        $supplierUser = User::create([
            'name' => 'PT Supplier Maju',
            'email' => 'supplier@example.com',
            'role' => 'supplier',
            'google_id' => null,
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $supplier = Supplier::create([
            'user_id' => $supplierUser->id,
            'name' => 'PT Supplier Maju',
            'description' => 'Leading raw material supplier for cooking oil industry',
            'address' => 'Jl. Industri No. 100, Medan, North Sumatra',
            'latitude' => 3.5952,
            'longitude' => 98.6722,
            'phone' => '+62 61 123456',
            'email' => 'info@suppliermaju.com',
        ]);

        SupplierProduct::create([
            'supplier_id' => $supplier->id,
            'product_id' => 1,
            'price' => 14.50,
            'stock_quantity' => 100000,
        ]);

        // Create sample factory user and profile
        $factoryUser = User::create([
            'name' => 'PT Factory Jaya',
            'email' => 'factory@example.com',
            'role' => 'factory',
            'google_id' => null,
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $factory = Factory::create([
            'user_id' => $factoryUser->id,
            'name' => 'PT Factory Jaya',
            'description' => 'Modern cooking oil processing facility',
            'address' => 'Kawasan Industri MM2100, Bekasi, West Java',
            'latitude' => -6.2884,
            'longitude' => 107.0970,
            'phone' => '+62 21 789012',
            'email' => 'info@factoryjaya.com',
            'production_capacity' => 50000,
        ]);

        FactoryProduct::create([
            'factory_id' => $factory->id,
            'product_id' => 2,
            'price' => 24.00,
            'production_quantity' => 30000,
        ]);

        // Create sample distributor user and profile
        $distributorUser = User::create([
            'name' => 'CV Distributor Sejahtera',
            'email' => 'distributor@example.com',
            'role' => 'distributor',
            'google_id' => null,
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $distributor = Distributor::create([
            'user_id' => $distributorUser->id,
            'name' => 'CV Distributor Sejahtera',
            'description' => 'National cooking oil distribution network',
            'address' => 'Jl. Gatot Subroto No. 55, Jakarta Selatan',
            'latitude' => -6.2297,
            'longitude' => 106.8015,
            'phone' => '+62 21 345678',
            'email' => 'orders@distributorsejahtera.com',
            'warehouse_capacity' => 100000,
        ]);

        DistributorStock::create([
            'distributor_id' => $distributor->id,
            'product_id' => 2,
            'quantity' => 15000,
            'min_stock_level' => 5000,
        ]);

        // Create sample courier user and profile
        $courierUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'courier@example.com',
            'role' => 'courier',
            'google_id' => null,
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        Courier::create([
            'user_id' => $courierUser->id,
            'name' => 'Budi Santoso',
            'vehicle_type' => 'Truck',
            'license_plate' => 'B 1234 ABC',
            'phone' => '+62 812 9876543',
            'current_latitude' => -6.1751,
            'current_longitude' => 106.8451,
            'status' => 'available',
        ]);

        // Create another supplier in Surabaya
        $supplier2User = User::create([
            'name' => 'PT Supplier Timur',
            'email' => 'supplier2@example.com',
            'role' => 'supplier',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $supplier2 = Supplier::create([
            'user_id' => $supplier2User->id,
            'name' => 'PT Supplier Timur',
            'description' => 'East Java palm oil supplier',
            'address' => 'Jl. Raya Surabaya No. 88, Surabaya',
            'latitude' => -7.2575,
            'longitude' => 112.7521,
            'phone' => '+62 31 654321',
        ]);

        SupplierProduct::create([
            'supplier_id' => $supplier2->id,
            'product_id' => 4, // Coconut Oil
            'price' => 28.00,
            'stock_quantity' => 50000,
        ]);
    }
}
