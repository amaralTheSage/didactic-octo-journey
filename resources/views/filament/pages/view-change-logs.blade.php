<div class="space-y-6">
    @php
        $numericFields = [
                'budget',
                'proposed_agency_cut',
                'n_reels',
                'n_stories',
                'n_carrousels',
                'reels_price',
                'stories_price',
                'carrousel_price',
            ];

        function isNumericChange(string $field, array $numericFields): bool {
            return in_array($field, $numericFields, true);
        }

        function differenceIndicator(float $difference): ?Illuminate\Support\HtmlString 
        {
            if ($difference === 0.0) {
                return null;
            }

            $color = 'text-secondary';

            $arrow =  $difference > 0
                ? new Illuminate\Support\HtmlString(
                    '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up mb-0.5"><path d="m5 12 7-7 7 7"/><path d="M12 19V5"/></svg>'
                )
                : new Illuminate\Support\HtmlString(
                    '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-down mb-0.5"><path d="M12 5v14"/><path d="m19 12-7 7-7-7"/></svg>'
                );

           return new Illuminate\Support\HtmlString(
                    '<span class="' . $color . ' text-xs flex items-end gap-1">'
                        . number_format(abs($difference), 2) .
                        $arrow .
                    '</span>'
                );
        }
    @endphp

    <div class="space-y-6">
        @foreach ($logs as $log)
            <div class="flex gap-4 pb-3">
                {{-- Avatar --}}
                <div class="shrink-0">
                    <img
                        src="{{ $log->user->avatar_url }}"
                        class="h-8 w-8 rounded-full"
                    />
                </div>

                {{-- Content --}}
                <div class="flex-1 space-y-1 ">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-foreground">
                            <span class="font-semibold">{{ $log->user->name }}</span>
                            editou a proposta
                        </div>
                        
                        <div class="text-xs text-muted-foreground">
                            {{ $log->created_at->translatedFormat('d \\d\\e F \\d\\e Y \\à\\s H:i') }}
                        </div>
                    </div>

                    {{-- Proposal Changes --}}
                    @foreach (($log->changes['proposal'] ?? []) as $field => $change)
                        @php
                            $isNumeric = isNumericChange($field, $numericFields);

                            if ($isNumeric) {
                                $difference = (float) $change['to'] - (float) $change['from'];
                                $arr = differenceIndicator($difference);
                            }                                   
                        @endphp

                        <div class="flex items-center gap-2 text-xs text-muted-foreground">
                            <span class="capitalize">
                                {{ __('proposal_fields.' . $field) }}
                            </span>
                            <span>{{ $change['from'] ?? '—' }}</span>
                            <span>→</span>
                            <span class="text-foreground font-medium">
                                {{ $change['to'] ?? '—' }}
                            </span>

                            @if ($isNumeric)
                                {{ $arr }}
                            @endif
                        </div>
                    @endforeach

                    {{-- Influencer Changes --}}
            @foreach (($log->changes['influencers'] ?? []) as $infId => $payload)
                @php
                    $name = $payload['name'] ?? '—';
                    $isRemoved = $payload['removed'] ?? false;
                    $isAdded = $payload['added'] ?? false;
                    
                    // Define which array to loop through for fields
                    // If it's a standard edit, use the 'changes' sub-array
                    // If it's added/removed, use the 'to' or 'from' arrays respectively
                    $fieldSource = [];
                    if ($isRemoved) {
                        $fieldSource = $payload['from'] ?? [];
                    } elseif ($isAdded) {
                        $fieldSource = $payload['to'] ?? [];
                    } else {
                        $fieldSource = $payload['changes'] ?? [];
                    }
                @endphp

                <div class=" space-y-1 pt-1"> 
                    <div class="flex items-center gap-2 text-sm font-medium text-foreground">
                        {{ $name }}
                        @if ($isRemoved)
                            <span class="rounded-md bg-danger-500/10 px-2 py-0.5 text-xs font-medium text-danger-600">Removido</span>
                        @elseif ($isAdded)
                            <span class="rounded-md bg-success-500/10 px-2 py-0.5 text-xs font-medium text-success-600">Adicionado</span>
                        @endif
                    </div>

                    @foreach ($fieldSource as $field => $data)
                        @php
                            if (!isNumericChange($field, $numericFields)) continue;

                            $from = null;
                            $to = null;
                            $diffIndicator = null;

                            if ($isRemoved) {
                                $from = $data;
                            } elseif ($isAdded) {
                                $to = $data;
                            } else {
                                // Standard edit: $data is an array with 'to' and 'from'
                                $from = $data['from'] ?? '—';
                                $to = $data['to'] ?? '—';
                                $diffIndicator = differenceIndicator((float)$to - (float)$from);
                            }
                        @endphp

                        <div @class([
                            "flex items-center gap-2 text-xs",
                            "text-muted-foreground line-through" => $isRemoved,
                            "text-success-600" => $isAdded,
                            "text-muted-foreground" => !$isRemoved && !$isAdded
                        ])>
                            <span class="capitalize">{{ __('proposal_fields.' . $field) }}:</span>
                            
                            @if(!$isAdded) <span>{{ $from }}</span> @endif
                            @if(!$isRemoved && !$isAdded) <span>→</span> @endif
                            @if(!$isRemoved) <span class="text-foreground font-medium">{{ $to }}</span> @endif
                            
                            {!! $diffIndicator !!}
                        </div>
                    @endforeach
                </div>
            @endforeach
                </div>
            </div>
            
            <hr class="bg-border w-[20%] mx-auto">
        @endforeach
    </div>
</div>