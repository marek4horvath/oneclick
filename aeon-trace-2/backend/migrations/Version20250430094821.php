<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250430094821 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add time stamp path field to logistics.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE logistics ADD timestamp_path VARCHAR(255) DEFAULT \'\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE logistics DROP timestamp_path');
    }
}
