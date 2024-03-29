<?php

namespace Tests\Feature\Web\Product;

use App\Models\Currency;
use App\Models\Product;
use App\Models\User;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductUpdateTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('validInputDataProvider')]
    public function test_if_input_is_valid_then_updates_and_redirects_to_view(Closure $inputCallback): void
    {
        $product = Product::factory()->create([
            'title'       => 'New Product',
            'price'       => 55.55,
            'currency_id' => Currency::factory()->create()->id,
        ]);

        $input = $inputCallback();

        $response = $this
            ->actingAs(User::factory()->create())
            ->put(
                route('products.update', compact('product')),
                $input
            );
        
        $product->refresh();
        
        $response->assertFound();
        $response->assertRedirectToRoute('products.show', compact('product'));
        $this->assertModelIsUpdated($product, $input);
    }

    protected function assertModelIsUpdated(Product $product, array $input): void
    {
        foreach ($input as $attribute => $value) {
            $this->assertEquals(
                is_string($value) ? trim($value) : $value,
                $product->{$attribute}
            );
        }
    }

    public static function validInputDataProvider(): array
    {
        return [
            'regular' => [function () {
                return [
                    'title'       => 'Updated Product',
                    'price'       => 99.99,
                    'currency_id' => Currency::factory()->create()->id,
                ];
            }],
            'empyty' => [function () {
                return [];
            }],
            'title_is_missing' => [function () {
                return [
                    'price'       => 99.99,
                    'currency_id' => Currency::factory()->create()->id,
                ];
            }],
            'title_is_shortest' => [function () {
                return [
                    'title'       => 'U',
                    'price'       => 99.99,
                    'currency_id' => Currency::factory()->create()->id,
                ];
            }],
            'title_is_longest' => [function () {
                return [
                    'title'       => str_repeat('a', 255),
                    'price'       => 99.99,
                    'currency_id' => Currency::factory()->create()->id,
                ];
            }],
            'title_is_the_same' => [function () {
                return [
                    'title'       => 'New Product',
                    'price'       => 99.99,
                    'currency_id' => Currency::factory()->create()->id,
                ];
            }],
            'title_is_trimless' => [function () {
                return [
                    'title'       => ' Updated Product ',
                    'price'       => 99.99,
                    'currency_id' => Currency::factory()->create()->id,
                ];
            }],
            'price_is_missing' => [function () {
                return [
                    'title'       => 'Updated Product',
                    'currency_id' => Currency::factory()->create()->id,
                ];
            }],
            'price_is_string' => [function () {
                return [
                    'title'       => 'Updated Product',
                    'price'       => '99.99',
                    'currency_id' => Currency::factory()->create()->id,
                ];
            }],
            'price_has_no_decimal' => [function () {
                return [
                    'title'       => 'Updated Product',
                    'price'       => 99,
                    'currency_id' => Currency::factory()->create()->id,
                ];
            }],
            'price_has_one_decimal' => [function () {
                return [
                    'title'       => 'Updated Product',
                    'price'       => 99.9,
                    'currency_id' => Currency::factory()->create()->id,
                ];
            }],
            'price_is_min' => [function () {
                return [
                    'title'       => 'Updated Product',
                    'price'       => 0,
                    'currency_id' => Currency::factory()->create()->id,
                ];
            }],
            'price_is_max' => [function () {
                return [
                    'title'       => 'Updated Product',
                    'price'       => 999999.99,
                    'currency_id' => Currency::factory()->create()->id,
                ];
            }],
            'currency_id_is_missing' => [function () {
                return [
                    'title'       => 'Updated Product',
                    'price'       => 99.99,
                ];
            }],
            'currency_id_is_string' => [function () {
                return [
                    'title'       => 'Updated Product',
                    'price'       => 99.99,
                    'currency_id' => strval(Currency::factory()->create()->id),
                ];
            }],
        ];
    }

    #[DataProvider('invalidInputDataProvider')]
    public function test_if_input_is_invalid_then_fails_validation(string $invalid, Closure $inputCallback): void
    {
        $product = Product::factory()->create([
            'title'       => 'New Product',
            'price'       => 55.55,
            'currency_id' => Currency::factory()->create()->id,
        ]);

        $input = $inputCallback();

        $response = $this
            ->actingAs(User::factory()->create())
            ->put(
                route('products.update', compact('product')),
                $input,
                ['HTTP_REFERER' => route('products.edit', compact('product'))]
            );
        
        $response->assertFound();
        $response->assertRedirectToRoute('products.edit', compact('product'));
        $response->assertInvalid($invalid);
    }

    public static function invalidInputDataProvider(): array
    {
        return [
            'title_is_null' => [
                'invalid' => 'title',
                'inputCallback' => function () {
                    return [
                        'title'       => null,
                        'price'       => 99.99,
                        'currency_id' => Currency::factory()->create()->id,
                    ];
                },
            ],
            'title_is_empty' => [
                'invalid' => 'title',
                'inputCallback' => function () {
                    return [
                        'title'       => '',
                        'price'       => 99.99,
                        'currency_id' => Currency::factory()->create()->id,
                    ];
                },
            ],
            'title_has_wrong_format' => [
                'invalid' => 'title',
                'inputCallback' => function () {
                    return [
                        'title'       => 123,
                        'price'       => 99.99,
                        'currency_id' => Currency::factory()->create()->id,
                    ];
                },
            ],
            'title_is_too_long' => [
                'invalid' => 'title',
                'inputCallback' => function () {
                    return [
                        'title'       => str_repeat('a', 256),
                        'price'       => 99.99,
                        'currency_id' => Currency::factory()->create()->id,
                    ];
                },
            ],
            'title_is_not_unique' => [
                'invalid' => 'title',
                'inputCallback' => function () {
                    return [
                        'title'       => Product::factory()->create()->title,
                        'price'       => 99.99,
                        'currency_id' => Currency::factory()->create()->id,
                    ];
                },
            ],

            'price_is_null' => [
                'invalid' => 'price',
                'inputCallback' => function () {
                    return [
                        'title'       => 'New Product',
                        'price'       => null,
                        'currency_id' => Currency::factory()->create()->id,
                    ];
                },
            ],
            'price_has_wrong_format' => [
                'invalid' => 'price',
                'inputCallback' => function () {
                    return [
                        'title'       => 'New Product',
                        'price'       => 'abc',
                        'currency_id' => Currency::factory()->create()->id,
                    ];
                },
            ],
            'price_has_too_many_decimals' => [
                'invalid' => 'price',
                'inputCallback' => function () {
                    return [
                        'title'       => 'New Product',
                        'price'       => 99.999,
                        'currency_id' => Currency::factory()->create()->id,
                    ];
                },
            ],
            'price_is_too_low' => [
                'invalid' => 'price',
                'inputCallback' => function () {
                    return [
                        'title'       => 'New Product',
                        'price'       => -1,
                        'currency_id' => Currency::factory()->create()->id,
                    ];
                },
            ],
            'price_is_too_high' => [
                'invalid' => 'price',
                'inputCallback' => function () {
                    return [
                        'title'       => 'New Product',
                        'price'       => 1000000,
                        'currency_id' => Currency::factory()->create()->id,
                    ];
                },
            ],

            'currency_is_null' => [
                'invalid' => 'currency_id',
                'inputCallback' => function () {
                    return [
                        'title'       => 'New Product',
                        'price'       => 99.99,
                        'currency_id' => null,
                    ];
                },
            ],
            'currency_does_not_exist' => [
                'invalid' => 'currency_id',
                'inputCallback' => function () {
                    return [
                        'title'       => 'New Product',
                        'price'       => 99.99,
                        'currency_id' => 2,
                    ];
                },
            ],
        ];
    }

    public function test_if_product_does_not_exist_then_returns_not_found(): void
    {
        $response = $this
            ->actingAs(User::factory()->create())
            ->put(route('products.update', ['product' => 1]), [
                'title'       => 'Updated Product',
                'price'       => 99.99,
                'currency_id' => Currency::factory()->create()->id,
            ]);
        
        $response->assertNotFound();
    }

    public function test_if_guest_then_redirects_to_login(): void
    {
        $product = Product::factory()->create([
            'title'       => 'New Product',
            'price'       => 55.55,
            'currency_id' => Currency::factory()->create()->id,
        ]);

        $response = $this->put(route('products.update', compact('product')), [
            'title'       => 'Updated Product',
            'price'       => 99.99,
            'currency_id' => Currency::factory()->create()->id,
        ]);
        
        $response->assertFound();
        $response->assertRedirectToRoute('login');
    }
}
