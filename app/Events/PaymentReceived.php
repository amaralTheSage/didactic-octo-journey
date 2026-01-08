<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived implements ShouldBroadcast
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

    public function broadcastAs(): string
    {
        return 'payment.received';
    }
}
