<div 
    x-data="{ 
        state: @js($getState()?->pluck('title')->toArray() ?? []), 
        expanded: false, 
        limit: {{ $getLimit() }} 
    }"
    class="flex flex-wrap min-w-80 gap-1 py-2 items-center"
>

    <template x-for="item in (expanded ? state : state.slice(0, limit))" >
        <x-filament::badge size="sm" x-text="item"></x-filament::badge>
    </template>

    <div x-show="state.length > limit" class="ml-auto mr-6">
        <button 
            @click.prevent="expanded = !expanded" 
            class="text-xs font-medium text-secondary hover:underline focus:outline-none"
            x-text="expanded ? 'Mostrar Menos' : 'Mostrar Mais'"
        ></button>
    </div>
</div>