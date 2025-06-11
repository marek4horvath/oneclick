<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241108123125 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create a join table for many-to-many relationship between product_input and input_category';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE IF NOT EXISTS product_input_category (product_input_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', input_category_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_CE54B3C4FDF27041 (product_input_id), INDEX IDX_CE54B3C4E78CA1A7 (input_category_id), PRIMARY KEY(product_input_id, input_category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_input_category ADD CONSTRAINT FK_CE54B3C4FDF27041 FOREIGN KEY IF NOT EXISTS (product_input_id) REFERENCES product_input (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_input_category ADD CONSTRAINT FK_CE54B3C4E78CA1A7 FOREIGN KEY IF NOT EXISTS (input_category_id) REFERENCES input_category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_input_category DROP FOREIGN KEY FK_CE54B3C4FDF27041');
        $this->addSql('ALTER TABLE product_input_category DROP FOREIGN KEY FK_CE54B3C4E78CA1A7');
        $this->addSql('DROP TABLE product_input_category');
    }
}
