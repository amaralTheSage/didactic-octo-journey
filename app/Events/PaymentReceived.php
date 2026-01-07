<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $abacateId,
        public string $status,
        public int $campaignId
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('payments');
    }
}
