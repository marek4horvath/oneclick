<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241107092109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added ongoing DPP flag.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp ADD IF NOT EXISTS ongoing_dpp TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp DROP IF EXISTS ongoing_dpp');
    }
}
