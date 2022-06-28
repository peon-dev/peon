<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Lcobucci\Clock\FrozenClock;
use Peon\Domain\Worker\Exception\WorkerNotReportedAnythingYet;
use Peon\Domain\Worker\Value\WorkerId;
use Peon\Domain\Worker\WorkerStatus;
use Peon\Domain\Worker\WorkerStatusesCollection;
use Peon\UseCase\ReportWorkerLiveness;
use Peon\UseCase\ReportWorkerLivenessHandler;
use PHPUnit\Framework\TestCase;

final class ReportWorkerLivenessHandlerTest extends TestCase
{
    public function testNewStatusWillBeCreatedWhenNotReportedYet(): void
    {
        $workerId = new WorkerId('1');

        $clock = FrozenClock::fromUTC();
        $workerStatusesCollectionMock = $this->createMock(WorkerStatusesCollection::class);
        $workerStatusesCollectionMock->expects(self::once())
            ->method('get')
            ->willThrowException(new WorkerNotReportedAnythingYet());

        $workerStatusesCollectionMock->expects(self::once())
            ->method('save')
            ->with(
                $this->callback(
                    static function(WorkerStatus $workerStatus) use ($workerId): bool {
                        return $workerStatus->workerId->id === $workerId->id;
                    }
                )
            );

        $handler = new ReportWorkerLivenessHandler($clock, $workerStatusesCollectionMock);
        $handler->__invoke(
            new ReportWorkerLiveness($workerId)
        );
    }


    public function testWillUpdateExistingStatusReport(): void
    {
        $clock = FrozenClock::fromUTC();
        $workerId = new WorkerId('1');

        $workerStatusProxy = $this->createTestProxy(WorkerStatus::class, [
           $workerId,
           $clock,
        ]);

        $workerStatusProxy->expects(self::once())
            ->method('updateLiveness')
            ->with($clock);

        $workerStatusesCollectionMock = $this->createMock(WorkerStatusesCollection::class);
        $workerStatusesCollectionMock->expects(self::once())
            ->method('get')
            ->willReturn($workerStatusProxy);

        $workerStatusesCollectionMock->expects(self::once())
            ->method('save')
            ->with($workerStatusProxy);

        $handler = new ReportWorkerLivenessHandler($clock, $workerStatusesCollectionMock);
        $handler->__invoke(
            new ReportWorkerLiveness($workerId)
        );
    }
}
