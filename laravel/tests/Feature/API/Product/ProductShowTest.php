<?php

namespace Tests\Feature\API\Product;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_authorized_then_returns_product(): void
    {
        $product = Product::factory()->create();

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('api.products.show', compact('product')));
        
        $response->assertOk();
        $response->assertJson([
            'data' => [
                'id'         => $product->id,
                'title'      => $product->title,
                'price'      => $product->price,
                'created_at' => $product->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $product->updated_at->format('Y-m-d H:i:s'),
                'currency' => [
                    'id'   => $product->currency->id,
                    'code' => $product->currency->code,
                ],
            ],
        ]);
    }

    public function test_if_product_does_not_exist_then_returns_not_found(): void
    {
        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('api.products.show', ['product' => 1]));
        
        $response->assertNotFound();
    }

    public function test_if_guest_then_returns_unauthorized(): void
    {
        $product = Product::factory()->create();

        $response = $this->get(route('api.products.show', compact('product')));
        
        $response->assertUnauthorized();
    }
}
