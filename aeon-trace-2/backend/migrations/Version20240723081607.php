<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240723081607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add parent field and child collection to dpp entity.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp ADD parent_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE dpp ADD CONSTRAINT FK_CFA5B224727ACA70 FOREIGN KEY (parent_id) REFERENCES dpp (id)');
        $this->addSql('CREATE INDEX IDX_CFA5B224727ACA70 ON dpp (parent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp DROP FOREIGN KEY FK_CFA5B224727ACA70');
        $this->addSql('DROP INDEX IDX_CFA5B224727ACA70 ON dpp');
        $this->addSql('ALTER TABLE dpp DROP parent_id');
    }
}
