<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Job\JobsCollection;
use Peon\Tests\Application\AbstractPeonApplicationTestCase;
use Peon\Tests\DataFixtures\DataFixtures;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RunRecipeControllerTest extends AbstractPeonApplicationTestCase
{
    public function testPageIsProtectedWithLogin(): void
    {
        $client = self::createClient();

        $randomProjectId = Uuid::uuid4()->toString();
        $recipeName = RecipeName::TYPED_PROPERTIES;

        $client->request('GET', "/projects/$randomProjectId/run-recipe/$recipeName->value");

        self::assertResponseRedirects('/login');
    }


    public function testCanNotRunRecipeForForeignProject(): void
    {
        $client = self::createClient();
        $anotherUserProjectId = DataFixtures::USER_2_PROJECT_1_ID;
        $recipeName = RecipeName::UNUSED_PRIVATE_METHODS;

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/projects/$anotherUserProjectId/run-recipe/$recipeName->value");

        // Intentionally 404, and not 401/403
        self::assertResponseStatusCodeSame(404);
    }


    public function testNonExistingProjectWillShow404(): void
    {
        $client = self::createClient();
        $projectId = '00000000-0000-0000-0000-000000000000';
        $recipeName = RecipeName::UNUSED_PRIVATE_METHODS;

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/projects/$projectId/run-recipe/$recipeName->value");

        self::assertResponseStatusCodeSame(404);
    }


    public function testTaskCanBeRunAndJobWillBeCreated(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $jobsCollection = $container->get(JobsCollection::class);
        $jobsCountBeforeScenario = count($jobsCollection->all());
        $recipeName = RecipeName::UNUSED_PRIVATE_METHODS;
        $projectId = DataFixtures::USER_1_PROJECT_1_ID;

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/projects/$projectId/run-recipe/$recipeName->value");

        self::assertResponseRedirects("/projects/$projectId");

        self::assertCount(1 + $jobsCountBeforeScenario, $jobsCollection->all());
    }
}
