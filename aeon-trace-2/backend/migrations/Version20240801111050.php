<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240801111050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add image collection and qr id to product_step';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input_image CHANGE image image VARCHAR(255) DEFAULT \'\'');
        $this->addSql('ALTER TABLE product_step ADD qr_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input_image CHANGE image image VARCHAR(255) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE product_step DROP qr_id');
    }
}
