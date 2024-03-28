<?php

namespace Tests\Feature\Web\Auth;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_authorized_then_deletes_and_redirects_to_index(): void
    {
        $product = Product::factory()->create();

        $response = $this
            ->actingAs(User::factory()->create())
            ->delete(route('products.destroy', ['product' => $product->id]));
        
        $response->assertFound();
        $response->assertRedirectToRoute('products.index');
        $this->assertModelMissing($product);
    }

    public function test_if_guest_then_redirects_to_login(): void
    {
        $product = Product::factory()->create();

        $response = $this->delete(route('products.destroy', ['product' => $product->id]));
        
        $response->assertFound();
        $response->assertRedirectToRoute('login');
    }

    public function test_if_not_found_then_returns_not_found(): void
    {
        $response = $this
            ->actingAs(User::factory()->create())
            ->delete(route('products.destroy', ['product' => 1]));
        
        $response->assertNotFound();
    }
}
