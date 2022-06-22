<?php

declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Tests\Application\AbstractPeonApplicationTestCase;
use Peon\Tests\DataFixtures\DataFixtures;

final class UserSettingsControllerTest extends AbstractPeonApplicationTestCase
{
    public function testPageIsProtectedWithLogin(): void
    {
        $client = self::createClient();

        $client->request('GET', "/user-settings");

        self::assertResponseRedirects('http://localhost/login');
    }


    public function testPasswordCanBeChanged(): void
    {
        $client = self::createClient();

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $crawler = $client->request('GET', '/user-settings');

        $form = $crawler->selectButton('submit')->form();
        $newPassword = 'new';

        $client->submit($form, [
            $form->getName() . '[oldPassword]' => DataFixtures::USER_PASSWORD,
            $form->getName() . '[newPassword][first]' => $newPassword,
            $form->getName() . '[newPassword][second]' => $newPassword,
        ]);

        self::assertResponseRedirects('/');
    }


    public function testPasswordsMismatch(): void
    {
        $client = self::createClient();

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $crawler = $client->request('GET', '/user-settings');

        $form = $crawler->selectButton('submit')->form();

        $client->submit($form, [
            $form->getName() . '[oldPassword]' => DataFixtures::USER_PASSWORD,
            $form->getName() . '[newPassword][first]' => 'a',
            $form->getName() . '[newPassword][second]' => 'b',
        ]);

        self::assertResponseIsUnprocessable();
    }


    public function testOldPasswordMustBeCorrect(): void
    {
        $client = self::createClient();

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $crawler = $client->request('GET', '/user-settings');

        $form = $crawler->selectButton('submit')->form();
        $newPassword = 'new';

        $client->submit($form, [
            $form->getName() . '[oldPassword]' => 'something-totally-random',
            $form->getName() . '[newPassword][first]' => $newPassword,
            $form->getName() . '[newPassword][second]' => $newPassword,
        ]);

        self::assertResponseIsUnprocessable();
    }
}
