@isset($label)
<div class="flex items-center justify-between">
    <label for="password" class="block mb-2 text-sm font-medium @error($id) text-red-500 @else text-gray-50 @enderror">{{ $label }}</label>
</div>
@endisset

<div class="mt-2">
    <input
        {{ $attributes }}
        id="{{ $id }}"
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ $slot }}"
        class="
            border text-sm rounded-lg block w-full p-2.5
            @error($id) bg-red-50 border-red-500 ext-red-900 focus:ring-red-500 dark:bg-gray-700 focus:border-red-500 dark:text-red-500 dark:placeholder-red-500 dark:border-red-500
            @else bg-gray-50 border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500
            @enderror
        "
    >
</div>

@error($id)
<div class="mt-2">
    @foreach ($errors->get($id) as $message)
        <p class="block w-full text-red-800">{{ $message }}</p>
    @endforeach
</div>
@enderror
