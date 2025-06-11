<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241025084634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added fieldy logistics_parent_id into logistics';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logistics ADD IF NOT EXISTS logistics_parent_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE logistics ADD CONSTRAINT FK_AE8ABD697E50523C FOREIGN KEY IF NOT EXISTS (logistics_parent_id) REFERENCES logistics (id)');
        $this->addSql('CREATE INDEX IDX_AE8ABD697E50523C ON logistics (logistics_parent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logistics DROP FOREIGN KEY FK_AE8ABD697E50523C');
        $this->addSql('DROP INDEX IDX_AE8ABD697E50523C ON logistics');
        $this->addSql('ALTER TABLE logistics DROP logistics_parent_id');
    }
}
