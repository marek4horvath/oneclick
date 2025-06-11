<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250407123839 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add updated at to dpp.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp ADD updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp DROP updated_at');
    }
}
