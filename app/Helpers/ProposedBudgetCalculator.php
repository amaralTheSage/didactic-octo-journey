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
                ($inf['reels_price'] ?? 0) * $nReels,
                ($inf['stories_price'] ?? 0) * $nStories,
                ($inf['carrousel_price'] ?? 0) * $nCarrousels,
            ];
            $allPrices = array_merge($allPrices, $prices);
            $totalSum += array_sum($prices);
        }

        return [
            'min' => min($allPrices),
            'max' => $totalSum,
        ];
    }
}
