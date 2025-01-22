<x-filament-panels::page>
    {{-- <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($this->getActionss() as $action)
            <div class="p-4 border rounded-lg shadow">
                <h2 class="text-lg font-bold">{{    ($action->button()) }}</h2>
                {{ $action->button() }}
            </div>
        @endforeach
    </div> --}}
    <x-filament-actions::modals />
</x-filament-panels::page>
