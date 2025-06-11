<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250513120449 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add measurement_type, unit_measurement, and unit_symbol columns to the input table with default empty strings.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE input ADD measurement_type VARCHAR(255) DEFAULT \'\' NOT NULL, ADD unit_measurement VARCHAR(255) DEFAULT \'\' NOT NULL, ADD unit_symbol VARCHAR(255) DEFAULT \'\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE input DROP measurement_type');
        $this->addSql('ALTER TABLE input DROP unit_measurement');
        $this->addSql('ALTER TABLE input DROP unit_symbol');

    }
}
