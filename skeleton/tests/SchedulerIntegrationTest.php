<?php

namespace App\Tests;

use App\Message\SendDailySummary;
use App\MessageHandler\SendDailySummaryHandler;
use App\Scheduler\MainSchedule;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Message\RedispatchMessage;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Scheduler\Generator\MessageContext;

#[CoversClass(MainSchedule::class)]
final class SchedulerIntegrationTest extends KernelTestCase
{
    public function testDailyScheduleRunsAtEightUtcAndRedispatchesToAsync(): void
    {
        $schedule = static::getContainer()->get(MainSchedule::class)->getSchedule();
        $recurringMessages = $schedule->getRecurringMessages();

        self::assertCount(1, $recurringMessages);

        $recurringMessage = $recurringMessages[0];
        $trigger = $recurringMessage->getTrigger();
        $beforeRun = new \DateTimeImmutable('2026-11-04 07:59:00', new \DateTimeZone('UTC'));
        $nextRun = $trigger->getNextRunDate($beforeRun);

        self::assertNotNull($nextRun);
        self::assertSame('2026-11-04 08:00:00', $nextRun->format('Y-m-d H:i:s'));

        $messages = iterator_to_array($recurringMessage->getMessages(new MessageContext(
            'default',
            $recurringMessage->getId(),
            $trigger,
            $beforeRun,
            $nextRun,
        )));

        self::assertCount(1, $messages);
        self::assertInstanceOf(RedispatchMessage::class, $messages[0]);
        self::assertInstanceOf(SendDailySummary::class, $messages[0]->envelope);
        self::assertSame('async', $messages[0]->transportNames);
    }

    public function testRedispatchedMessageIsQueuedAndHandled(): void
    {
        $container = static::getContainer();
        $bus = $container->get('messenger.default_bus');

        $bus->dispatch(new RedispatchMessage(new SendDailySummary(), 'async'));

        $transport = $container->get('messenger.transport.async');
        $queuedMessages = iterator_to_array($transport->get());

        self::assertCount(1, $queuedMessages);

        $handledEnvelope = $bus->dispatch(
            $queuedMessages[0]->getMessage(),
            [new ReceivedStamp('async')],
        );
        $handledStamp = $handledEnvelope->last(HandledStamp::class);

        self::assertNotNull($handledStamp);
        self::assertSame(SendDailySummaryHandler::class.'::__invoke', $handledStamp->getHandlerName());
    }
}