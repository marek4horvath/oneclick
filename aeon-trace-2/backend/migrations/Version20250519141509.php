<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250519141509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Mark product input as nullable on product input image.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input_image CHANGE input_id input_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input_image CHANGE input_id input_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }
}
