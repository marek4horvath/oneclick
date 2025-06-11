<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241022122757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add logistics template reference to input';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE input ADD IF NOT EXISTS logistics_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE input ADD CONSTRAINT FK_D82832D7A7C218ED FOREIGN KEY IF NOT EXISTS (logistics_template_id) REFERENCES logistics_template (id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_D82832D7A7C218ED ON input (logistics_template_id)');
        $this->addSql('ALTER TABLE logistics_template DROP FOREIGN KEY IF EXISTS FK_BE36B4849CCB89A');
        $this->addSql('DROP INDEX IF EXISTS IDX_BE36B4849CCB89A ON logistics_template');
        $this->addSql('ALTER TABLE logistics_template ADD IF NOT EXISTS latitude DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD IF NOT EXISTS longitude DOUBLE PRECISION DEFAULT \'0\' NOT NULL, DROP IF EXISTS steps_template_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logistics_template ADD steps_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', DROP latitude, DROP longitude');
        $this->addSql('ALTER TABLE logistics_template ADD CONSTRAINT FK_BE36B4849CCB89A FOREIGN KEY (steps_template_id) REFERENCES steps_template (id)');
        $this->addSql('CREATE INDEX IDX_BE36B4849CCB89A ON logistics_template (steps_template_id)');
        $this->addSql('ALTER TABLE input DROP FOREIGN KEY FK_D82832D7A7C218ED');
        $this->addSql('DROP INDEX IDX_D82832D7A7C218ED ON input');
        $this->addSql('ALTER TABLE input DROP logistics_template_id');
    }
}
