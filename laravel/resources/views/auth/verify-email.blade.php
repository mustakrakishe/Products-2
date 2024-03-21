@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-gray-50">Verify email</h2>
        <p>Follow the link at {{ $email }} to confirm your email.</p>
    </div>
</div>
@endsection
