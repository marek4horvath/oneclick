<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240911114422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove Unused name field for input.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE input DROP name');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE input ADD name VARCHAR(255) DEFAULT \'\' NOT NULL');
    }
}
