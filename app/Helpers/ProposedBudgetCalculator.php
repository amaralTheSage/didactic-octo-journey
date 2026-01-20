<?php

namespace App\Helpers;

class ProposedBudgetCalculator
{
    public static function calculateInfluencerBudgetRange(
        int $nReels,
        int $nStories,
        int $nCarrousels,
        array $influencers
    ): array {
        if (empty($influencers)) {
            return ['min' => 0, 'max' => 0];
        }

        $allPrices = [];
        $totalSum = 0;

        foreach ($influencers as $inf) {
            $prices = [
                (float) ($inf['reels_price'] ?? 0) * $nReels,
                (float) ($inf['stories_price'] ?? 0) * $nStories,
                (float) ($inf['carrousel_price'] ?? 0) * $nCarrousels,
            ];
            $allPrices = array_merge($allPrices, $prices);
            $totalSum += array_sum($prices);
        }

        // Filtra valores zero para o mínimo
        $nonZeroPrices = array_filter($allPrices, fn($price) => $price > 0);

        return [
            'min' => !empty($nonZeroPrices) ? min($nonZeroPrices) : 0,
            'max' => $totalSum,
        ];
    }
}
