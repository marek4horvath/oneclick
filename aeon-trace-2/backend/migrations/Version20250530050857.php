<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250530050857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a non-nullable "unit_symbol" column with default empty string to the "product_input_history" table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input_history ADD unit_symbol VARCHAR(255) DEFAULT \'\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input_history DROP unit_symbol');
    }
}
