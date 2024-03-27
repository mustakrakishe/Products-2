<?php

namespace Tests\Feature\API\Auth;

use App\Models\Currency;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductCreateTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('validInputDataProvider')]
    public function test_if_input_is_valid_then_creates_and_returns_product(callable $inputCallback): void
    {
        $input = $inputCallback();

        $response = $this
            ->actingAs(User::factory()->create())
            ->post('api/products', $input);
        
        $product = Product::firstWhere('title', trim($input['title']));
        
        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseHas('products', array_map('trim', $input));
        $response->assertCreated();
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

    public static function validInputDataProvider(): array
    {
        return [
            'regular' => [function () {
                return [
                    'title'       => 'New Product',
                    'price'       => 99.99,
                    'currency_id' => Currency::factory()->create()->id,
                ];
            }],
            'title_is_shortest' => [function () {
                return [
                    'title'       => 'N',
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
            'title_is_trimless' => [function () {
                return [
                    'title'       => ' New Product ',
                    'price'       => 99.99,
                    'currency_id' => Currency::factory()->create()->id,
                ];
            }],
            'price_is_string' => [function () {
                return [
                    'title'       => 'New Product',
                    'price'       => '99.99',
                    'currency_id' => Currency::factory()->create()->id,
                ];
            }],
            'price_has_no_decimal' => [function () {
                return [
                    'title'       => 'New Product',
                    'price'       => 99,
                    'currency_id' => Currency::factory()->create()->id,
                ];
            }],
            'price_has_one_decimal' => [function () {
                return [
                    'title'       => 'New Product',
                    'price'       => 99.9,
                    'currency_id' => Currency::factory()->create()->id,
                ];
            }],
            'price_is_min' => [function () {
                return [
                    'title'       => 'New Product',
                    'price'       => 0,
                    'currency_id' => Currency::factory()->create()->id,
                ];
            }],
            'price_is_max' => [function () {
                return [
                    'title'       => 'New Product',
                    'price'       => 999999.99,
                    'currency_id' => Currency::factory()->create()->id,
                ];
            }],
            'currency_id_is_string' => [function () {
                return [
                    'title'       => 'New Product',
                    'price'       => 99.99,
                    'currency_id' => strval(Currency::factory()->create()->id),
                ];
            }],
        ];
    }

    public function test_if_guest_then_returns_unauthorized(): void
    {
        $response = $this->post(
            'api/products',
            Product::factory()->make()->toArray()
        );
        
        $response->assertUnauthorized();
    }

    #[DataProvider('invalidInputDataProvider')]
    public function test_if_input_is_invalid_then_fails_validation(string $invalid, callable $inputCallback): void
    {
        $response = $this
            ->actingAs(User::factory()->create())
            ->post('api/products', $inputCallback());

        $response->assertUnprocessable();
        $response->assertInvalid($invalid);
    }

    public static function invalidInputDataProvider(): array
    {
        return [
            'title_is_missing' => [
                'invalid' => 'title',
                'inputCallback' => function () {
                    return [
                        'price'       => 99.99,
                        'currency_id' => Currency::factory()->create()->id,
                    ];
                },
            ],
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
            'title_has_wrong_data_type' => [
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

            'price_is_missing' => [
                'invalid' => 'price',
                'inputCallback' => function () {
                    return [
                        'title'       => 'New Product',
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

            'currency_is_missing' => [
                'invalid' => 'currency_id',
                'inputCallback' => function () {
                    return [
                        'title'       => 'New Product',
                        'price'       => 99.99,
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
                        'currency_id' => 1,
                    ];
                },
            ],
        ];
    }
}
