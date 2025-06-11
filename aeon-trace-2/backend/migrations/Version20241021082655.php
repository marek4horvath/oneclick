<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241021082655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transport_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company_transport_type (company_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', transport_type_id INT NOT NULL, INDEX IDX_7FC62F0F979B1AD6 (company_id), INDEX IDX_7FC62F0F519B4C62 (transport_type_id), PRIMARY KEY(company_id, transport_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE company_transport_type ADD CONSTRAINT FK_7FC62F0F979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE company_transport_type ADD CONSTRAINT FK_7FC62F0F519B4C62 FOREIGN KEY (transport_type_id) REFERENCES transport_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user DROP type_of_transport');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company_transport_type DROP FOREIGN KEY FK_7FC62F0F979B1AD6');
        $this->addSql('ALTER TABLE company_transport_type DROP FOREIGN KEY FK_7FC62F0F519B4C62');
        $this->addSql('DROP TABLE transport_type');
        $this->addSql('DROP TABLE company_transport_type');
        $this->addSql('ALTER TABLE user ADD type_of_transport JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }
}
