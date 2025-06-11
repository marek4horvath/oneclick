<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250325095205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add locked boolean for product input.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input ADD locked TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE product_input_image CHANGE input_id input_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input_image CHANGE input_id input_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE product_input DROP locked');
    }
}
