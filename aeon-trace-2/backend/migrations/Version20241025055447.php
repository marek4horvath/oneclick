<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241025055447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added logistics_id as UUID foreign key to logistic_steps table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logistic_steps ADD IF NOT EXISTS logistics_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE logistic_steps ADD CONSTRAINT FK_E35043757D418FFA FOREIGN KEY IF NOT EXISTS (logistics_id) REFERENCES logistics (id)');
        $this->addSql('CREATE INDEX IDX_E35043757D418FFA ON logistic_steps (logistics_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logistic_steps DROP FOREIGN KEY FK_E35043757D418FFA');
        $this->addSql('DROP INDEX IDX_E35043757D418FFA ON logistic_steps');
        $this->addSql('ALTER TABLE logistic_steps DROP logistics_id');
    }
}
