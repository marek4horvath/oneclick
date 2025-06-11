<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241122112733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a "user" column to the "dpp".';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('ALTER TABLE dpp ADD IF NOT EXISTS user VARCHAR(255) DEFAULT \'\' NOT NULL');
    }

    public function down(Schema $schema): void
    {

        $this->addSql('ALTER TABLE dpp DROP IF EXISTS user');
    }
}
