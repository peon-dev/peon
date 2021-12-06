<?php
declare(strict_types=1);

namespace PHPMate\Tests\Application\Ui\Controller;

use PHPMate\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProjectSettingsControllerTest extends WebTestCase
{
    public function testNonExistingProjectWillShow404(): void
    {
        $client = self::createClient();
        $projectId = '00000000-0000-0000-0000-000000000000';

        $client->request('GET', "/projects/$projectId/settings");

        self::assertResponseStatusCodeSame(404);
    }


    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();
        $projectId = DataFixtures::PROJECT_ID;

        $client->request('GET', "/projects/$projectId/settings");

        self::assertResponseIsSuccessful();
    }
}
