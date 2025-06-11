<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250109093208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the "step_parents_step_children" table to establish a many-to-many relationship between "step" entities with cascading deletes.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE step_parents_step_children (step_source BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', step_target BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_3133AF047294B209 (step_source), INDEX IDX_3133AF046B71E286 (step_target), PRIMARY KEY(step_source, step_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE step_parents_step_children ADD CONSTRAINT FK_3133AF047294B209 FOREIGN KEY IF NOT EXISTS (step_source) REFERENCES step (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE step_parents_step_children ADD CONSTRAINT FK_3133AF046B71E286 FOREIGN KEY IF NOT EXISTS (step_target) REFERENCES step (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE step_parents_step_children DROP FOREIGN KEY IF EXISTS FK_3133AF047294B209');
        $this->addSql('ALTER TABLE step_parents_step_children DROP FOREIGN KEY IF EXISTS FK_3133AF046B71E286');
        $this->addSql('DROP TABLE step_parents_step_children');
    }
}
