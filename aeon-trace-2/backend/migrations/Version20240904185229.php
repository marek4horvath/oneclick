<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240904185229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add createdAt and tsaVerifiedAt to DPP.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp ADD created_at DATETIME DEFAULT NULL, ADD tsa_verified_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp DROP created_at, DROP tsa_verified_at');
    }
}
