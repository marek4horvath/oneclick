<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250513113602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add `unit_symbol` column to the `step` table with a default empty string value.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE step ADD unit_symbol VARCHAR(255) DEFAULT \'\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE step DROP unit_symbol');
    }
}
