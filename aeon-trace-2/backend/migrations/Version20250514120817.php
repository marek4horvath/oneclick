<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250514120817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add radio button and checkbox input types.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE input ADD options JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE product_input ADD radio_value VARCHAR(255) DEFAULT NULL, ADD checkbox_value JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input DROP radio_value, DROP checkbox_value');
        $this->addSql('ALTER TABLE input DROP options');
    }
}
