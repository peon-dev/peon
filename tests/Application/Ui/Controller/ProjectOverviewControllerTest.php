<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Tests\Application\AbstractPeonApplicationTestCase;
use Peon\Tests\DataFixtures\DataFixtures;
use Ramsey\Uuid\Uuid;

final class ProjectOverviewControllerTest extends AbstractPeonApplicationTestCase
{
    public function testPageIsProtectedWithLogin(): void
    {
        $client = self::createClient();

        $randomProjectId = Uuid::uuid4()->toString();

        $client->request('GET', "/projects/$randomProjectId");

        self::assertResponseRedirects('http://localhost/login');
    }


    public function testCanNotAccessForeignUserProject(): void
    {
        $client = self::createClient();
        $anotherUserProjectId = DataFixtures::USER_2_PROJECT_1_ID;

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/projects/$anotherUserProjectId");

        // Intentionally 404, and not 401/403
        self::assertResponseStatusCodeSame(404);
    }


    public function testNonExistingProjectWillShow404(): void
    {
        $client = self::createClient();
        $projectId = '00000000-0000-0000-0000-000000000000';

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/projects/$projectId");

        self::assertResponseStatusCodeSame(404);
    }


    /**
     * @dataProvider provideTestPageCanBeRenderedData
     */
    public function testPageCanBeRendered(string $projectId): void
    {
        $client = self::createClient();

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/projects/$projectId");

        self::assertResponseIsSuccessful();
    }


    /**
     * @return \Generator<array<string>>
     */
    public function provideTestPageCanBeRenderedData(): \Generator
    {
        yield [DataFixtures::USER_1_PROJECT_1_ID];
        yield [DataFixtures::USER_1_PROJECT_2_ID];
    }
}
