<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241113211257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added locked to the product step.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_step ADD IF NOT EXISTS locked TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_step DROP IF EXISTS locked');
    }
}
