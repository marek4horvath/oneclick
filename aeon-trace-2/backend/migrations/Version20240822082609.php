<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240822082609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add node into DPP.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp ADD node_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE dpp ADD CONSTRAINT FK_CFA5B224460D9FD7 FOREIGN KEY (node_id) REFERENCES node (id)');
        $this->addSql('CREATE INDEX IDX_CFA5B224460D9FD7 ON dpp (node_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp DROP FOREIGN KEY FK_CFA5B224460D9FD7');
        $this->addSql('DROP INDEX IDX_CFA5B224460D9FD7 ON dpp');
        $this->addSql('ALTER TABLE dpp DROP node_id');
    }
}
