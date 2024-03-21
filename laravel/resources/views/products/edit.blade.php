@extends('layouts.app')

@section('title', 'Edit product')

@section('content')
    <section class="bg-white dark:bg-gray-900">
        <div class="py-8 px-4 mx-auto max-w-screen-xl lg:py-16 lg:px-6">
            <div class="mx-auto max-w-screen-sm text-center lg:mb-16 mb-8">
                <h2 class="mb-4 text-3xl lg:text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white">Edit product</h2>
            </div> 

            <div class="relative overflow-x-auto">
                <form action="{{ route('products.update', ['product' => $product->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <tbody>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    Title
                                </th>
                                <td class="px-6 py-4">
                                    <x-input type="text" id="title" name="title">{{ old('title') ?? $product->title }}</x-input>
                                </td>
                            </tr>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    Price
                                </th>
                                <td class="px-6 py-4">
                                    <x-input type="number" step="0.01" id="price" name="price">{{ old('price') ?? $product->price }}</x-input>
                                </td>
                            </tr>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    Currency
                                </th>
                                <td class="px-6 py-4">
                                    <select id="currency_id" name="currency_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}" @if($currency->id === (old('currency_id') ?? $product->currency_id)) selected @endif>{{ $currency->code }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    Created at
                                </th>
                                <td class="px-6 py-4">
                                    -
                                </td>
                            </tr>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    Updated at
                                </th>
                                <td class="px-6 py-4">
                                    -
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Update</button>
                    </div>
                </form>
            </div>

        </div>
    </section>
@endsection
