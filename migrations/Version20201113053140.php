<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201113053140 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE comment DROP COLUMN state');
        $this->addSql('ALTER TABLE comment ADD COLUMN state VARCHAR(255)');
        $this->addSql('UPDATE comment SET state=\'published\'');
        $this->addSql('ALTER TABLE comment MODIFY state VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE comment DROP state');
    }
}
