@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-gray-50">Register</h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
        <form class="space-y-6" action="#" method="POST">
            @csrf
            <div>
                <x-input id="name" name="name" type="text" label="Name">{{ old('name') }}</x-input>
            </div>
            <div>
                <x-input id="email" name="email" type="text" label="Email address">{{ old('email') }}</x-input>
            </div>
            <div>
                <x-input id="password" name="password" type="password" label="Password"></x-input>
            </div>
            <div>
                <x-input id="password_confirmation" name="password_confirmation" type="password" label="Password confirmation"></x-input>
            </div>

            <div>
                <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Register</button>
            </div>
        </form>
    </div>
</div>
@endsection
