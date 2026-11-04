<?php

namespace App\MessageHandler;

use App\Message\SendDailySummary;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendDailySummaryHandler
{
    public function __invoke(SendDailySummary $message): void
    {
        error_log(sprintf('%s handler executed.', $message::class));
    }
}