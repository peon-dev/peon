<?php

declare(strict_types=1);

namespace PHPMate\Tests\Application\Ui\Controller;

use PHPMate\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class JobDetailControllerTest extends WebTestCase
{
    // TODO: test 404

    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();
        $jobId = DataFixtures::JOB_1_ID;

        $client->request('GET', "/job/$jobId");

        self::assertResponseIsSuccessful();

        // TODO: maybe add more assertions later
    }
}