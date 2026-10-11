{{-- Kanvas Chart.js; spesifikasinya dibaca resources/js/charts.js. --}}
@props(['id', 'spec', 'label', 'height' => 'h-64'])

<div {{ $attributes->class(['relative', $height]) }}>
    <canvas data-chart="{{ $id }}" role="img" aria-label="{{ $label }}"></canvas>
</div>
<script type="application/json" id="{{ $id }}">@json($spec)</script>
