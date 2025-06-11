<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241129065644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a `user_id` column to the `logistics` table as a nullable UUID field with a foreign key constraint referencing the `user` table. Includes an index on `user_id` for optimized queries.';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('ALTER TABLE logistics ADD IF NOT EXISTS user_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE logistics ADD CONSTRAINT FK_AE8ABD69A76ED395 FOREIGN KEY IF NOT EXISTS (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_AE8ABD69A76ED395 ON logistics (user_id)');
    }

    public function down(Schema $schema): void
    {

        $this->addSql('ALTER TABLE logistics DROP FOREIGN KEY IF EXISTS FK_AE8ABD69A76ED395');
        $this->addSql('DROP INDEX IDX_AE8ABD69A76ED395 ON logistics');
        $this->addSql('ALTER TABLE logistics DROP IF EXISTS user_id');
    }
}
