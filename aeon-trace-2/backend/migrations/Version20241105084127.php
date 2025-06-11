<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241105084127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration to drop type_of_process if exists and add have_dpp to product_template';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE node DROP IF EXISTS type_of_process');
        $this->addSql('ALTER TABLE product_template ADD IF NOT EXISTS have_dpp TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_template DROP IF EXISTS have_dpp');
        $this->addSql('ALTER TABLE node ADD IF NOT EXISTS type_of_process VARCHAR(255) NOT NULL');
    }
}
