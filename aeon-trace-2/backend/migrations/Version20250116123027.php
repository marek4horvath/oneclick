<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250116123027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds measurement_type and unit_measurement columns to the step table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE step ADD IF NOT EXISTS measurement_type VARCHAR(255) DEFAULT \'\' NOT NULL, ADD IF NOT EXISTS unit_measurement VARCHAR(255) DEFAULT \'\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE step DROP IF EXISTS measurement_type, DROP IF EXISTS unit_measurement');
    }
}
