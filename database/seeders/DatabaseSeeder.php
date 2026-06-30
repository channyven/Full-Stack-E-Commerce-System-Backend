<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Enums\UserStatus;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate([
            'slug' => 'admin',
        ], [
            'name' => 'Administrator',
            'description' => 'Full access to the storefront and admin panel.',
        ]);

        $customerRole = Role::firstOrCreate([
            'slug' => 'customer',
        ], [
            'name' => 'Customer',
            'description' => 'Default shop customer role.',
        ]);

        foreach ([
            ['name' => 'Manage catalog', 'slug' => 'manage-catalog'],
            ['name' => 'Manage orders', 'slug' => 'manage-orders'],
            ['name' => 'Manage users', 'slug' => 'manage-users'],
        ] as $permissionData) {
            Permission::firstOrCreate(['slug' => $permissionData['slug']], $permissionData);
        }

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_admin' => true,
            'role' => 'admin',
            'status' => UserStatus::Active->value,
        ]);

        $customer = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'customer',
            'status' => UserStatus::Active->value,
        ]);

        $admin->roles()->syncWithoutDetaching([$adminRole->id]);
        $customer->roles()->syncWithoutDetaching([$customerRole->id]);

        Setting::firstOrCreate(['key' => 'store_name'], [
            'value' => 'Nova Shop',
            'type' => 'string',
            'group' => 'general',
        ]);

        $categories = collect([
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Gadgets, devices, and accessories.',
                'position' => 1,
            ],
            [
                'name' => 'Home & Living',
                'slug' => 'home-living',
                'description' => 'Daily essentials and home decor.',
                'position' => 2,
            ],
        ])->map(function (array $categoryData): Category {
            return Category::firstOrCreate(['slug' => $categoryData['slug']], $categoryData);
        });

        $electronics = $categories->firstWhere('slug', 'electronics');
        $home = $categories->firstWhere('slug', 'home-living');

        $products = [
            [
                'category_id' => $electronics->id,
                'name' => 'Aurora Headphones',
                'slug' => 'aurora-headphones',
                'sku' => 'AUR-HP-001',
                'description' => 'Wireless over-ear headphones with active noise cancellation.',
                'price' => 149.00,
                'sale_price' => 129.00,
                'stock_quantity' => 24,
                'low_stock_threshold' => 5,
                'is_featured' => true,
                'status' => ProductStatus::Published->value,
                'is_active' => true,
                'images' => ['/images/products/aurora-headphones-1.jpg', '/images/products/aurora-headphones-2.jpg'],
                'seo_title' => 'Aurora Headphones',
                'seo_description' => 'Premium wireless headphones for everyday use.',
            ],
            [
                'category_id' => $electronics->id,
                'name' => 'Pixel Camera',
                'slug' => 'pixel-camera',
                'sku' => 'PIX-CAM-010',
                'description' => 'Compact mirrorless camera for creators and travelers.',
                'price' => 799.00,
                'sale_price' => null,
                'stock_quantity' => 8,
                'low_stock_threshold' => 3,
                'is_featured' => true,
                'status' => ProductStatus::Published->value,
                'is_active' => true,
                'images' => ['/images/products/pixel-camera-1.jpg'],
                'seo_title' => 'Pixel Camera',
                'seo_description' => 'A compact camera that delivers sharp photos.',
            ],
            [
                'category_id' => $home->id,
                'name' => 'Luna Desk Lamp',
                'slug' => 'luna-desk-lamp',
                'sku' => 'LUNA-LP-100',
                'description' => 'Minimal LED desk lamp with warm and cool light modes.',
                'price' => 39.00,
                'sale_price' => 29.00,
                'stock_quantity' => 50,
                'low_stock_threshold' => 10,
                'is_featured' => false,
                'status' => ProductStatus::Published->value,
                'is_active' => true,
                'images' => ['/images/products/luna-desk-lamp-1.jpg'],
                'seo_title' => 'Luna Desk Lamp',
                'seo_description' => 'Soft lighting for work and study.',
            ],
        ];

        foreach ($products as $productData) {
            $images = $productData['images'];
            unset($productData['images']);

            $product = Product::firstOrCreate(['slug' => $productData['slug']], $productData);

            foreach ($images as $sortOrder => $path) {
                ProductImage::firstOrCreate([
                    'product_id' => $product->id,
                    'path' => $path,
                ], [
                    'alt_text' => $product->name,
                    'sort_order' => $sortOrder,
                ]);
            }
        }
    }
}
