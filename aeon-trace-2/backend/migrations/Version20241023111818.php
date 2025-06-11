<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241023111818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Making input categories and inputs a many to many relation.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE IF NOT EXISTS input_category_input (input_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', input_category_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_4E08601136421AD6 (input_id), INDEX IDX_4E086011E78CA1A7 (input_category_id), PRIMARY KEY(input_id, input_category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE input_category_input ADD CONSTRAINT FK_4E08601136421AD6 FOREIGN KEY IF NOT EXISTS (input_id) REFERENCES input (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE input_category_input ADD CONSTRAINT FK_4E086011E78CA1A7 FOREIGN KEY IF NOT EXISTS (input_category_id) REFERENCES input_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE input DROP FOREIGN KEY IF EXISTS FK_D82832D7E78CA1A7');
        $this->addSql('DROP INDEX IDX_D82832D7E78CA1A7 ON input');
        $this->addSql('ALTER TABLE input DROP IF EXISTS input_category_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE input_category_input DROP FOREIGN KEY FK_4E08601136421AD6');
        $this->addSql('ALTER TABLE input_category_input DROP FOREIGN KEY FK_4E086011E78CA1A7');
        $this->addSql('DROP TABLE input_category_input');
        $this->addSql('ALTER TABLE input ADD input_category_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE input ADD CONSTRAINT FK_D82832D7E78CA1A7 FOREIGN KEY (input_category_id) REFERENCES input_category (id)');
        $this->addSql('CREATE INDEX IDX_D82832D7E78CA1A7 ON input (input_category_id)');
    }
}
