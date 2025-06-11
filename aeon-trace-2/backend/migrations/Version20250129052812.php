<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250129052812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE node ADD IF NOT EXISTS node_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE84545D1A451 FOREIGN KEY IF NOT EXISTS (node_template_id) REFERENCES product_template (id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_857FE84545D1A451 ON node (node_template_id)');
        $this->addSql('ALTER TABLE step DROP FOREIGN KEY IF EXISTS FK_43B9FE3C1FA6ADA');
        $this->addSql('DROP INDEX IDX_43B9FE3C1FA6ADA ON step');
        $this->addSql('ALTER TABLE step DROP IF EXISTS parent_step_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE step ADD IF NOT EXISTS parent_step_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE step ADD CONSTRAINT FK_43B9FE3C1FA6ADA FOREIGN KEY IF NOT EXISTS (parent_step_id) REFERENCES step (id)');
        $this->addSql('CREATE INDEX IDX_43B9FE3C1FA6ADA ON step (parent_step_id)');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY IF EXISTS FK_857FE84545D1A451');
        $this->addSql('DROP INDEX IDX_857FE84545D1A451 ON node');
        $this->addSql('ALTER TABLE node DROP IF EXISTS node_template_id');
    }
}
