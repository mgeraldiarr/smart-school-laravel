@if (session('success'))
    <div class="mb-5 flex items-start gap-2.5 rounded-lg border border-hadir/25 bg-hadir/5 px-4 py-3 text-sm text-hadir" role="status">
        <x-icon name="check" class="mt-px size-4" />
        <p>{{ session('success') }}</p>
    </div>
@endif

@if (session('error'))
    <div class="mb-5 flex items-start gap-2.5 rounded-lg border border-alpa/25 bg-alpa/5 px-4 py-3 text-sm text-alpa" role="alert">
        <x-icon name="alert" class="mt-px size-4" />
        <p>{{ session('error') }}</p>
    </div>
@endif
