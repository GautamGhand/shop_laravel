<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_place_order(): void
    {
        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => 1, 'quantity' => 1, 'price' => 10.00]
            ],
            'total' => 10.00,
        ]);

        $response->assertStatus(401);
    }

    public function test_customer_can_place_order_successfully(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        Sanctum::actingAs($customer);

        $product1 = Product::factory()->create(['stock' => 10, 'is_active' => true, 'price' => 50.00]);
        $product2 = Product::factory()->create(['stock' => 5, 'is_active' => true, 'price' => 25.00]);

        $payload = [
            'items' => [
                [
                    'product_id' => $product1->id,
                    'quantity'   => 2,
                    'price'      => 50.00,
                ],
                [
                    'product_id' => $product2->id,
                    'quantity'   => 1,
                    'price'      => 25.00,
                ],
            ],
            'total' => 125.00,
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message', 'Order placed successfully');
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'customer_id',
                'total',
                'status',
                'items' => [
                    '*' => [
                        'id',
                        'order_id',
                        'product_id',
                        'quantity',
                        'price',
                        'product',
                    ]
                ],
                'customer',
            ]
        ]);

        // Verify stock decremented
        $this->assertEquals(8, $product1->fresh()->stock);
        $this->assertEquals(4, $product2->fresh()->stock);

        // Verify database records
        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'total'       => 125.00,
            'status'      => 'pending',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product1->id,
            'quantity'   => 2,
            'price'      => 50.00,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product2->id,
            'quantity'   => 1,
            'price'      => 25.00,
        ]);
    }

    public function test_cannot_place_order_if_product_out_of_stock(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        Sanctum::actingAs($customer);

        $product = Product::factory()->create(['stock' => 1, 'is_active' => true, 'price' => 50.00]);

        $payload = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity'   => 2, // exceeding stock of 1
                    'price'      => 50.00,
                ],
            ],
            'total' => 100.00,
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', "Insufficient stock for product '{$product->name}'. Only 1 items remaining.");

        // Verify stock was not decremented (rollback)
        $this->assertEquals(1, $product->fresh()->stock);

        // Verify no order created
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_cannot_place_order_if_product_inactive(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        Sanctum::actingAs($customer);

        $product = Product::factory()->create(['stock' => 10, 'is_active' => false, 'price' => 50.00]);

        $payload = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity'   => 1,
                    'price'      => 50.00,
                ],
            ],
            'total' => 50.00,
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', "Product '{$product->name}' is no longer available.");

        // Verify stock was not decremented
        $this->assertEquals(10, $product->fresh()->stock);

        // Verify no order created
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_validation_errors(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        Sanctum::actingAs($customer);

        // Empty items
        $response = $this->postJson('/api/orders', [
            'items' => [],
            'total' => 0,
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items']);

        // Missing field validation
        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => 999] // missing quantity and price
            ],
            'total' => -10, // negative total
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items.0.quantity', 'items.0.price', 'total']);
    }
}
