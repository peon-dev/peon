<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Tests\Application\AbstractPeonApplicationTestCase;
use Peon\Tests\DataFixtures\DataFixtures;

final class RerunJobControllerTest extends AbstractPeonApplicationTestCase
{
    public function testPageIsProtectedWithLogin(): void
    {
        $client = self::createClient();

        $jobId = DataFixtures::USER_1_JOB_1_ID;

        $client->request('GET', "/rerun-job/$jobId");

        self::assertResponseRedirects('http://localhost/login');
    }


    public function testNonExistingTaskWillShow404(): void
    {
        $client = self::createClient();
        $jobId = '00000000-0000-0000-0000-000000000000';

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/rerun-job/$jobId");

        self::assertResponseStatusCodeSame(404);
    }


    public function testCanNotRerunJobOfForeignProject(): void
    {
        $client = self::createClient();
        $anotherUserJobId = DataFixtures::USER_2_JOB_1_ID;

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/rerun-job/$anotherUserJobId");

        // Intentionally 404, and not 401/403
        self::assertResponseStatusCodeSame(404);
    }


    /**
     * @return \Generator<array{string, string}>
     */
    public static function provideRoutesRedirectData(): \Generator
    {
        $jobId = DataFixtures::USER_1_JOB_1_ID;

        yield ["/rerun-job/$jobId", "/job/"];

        yield ["/projects/rerun-job/$jobId", "/projects/"];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideRoutesRedirectData')]
    public function testRedirectAfterSuccessfulRerunSchedule(string $requestUrl, string $expectedRedirectUrlStartsWith): void
    {
        $client = self::createClient();

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', $requestUrl);

        self::assertResponseRedirects();

        $currentLocation = $client->getResponse()->headers->get('Location');
        assert(is_string($currentLocation));

        self::assertTrue(str_starts_with($currentLocation, $expectedRedirectUrlStartsWith));
    }
}
