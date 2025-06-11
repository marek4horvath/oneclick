<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241024065103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Removal of the relationship between logistics template and company.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logistics_template DROP FOREIGN KEY IF EXISTS FK_BE36B48979B1AD6');
        $this->addSql('DROP INDEX IDX_BE36B48979B1AD6 ON logistics_template');
        $this->addSql('ALTER TABLE logistics_template DROP IF EXISTS company_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logistics_template ADD company_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE logistics_template ADD CONSTRAINT FK_BE36B48979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_BE36B48979B1AD6 ON logistics_template (company_id)');
    }
}
