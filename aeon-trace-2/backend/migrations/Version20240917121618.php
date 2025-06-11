<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240917121618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add deleted at flag to supply chain template.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE supply_chain_template ADD deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE supply_chain_template DROP deleted_at');
    }
}
