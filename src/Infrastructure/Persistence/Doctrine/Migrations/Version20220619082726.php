<?php

declare(strict_types=1);

namespace Peon\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\UseCase\RegisterUser;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220619082726 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private null|string $userId = null;


    public function getDescription(): string
    {
        return '';
    }

    public function preUp(Schema $schema): void
    {
        $commandBus = $this->container->get(CommandBus::class);
        assert($commandBus instanceof CommandBus);

        // Create first default user
        $commandBus->dispatch(
            new RegisterUser('peon', 'peon'),
        );

        // Get the created user id
        $resultSet = $this->connection->executeQuery('SELECT user_id FROM users');
        $userId = $resultSet->fetchOne();
        assert(is_string($userId));
        $this->userId = $userId;
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project ADD owner_user_id UUID DEFAULT NULL');
        $this->addSql("UPDATE project SET owner_user_id ='$this->userId'");

        $this->addSql('COMMENT ON COLUMN project.owner_user_id IS \'(DC2Type:user_id)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE project DROP owner_user_id');
    }
}
