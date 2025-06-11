<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241029062400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added new fieldy date_time_to, date_time_from into product_input';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_input ADD IF NOT EXISTS date_time_to DATETIME DEFAULT NULL, ADD IF NOT EXISTS date_time_from DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_input DROP IF EXISTS date_time_to, DROP IF EXISTS date_time_from');
    }
}
