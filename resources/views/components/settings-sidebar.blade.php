<div {{ $attributes->merge(['class' => 'bg-white border rounded-xl shadow-sm p-4 lg:w-64']) }}>
    <nav class="space-y-2">
        {{ $slot }}
    </nav>
</div>
