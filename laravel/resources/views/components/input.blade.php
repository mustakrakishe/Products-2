<div class="flex items-center justify-between">
    <label for="password" class="block text-sm font-medium leading-6 text-gray-50">{{ $label }}</label>
</div>

<div class="mt-2">
    <input {{ $attributes }} id="{{ $id }}" type="{{ $type }}" class="block w-full rounded-md border-0 py-1.5 text-gray-50 shadow-sm ring-1 ring-inset ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 bg-gray-800" value="{{ $slot }}">
</div>
