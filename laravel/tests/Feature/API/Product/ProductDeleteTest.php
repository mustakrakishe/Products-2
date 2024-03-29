<?php

namespace Tests\Feature\API\Product;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_authorized_then_returns_no_content(): void
    {
        $product = Product::factory()->create();

        $response = $this
            ->actingAs(User::factory()->create())
            ->delete(route('api.products.destroy', compact('product')));
        
        $response->assertNoContent();
        $this->assertModelMissing($product);
    }

    public function test_if_guest_then_returns_unauthorized(): void
    {
        $product = Product::factory()->create();

        $response = $this->delete(route('api.products.destroy', compact('product')));
        
        $response->assertUnauthorized();
    }

    public function test_if_product_does_not_exist_then_returns_not_found(): void
    {
        $response = $this
            ->actingAs(User::factory()->create())
            ->delete(route('api.products.destroy', ['product' => 1]));
        
        $response->assertNotFound();
    }
}
