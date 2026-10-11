@props(['title' => null, 'description' => null, 'flush' => false])

<section {{ $attributes->class(['panel flex min-w-0 flex-col']) }}>
    @if ($title)
        <header class="flex items-start justify-between gap-4 px-5 pt-4 pb-3">
            <div class="min-w-0">
                <h2 class="font-bold text-dongker">{{ $title }}</h2>
                @if ($description)
                    <p class="mt-0.5 text-[13px] text-redup">{{ $description }}</p>
                @endif
            </div>
            @isset($action)
                <div class="shrink-0 text-sm">{{ $action }}</div>
            @endisset
        </header>
    @endif

    <div @class(['flex-1', 'px-5 pb-5' => ! $flush, 'pt-4' => ! $flush && ! $title])>
        {{ $slot }}
    </div>
</section>
