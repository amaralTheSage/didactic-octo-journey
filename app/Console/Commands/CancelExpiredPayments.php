<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelExpiredPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:cancel-expired';
    protected $description = 'Cancel expired pending payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::alert('Cancelando pagamentos expirados');

        $count = Payment::whereStatus(PaymentStatus::PENDING)
            ->where('expires_at', '<=', now())
            ->update([
                'status' => PaymentStatus::CANCELLED,
            ]);

        $this->info("{$count} pagamentos foram cancelados.");

        return self::SUCCESS;
    }
}
