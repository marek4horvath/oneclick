<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241105144810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add type_of_process to NODE table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE node ADD IF NOT EXISTS type_of_process VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE node DROP IF EXISTS type_of_process');
    }
}
