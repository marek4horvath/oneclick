<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250514044003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds unit_symbol column to product_input and product_step tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE product_input ADD unit_symbol VARCHAR(255) DEFAULT '' NOT NULL");
        $this->addSql("ALTER TABLE product_step ADD unit_symbol VARCHAR(255) DEFAULT '' NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input DROP unit_symbol');
        $this->addSql('ALTER TABLE product_step DROP unit_symbol');
    }
}
