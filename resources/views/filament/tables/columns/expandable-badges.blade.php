<div>
    {{ $limit }}
    <div class="flex flex-wrap gap-1 transition-all duration-300 ease-in-out">
        @if(is_array($getState()) || is_object($getState()))
            @foreach ($getState() as $item)
                @if ($loop->index < $limit)
                    <x-filament::badge size=" sm">
                        {{ $item }}
                    </x-filament::badge>
                @endif                
            @endforeach
        @endif
    </div>

    @if (count($getState()) > $limit)
        <button 
            wire:click="toggleExpand"
            class="text-xs font-medium text-primary-600 hover:text-primary-500 hover:underline mt-1 focus:outline-none"
            x-text="expanded ? 'Show less' : 'Show more'">
        </button>
    @endif
</div>