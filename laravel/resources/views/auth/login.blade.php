@extends('layouts.app')

@section('title', 'Log in')

@section('content')
<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
        <form class="space-y-6" action="#" method="POST">
            @csrf
            <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-gray-50">Log in</h2>
            </div>
            
            <div>
                <x-input id="email" name="email" type="text" label="Email address">{{ old('email') }}</x-input>
            </div>
            <div>
                <x-input id="password" name="password" type="password" label="Password"></x-input>
            </div>

            <div class="text-sm">
                <a href="{{ route('password.reset.send') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">Forgot password?</a>
            </div>

            <div>
                <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Log in</button>
            </div>

            <div class="text-green-700">
                <p>{{ session('message') }}</p>
            </div>
        </form>
    </div>
</div>
@endsection
