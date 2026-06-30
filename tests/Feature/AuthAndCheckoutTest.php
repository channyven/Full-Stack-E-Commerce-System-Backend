<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_token_and_default_cart_entities(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'New Customer',
            'email' => 'customer@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);

        $userId = $response->json('data.user.id');

        $this->assertDatabaseHas('users', [
            'email' => 'customer@example.com',
            'role' => 'customer',
        ]);
        $this->assertDatabaseHas('carts', ['user_id' => $userId]);
        $this->assertDatabaseHas('wishlists', ['user_id' => $userId]);
    }

    public function test_checkout_creates_order_and_empties_cart(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::create([
            'name' => 'Home',
            'slug' => 'home',
            'description' => 'Home goods',
            'is_active' => true,
            'position' => 1,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Desk Lamp',
            'slug' => 'desk-lamp',
            'sku' => 'LAMP-001',
            'description' => 'A bright lamp.',
            'price' => 50.00,
            'sale_price' => null,
            'stock_quantity' => 5,
            'low_stock_threshold' => 1,
            'is_featured' => false,
            'status' => ProductStatus::Published->value,
            'is_active' => true,
            'images' => [],
            'seo_title' => 'Desk Lamp',
            'seo_description' => 'A bright lamp.',
        ]);

        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50.00,
        ]);

        $response = $this->postJson('/api/v1/checkout', [
            'payment_method' => 'cod',
            'shipping_address' => [
                'full_name' => 'New Customer',
                'phone' => '0812345678',
                'address' => '1 Market Street',
                'city' => 'Bangkok',
                'state' => 'Bangkok',
                'country' => 'Thailand',
                'postal_code' => '10110',
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $orderNumber = $response->json('data.order_number');
        $this->assertIsString($orderNumber);
        $this->assertStringStartsWith('ORD-', $orderNumber);

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 3,
        ]);
    }
}
