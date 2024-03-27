<?php

namespace Tests\Feature\API\Auth;

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
            ->delete('api/products/' . $product->id);
        
        $response->assertNoContent();
        $this->assertModelMissing($product);
    }

    public function test_if_guest_then_returns_unauthorized(): void
    {
        $product = Product::factory()->create();

        $response = $this->delete('api/products/' . $product->id);
        
        $response->assertUnauthorized();
    }

    public function test_if_product_does_not_exist_then_returns_not_found(): void
    {
        $response = $this
            ->actingAs(User::factory()->create())
            ->delete('api/products/1');
        
        $response->assertNotFound();
    }
}
