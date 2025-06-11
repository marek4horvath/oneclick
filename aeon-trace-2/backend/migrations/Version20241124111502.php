<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241124111502 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a foreign key relationship between the Dpp table and the User table using a UUID (user_id). Removes the previous user field from the Dpp table.';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('ALTER TABLE dpp ADD user_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', DROP IF EXISTS user');
        $this->addSql('ALTER TABLE dpp ADD CONSTRAINT FK_CFA5B224A76ED395 FOREIGN KEY IF NOT EXISTS (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_CFA5B224A76ED395 ON dpp (user_id)');
    }

    public function down(Schema $schema): void
    {

        $this->addSql('ALTER TABLE dpp DROP FOREIGN KEY IF EXISTS FK_CFA5B224A76ED395');
        $this->addSql('DROP INDEX IDX_CFA5B224A76ED395 ON dpp');
        $this->addSql('ALTER TABLE dpp ADD user VARCHAR(255) DEFAULT \'\' NOT NULL, DROP user_id');
    }
}
