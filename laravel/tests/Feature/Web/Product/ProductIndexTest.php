<?php

namespace Tests\Feature\Web\Product;

use App\Models\Currency;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_authorized_then_returns_view_with_products(): void
    {
        Product::factory(2)->for(Currency::factory())->create();

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('products.index'));
        
        $response->assertOk();
        $response->assertViewIs('products.index');
        $response->assertViewHas('products');
    }

    public function test_if_no_products_then_returns_view_with_products(): void
    {
        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('products.index'));
        
        $response->assertOk();
        $response->assertViewIs('products.index');
        $response->assertViewHas('products');
    }

    public function test_if_guest_then_redirects_to_login(): void
    {
        $response = $this->get(route('products.index'));
        
        $response->assertFound();
        $response->assertRedirectToRoute('login');
    }
}
