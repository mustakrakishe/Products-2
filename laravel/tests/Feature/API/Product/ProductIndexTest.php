<?php

namespace Tests\Feature\API\Product;

use App\Models\Currency;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_authorized_then_returns_products(): void
    {
        $product1 = Product::factory()->for(Currency::factory())->create();
        $product2 = Product::factory()->for(Currency::factory())->create();

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('api.products.index'));
        
        $response->assertOk();
        $response->assertJson([
            'data' => [
                [
                    'id'         => $product2->id,
                    'title'      => $product2->title,
                    'price'      => $product2->price,
                    'created_at' => $product2->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $product2->updated_at->format('Y-m-d H:i:s'),
                    'currency' => [
                        'id'   => $product2->currency->id,
                        'code' => $product2->currency->code,
                    ],

                ],
                [
                    'id'         => $product1->id,
                    'title'      => $product1->title,
                    'price'      => $product1->price,
                    'created_at' => $product1->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $product1->updated_at->format('Y-m-d H:i:s'),
                    'currency' => [
                        'id'   => $product1->currency->id,
                        'code' => $product1->currency->code,
                    ],

                ],
            ],
        ]);
    }

    public function test_if_no_products_then_returns_empty_ok(): void
    {
        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('api.products.index'));
        
        $response->assertOk();
        $response->assertJson([
            'data' => [],
        ]);
    }

    public function test_if_guest_then_returns_unauthorized(): void
    {
        $response = $this->get(route('api.products.index'));
        
        $response->assertUnauthorized();
    }
}
