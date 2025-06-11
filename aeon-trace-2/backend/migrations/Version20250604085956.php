<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250604085956 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a non-nullable "created_at" column of type datetime_immutable to the product_input_history table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input_history ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input_history DROP created_at');
    }
}
