<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Job\JobsCollection;
use Peon\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RunRecipeControllerTest extends WebTestCase
{
    public function testNonExistingProjectWillShow404(): void
    {
        $client = self::createClient();
        $projectId = '00000000-0000-0000-0000-000000000000';
        $recipeName = RecipeName::UNUSED_PRIVATE_METHODS;

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
        $projectId = DataFixtures::PROJECT_1_ID;

        $client->request('GET', "/projects/$projectId/run-recipe/$recipeName->value");

        self::assertResponseRedirects("/projects/$projectId");

        self::assertCount(1 + $jobsCountBeforeScenario, $jobsCollection->all());
    }
}
