<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241108111350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update foreign key constraints and add type column to product_input table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE input DROP FOREIGN KEY IF EXISTS  FK_D82832D7A7C218ED');
        $this->addSql('ALTER TABLE input ADD CONSTRAINT FK_D82832D7A7C218ED FOREIGN KEY IF NOT EXISTS (logistics_template_id) REFERENCES logistics_template (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_input ADD IF NOT EXISTS type VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_input DROP IF EXISTS type');
        $this->addSql('ALTER TABLE input DROP FOREIGN KEY IF EXISTS  FK_D82832D7A7C218ED');
        $this->addSql('ALTER TABLE input ADD CONSTRAINT FK_D82832D7A7C218ED FOREIGN KEY (logistics_template_id) REFERENCES logistics_template (id)');
    }
}
