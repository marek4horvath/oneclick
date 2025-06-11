<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241107210338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds quantity index to differ product steps';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_step ADD IF NOT EXISTS quantity_index INT DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_step DROP IF EXISTS quantity_index');
    }
}
