@props(['colspan', 'title'])

<tr>
    <td colspan="{{ $colspan }}" class="!py-14 text-center hover:!bg-transparent">
        <p class="font-semibold text-dongker">{{ $title }}</p>
        @if ($slot->isNotEmpty())
            <p class="mx-auto mt-1 max-w-sm text-sm text-redup">{{ $slot }}</p>
        @endif
    </td>
</tr>
