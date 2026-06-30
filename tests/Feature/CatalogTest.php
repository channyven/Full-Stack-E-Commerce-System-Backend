<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_endpoints_return_published_products(): void
    {
        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Electronics and gadgets',
            'is_active' => true,
            'position' => 1,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Demo Product',
            'slug' => 'demo-product',
            'sku' => 'DEMO-001',
            'description' => 'A sample product for tests.',
            'price' => 99.99,
            'sale_price' => 79.99,
            'stock_quantity' => 12,
            'low_stock_threshold' => 2,
            'is_featured' => true,
            'status' => ProductStatus::Published->value,
            'is_active' => true,
            'images' => ['/images/demo.jpg'],
            'seo_title' => 'Demo Product',
            'seo_description' => 'Demo product description',
        ]);

        $response = $this->getJson('/api/v1/products');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['name' => 'Demo Product']);
    }
}
