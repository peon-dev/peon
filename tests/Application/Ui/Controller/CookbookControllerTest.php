<?php
declare(strict_types=1);

namespace PHPMate\Tests\Application\Ui\Controller;

use PHPMate\Infrastructure\Cookbook\StaticRecipesCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CookbookControllerTest extends WebTestCase
{
    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/cookbook');

        self::assertResponseIsSuccessful();

        $recipesInCollectionCount = count((new StaticRecipesCollection())->all());
        self::assertCount($recipesInCollectionCount, $crawler->filter('.dashboard-projects .col'));
    }
}
