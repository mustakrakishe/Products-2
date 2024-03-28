<?php

namespace Tests\Feature\Web\Auth;

use App\Models\Currency;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_authorized_then_returns_view_with_product(): void
    {
        $product = Product::factory()->create();

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('products.show', [
                'product' => $product,
            ]));
        
        $response->assertOk();
        $response->assertViewIs('products.show');
        $response->assertViewHas('product');
        $this->assertTrue($response->viewData('product')->is($product));
    }

    public function test_if_product_does_not_exist_then_returns_not_found(): void
    {
        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('products.show', [
                'product' => 1,
            ]));
        
        $response->assertNotFound();
    }

    public function test_if_guest_then_redirects_to_login(): void
    {
        $response = $this
            ->get(route('products.show', [
                'product' => Product::factory()->create(),
            ]));
        
        $response->assertFound();
        $response->assertRedirectToRoute('login');
    }
}
