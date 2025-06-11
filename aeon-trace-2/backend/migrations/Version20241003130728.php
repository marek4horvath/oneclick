<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241003130728 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add TSA verified at, created at and closed fields to product steps.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_step ADD tsa_verified_at DATETIME DEFAULT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD closed TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_step DROP tsa_verified_at, DROP created_at, DROP closed');
    }
}
