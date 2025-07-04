<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250602103711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the "updatable" boolean column to the "input" table to indicate if the input can be updated after initial entry.';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('ALTER TABLE input ADD updatable TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {

        $this->addSql('ALTER TABLE input DROP updatable');
    }
}
