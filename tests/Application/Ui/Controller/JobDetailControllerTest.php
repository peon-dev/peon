<?php

declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Tests\DataFixtures\DataFixtures;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class JobDetailControllerTest extends WebTestCase
{
    public function testPageIsProtectedWithLogin(): void
    {
        $client = self::createClient();

        $randomJobId = Uuid::uuid4()->toString();

        $client->request('GET', "/job/$randomJobId");

        self::assertResponseRedirects('http://localhost/login');
    }


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
    }
}
