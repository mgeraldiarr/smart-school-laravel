@props(['href', 'icon', 'active' => false])

<a href="{{ $href }}"
   @if ($active) aria-current="page" @endif
   {{ $attributes->class([
       'relative flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors',
       'bg-dongker-50 font-semibold text-dongker' => $active,
       'text-redup hover:bg-latar hover:text-teks' => ! $active,
   ]) }}>
    @if ($active)
        <span class="absolute inset-y-2 -left-3 w-[3px] rounded-r bg-emas" aria-hidden="true"></span>
    @endif
    <x-icon :name="$icon" />
    <span>{{ $slot }}</span>
</a>
