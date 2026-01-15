<div class="space-y-6">
    @php
        use Illuminate\Support\HtmlString;

        $numericFields = [
            'commission_cut',
            'proposed_agency_cut',
            'n_reels',
            'n_stories',
            'n_carrousels',
            'reels_price',
            'stories_price',
            'carrousel_price',
        ];

        $moneyFields = [
            'budget',
            'reels_price',
            'stories_price',
            'carrousel_price',
        ];

        function isNumericChange(string $field, array $numericFields): bool
        {
            return in_array($field, $numericFields, true);
        }

        function formatValue($value, string $field, array $moneyFields, array $numericFields): string
        {
            if ($value === null || $value === '—') {
                return '—';
            }

            if (in_array($field, $moneyFields, true)) {
                return app(\App\Helpers\BRLFormatter::class)((float) $value);
            }

            if (in_array($field, $numericFields, true) && !in_array($field, $moneyFields, true)) {
                $value = (int) $value;
            }

            if ($field === 'commission_cut' || $field === 'proposed_agency_cut') {
                return $value . '%';
            }

            return (string) $value;
        }

        function differenceIndicator(
            float $difference,
            string $field,
            array $moneyFields,
            array $numericFields
        ): ?HtmlString {
            if ($difference === 0.0) {
                return null;
            }

            $arrow = $difference > 0
                ? '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up mb-0.5"><path d="m5 12 7-7 7 7"/><path d="M12 19V5"/></svg>'
                : '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-down mb-0.5"><path d="M12 5v14"/><path d="m19 12-7 7-7-7"/></svg>';

            $formatted = formatValue(
                abs($difference),
                $field,
                $moneyFields,
                $numericFields
            );

            return new HtmlString(
                '<span class="text-secondary text-xs flex items-end gap-1">'
                . $formatted
                . $arrow .
                '</span>'
            );
        }
    @endphp

    <div class="space-y-6">
        @if ($logs->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center text-muted-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" class="mb-3 h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 7h8M8 11h8m-6 4h6M5 21h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2Z" />
                </svg>

                <p class="text-sm font-medium text-foreground">Nenhuma alteração registrada</p>
                <p class="text-xs">As edições da proposta aparecerão aqui quando ocorrerem.</p>
            </div>
        @else
            @foreach ($logs as $log)
                <div class="flex gap-4 pb-3">
                    <img src="{{ $log->user->avatar_url }}" class="h-8 w-8 rounded-full" />

                    <div class="flex-1 space-y-1">
                        <div class="flex justify-between">
                            <span class="text-sm font-semibold text-foreground">{{ $log->user->name }}</span>
                            <span class="text-xs text-muted-foreground">
                                {{ $log->created_at->translatedFormat('d \\d\\e F \\d\\e Y \\à\\s H:i') }}
                            </span>
                        </div>

                        {{-- Approval --}}
                        @if ($approval = $log->changes['approval'] ?? null)
                            <div class="text-xs text-muted-foreground">
                                {{ match ($approval['role']) {
                                    'company' => 'A empresa',
                                    'agency' => 'A agência',
                                    'influencer' => 'O influenciador',
                                    default => 'O usuário',
                                } }}

                                <span class="{{ $approval['to'] === 'approved' ? 'text-green-500' : 'text-red-400' }}">
                                    {{ $approval['to'] === 'approved' ? 'aprovou' : 'rejeitou' }}
                                </span>
                                a proposta
                            </div>
                        @endif

                        {{-- Proposal changes --}}
                        @foreach ($log->changes['proposal'] ?? [] as $field => $change)
                            @php
                                if (!isNumericChange($field, $numericFields)) continue;

                                $from = $change['from'] ?? '—';
                                $to = $change['to'] ?? '—';

                                $diff = differenceIndicator(
                                    (float) $to - (float) $from,
                                    $field,
                                    $moneyFields,
                                    $numericFields
                                );
                            @endphp

                            <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                <span>{{ __('proposal_fields.' . $field) }}</span>
                                <span>{{ formatValue($from, $field, $moneyFields, $numericFields) }}</span>
                                <span>→</span>
                                <span class="font-medium text-foreground">
                                    {{ formatValue($to, $field, $moneyFields, $numericFields) }}
                                </span>
                                {!! $diff !!}
                            </div>
                        @endforeach

                        {{-- Influencer changes --}}
                        @foreach ($log->changes['influencers'] ?? [] as $payload)
                            @php
                                $name = $payload['name'] ?? '—';
                                $isRemoved = $payload['removed'] ?? false;
                                $isAdded = $payload['added'] ?? false;

                                $fieldSource = $isRemoved
                                    ? ($payload['from'] ?? [])
                                    : ($isAdded
                                        ? ($payload['to'] ?? [])
                                        : ($payload['changes'] ?? []));
                            @endphp

                            <div class="pt-1 space-y-1">
                                <div class="text-xs font-medium text-foreground flex gap-2">
                                    {{ $name }}
                                </div>

                                @foreach ($fieldSource as $field => $data)
                                    @php
                                        if (!isNumericChange($field, $numericFields)) continue;

                                        $from = $isRemoved ? $data : ($data['from'] ?? '—');
                                        $to = $isAdded ? $data : ($data['to'] ?? '—');

                                        $diff = (!$isRemoved && !$isAdded)
                                            ? differenceIndicator(
                                                (float) $to - (float) $from,
                                                $field,
                                                $moneyFields,
                                                $numericFields
                                            )
                                            : null;
                                    @endphp

                                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                        <span>{{ __('proposal_fields.' . $field) }}:</span>

                                        @if (!$isAdded)
                                            <span>{{ formatValue($from, $field, $moneyFields, $numericFields) }}</span>
                                        @endif

                                        @if (!$isRemoved && !$isAdded)
                                            <span>→</span>
                                        @endif

                                        @if (!$isRemoved)
                                            <span class="font-medium text-foreground">
                                                {{ formatValue($to, $field, $moneyFields, $numericFields) }}
                                            </span>
                                        @endif

                                        {!! $diff !!}
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                <hr class="w-[20%] mx-auto bg-border h-px">
            @endforeach
        @endif
    </div>
</div>
