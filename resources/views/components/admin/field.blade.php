@props(['label', 'name', 'hint' => null])

<div {{ $attributes }}>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-semibold">{{ $label }}</label>
    {{ $slot }}
    @error($name)
        <p class="mt-1.5 text-[13px] text-alpa" id="{{ $name }}-error">{{ $message }}</p>
    @else
        @if ($hint)
            <p class="mt-1.5 text-[13px] text-redup">{{ $hint }}</p>
        @endif
    @enderror
</div>
