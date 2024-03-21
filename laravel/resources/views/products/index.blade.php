@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <section class="bg-white dark:bg-gray-900">
        <div class="py-8 px-4 mx-auto max-w-screen-xl lg:py-16 lg:px-6">
            <div class="mx-auto max-w-screen-sm text-center lg:mb-16 mb-8">
                <h2 class="mb-4 text-3xl lg:text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white">Products</h2>
            </div> 

            <div class="grid gap-8 lg:grid-cols-4">
                <a href="{{ route('products.create') }}" class="block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-700 dark:text-gray-400">Create new</h5>
                </a>

                @foreach ($products as $product)
                    <a href="{{ route('products.show', ['product' => $product->id]) }}" class="block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $product->title }}</h5>
                        <p class="font-normal text-gray-700 dark:text-gray-400">{{ $product->price }} {{ $product->currency->code }}</p>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $products->links() }}
            </div>
        </div>
    </section>
@endsection
