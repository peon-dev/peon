<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\Scheduler;

use Cron\CronExpression;
use DateTimeImmutable;
use Generator;
use Lcobucci\Clock\FrozenClock;
use Peon\Domain\Scheduler\ShouldSchedule;
use PHPUnit\Framework\TestCase;

final class ShouldScheduleTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('provideTestCronExpressionNowData')]
    public function testCronExpressionNow(DateTimeImmutable $now, CronExpression $cronExpression, DateTimeImmutable|null $lastTimeSchedule, bool $expected): void
    {
        $clock = new FrozenClock($now);

        $shouldSchedule = new ShouldSchedule($clock);

        self::assertSame($expected, $shouldSchedule->cronExpressionNow($cronExpression, $lastTimeSchedule));
    }


    /**
     * @return Generator<array{DateTimeImmutable, CronExpression, DateTimeImmutable|null, bool}>
     */
    public static function provideTestCronExpressionNowData(): Generator
    {
        // Every hour at 0 minutes
        $cronExpression = new CronExpression('0 * * * *');
        $now = new DateTimeImmutable('2000-10-10 10:10:00');
        $nowMinus10Minutes = new DateTimeImmutable('2000-10-10 10:00:00');
        $nowMinus1Hour = new DateTimeImmutable('2000-10-10 9:10:00');

        yield [
            $now,
            $cronExpression,
            null,
            true,
        ];

        yield [
            $now,
            $cronExpression,
            $nowMinus10Minutes,
            false,
        ];

        yield [
            $now,
            $cronExpression,
            $nowMinus1Hour,
            true,
        ];
    }
}
