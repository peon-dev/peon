<?php

declare(strict_types=1);

namespace Peon\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220628050238 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE worker_status (worker_id VARCHAR(255) NOT NULL, last_seen_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(worker_id))');
        $this->addSql('COMMENT ON COLUMN worker_status.worker_id IS \'(DC2Type:worker_id)\'');
        $this->addSql('COMMENT ON COLUMN worker_status.last_seen_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE worker_status');
    }
}
