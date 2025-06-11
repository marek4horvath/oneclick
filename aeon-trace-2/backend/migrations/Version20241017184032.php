<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241017184032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add model id to product and imported field to dpp';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp ADD imported TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE product ADD model_id VARCHAR(255) DEFAULT \'\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product DROP model_id');
        $this->addSql('ALTER TABLE dpp DROP imported');
    }
}
