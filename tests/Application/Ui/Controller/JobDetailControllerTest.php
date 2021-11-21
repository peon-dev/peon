<?php

declare(strict_types=1);

namespace PHPMate\Tests\Application\Ui\Controller;

use PHPMate\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class JobDetailControllerTest extends WebTestCase
{
    public function testNonExistingJobWillShow404(): void
    {
        $client = self::createClient();
        $jobId = '00000000-0000-0000-0000-000000000000';

        $client->request('GET', "/job/$jobId");

        self::assertResponseStatusCodeSame(404);
    }


    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();
        $jobId = DataFixtures::JOB_1_ID;

        $client->request('GET', "/job/$jobId");

        self::assertResponseIsSuccessful();

        // TODO: maybe add more assertions later
    }
}
