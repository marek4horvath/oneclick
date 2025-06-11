<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250425084227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add timestamp path to database table of dpps and product steps.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp ADD timestamp_path VARCHAR(255) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE product_step ADD timestamp_path VARCHAR(255) DEFAULT \'\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp DROP timestamp_path');
        $this->addSql('ALTER TABLE product_step DROP timestamp_path');
    }
}
