<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCollection;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $products = Product::with('currency')
            ->orderByDesc('id')
            ->paginate(
                perPage: 7,
                page: request()->page
            );

        return (new ProductCollection($products))->response();
    }
}
