<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250402071420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_step DROP IF EXISTS dpp_name, DROP IF EXISTS ongoing_dpp, DROP IF EXISTS create_empty_dpp, DROP IF EXISTS state');
    }

    public function down(Schema $schema): void
    {
    }
}
