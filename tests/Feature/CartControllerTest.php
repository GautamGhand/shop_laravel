<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_cart(): void
    {
        $this->getJson('/api/cart')->assertStatus(401);
        $this->postJson('/api/cart', ['product_id' => 1, 'quantity' => 1])->assertStatus(401);
        $this->putJson('/api/cart/1', ['quantity' => 2])->assertStatus(401);
        $this->deleteJson('/api/cart/1')->assertStatus(401);
        $this->deleteJson('/api/cart')->assertStatus(401);
    }

    public function test_user_can_get_empty_cart(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/cart');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(0, 'data');
    }

    public function test_user_can_add_item_to_cart(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        Sanctum::actingAs($user);

        $product = Product::factory()->create(['stock' => 10, 'is_active' => true]);

        $response = $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message', 'Product added to cart');
        $response->assertJsonPath('data.product_id', $product->id);
        $response->assertJsonPath('data.quantity', 2);

        $this->assertDatabaseHas('cart_items', [
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);
    }

    public function test_user_adding_duplicate_product_increments_quantity(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        Sanctum::actingAs($user);

        $product = Product::factory()->create(['stock' => 10, 'is_active' => true]);

        // Add 2
        $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        // Add 3 more
        $response = $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity'   => 3,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.quantity', 5);

        $this->assertDatabaseHas('cart_items', [
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 5,
        ]);
    }

    public function test_cannot_add_item_if_product_inactive(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        Sanctum::actingAs($user);

        $product = Product::factory()->create(['stock' => 10, 'is_active' => false]);

        $response = $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cannot_add_item_exceeding_stock(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        Sanctum::actingAs($user);

        $product = Product::factory()->create(['stock' => 3, 'is_active' => true]);

        $response = $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity'   => 4,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_user_can_update_cart_quantity(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        Sanctum::actingAs($user);

        $product = Product::factory()->create(['stock' => 10, 'is_active' => true]);
        $cartItem = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $response = $this->putJson("/api/cart/{$product->id}", [
            'quantity' => 5,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.quantity', 5);

        $this->assertEquals(5, $cartItem->fresh()->quantity);
    }

    public function test_user_cannot_update_cart_quantity_exceeding_stock(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        Sanctum::actingAs($user);

        $product = Product::factory()->create(['stock' => 3, 'is_active' => true]);
        $cartItem = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $response = $this->putJson("/api/cart/{$product->id}", [
            'quantity' => 4,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $this->assertEquals(2, $cartItem->fresh()->quantity);
    }

    public function test_user_can_remove_item_from_cart(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        Sanctum::actingAs($user);

        $product = Product::factory()->create(['stock' => 10, 'is_active' => true]);
        CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $response = $this->deleteJson("/api/cart/{$product->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message', 'Product removed from cart');
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_user_can_clear_cart(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        Sanctum::actingAs($user);

        $product1 = Product::factory()->create(['stock' => 10, 'is_active' => true]);
        $product2 = Product::factory()->create(['stock' => 5, 'is_active' => true]);

        CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product1->id,
            'quantity'   => 2,
        ]);
        CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product2->id,
            'quantity'   => 1,
        ]);

        $response = $this->deleteJson('/api/cart');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message', 'Cart cleared successfully');
        $this->assertDatabaseCount('cart_items', 0);
    }
}
