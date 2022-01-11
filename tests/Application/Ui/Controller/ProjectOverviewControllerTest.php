<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProjectOverviewControllerTest extends WebTestCase
{
    public function testNonExistingProjectWillShow404(): void
    {
        $client = self::createClient();
        $projectId = '00000000-0000-0000-0000-000000000000';

        $client->request('GET', "/projects/$projectId");

        self::assertResponseStatusCodeSame(404);
    }


    /**
     * @dataProvider provideTestPageCanBeRenderedData
     */
    public function testPageCanBeRendered(string $projectId): void
    {
        $client = self::createClient();

        $client->request('GET', "/projects/$projectId");

        self::assertResponseIsSuccessful();
    }


    /**
     * @return \Generator<array<string>>
     */
    public function provideTestPageCanBeRenderedData(): \Generator
    {
        yield [DataFixtures::PROJECT_1_ID];
        yield [DataFixtures::PROJECT_2_ID];
    }
}
