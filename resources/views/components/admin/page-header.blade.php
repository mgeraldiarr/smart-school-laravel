@props(['title', 'description' => null, 'back' => null])

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0 max-w-2xl">
        @if ($back)
            <a href="{{ $back['href'] }}" class="mb-2 inline-flex items-center gap-1 text-sm text-redup hover:text-dongker">
                <x-icon name="chevron" class="size-4 rotate-180" />
                {{ $back['label'] }}
            </a>
        @endif
        <h1 class="text-[22px] leading-tight font-extrabold tracking-tight text-dongker">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-sm leading-relaxed text-redup">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
