<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250108100610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration for updating schema, indexes, and foreign keys.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE node ADD IF NOT EXISTS type_of_process_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', DROP IF EXISTS type_of_process');
        $defaultProcessId = '0A2B4F1AD00511EFB9910242AC120005';
        $this->addSql('UPDATE node SET type_of_process_id = UNHEX(\'' . $defaultProcessId . '\') WHERE type_of_process_id IS NOT NULL AND type_of_process_id NOT IN (SELECT id FROM process)');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE845E77B83D4 FOREIGN KEY IF NOT EXISTS (type_of_process_id) REFERENCES process (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_857FE845E77B83D4 ON node (type_of_process_id)');
        $this->addSql('ALTER TABLE node_position DROP INDEX IF EXISTS FK_1F52EE5A460D9FD8, ADD UNIQUE INDEX IF NOT EXISTS UNIQ_F881FCA0460D9FD7 (node_id)');
        $this->addSql('ALTER TABLE node_position DROP FOREIGN KEY IF EXISTS FK_1F52EE5A460D9FD8');
        $this->addSql('ALTER TABLE node_position CHANGE node_id node_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE node_position ADD CONSTRAINT FK_F881FCA0460D9FD7 FOREIGN KEY IF NOT EXISTS (node_id) REFERENCES node (id)');
        $this->addSql('ALTER TABLE process CHANGE color color VARCHAR(7) DEFAULT \'#ffffff\'');
        $this->addSql('ALTER TABLE step_position DROP INDEX IF EXISTS  FK_1F52EE5A460D9FD9, ADD UNIQUE INDEX IF NOT EXISTS UNIQ_7629E69A73B21E9C (step_id)');
        $this->addSql('ALTER TABLE step_position DROP FOREIGN KEY IF EXISTS FK_1F52EE5A460D9FD9');
        $this->addSql('ALTER TABLE step_position CHANGE step_id step_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE step_position ADD CONSTRAINT FK_7629E69A73B21E9C FOREIGN KEY IF NOT EXISTS (step_id) REFERENCES step (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE process CHANGE color color VARCHAR(7) DEFAULT NULL');
        $this->addSql('ALTER TABLE step_position DROP INDEX UNIQ_7629E69A73B21E9C, ADD INDEX FK_1F52EE5A460D9FD9 (step_id)');
        $this->addSql('ALTER TABLE step_position DROP FOREIGN KEY IF EXISTS FK_7629E69A73B21E9C');
        $this->addSql('ALTER TABLE step_position CHANGE step_id step_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE step_position ADD CONSTRAINT FK_1F52EE5A460D9FD9 FOREIGN KEY (step_id) REFERENCES step (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY IF EXISTS FK_857FE845E77B83D4');
        $this->addSql('DROP INDEX IDX_857FE845E77B83D4 ON node');
        $this->addSql('ALTER TABLE node ADD IF NOT EXISTS type_of_process VARCHAR(255) NOT NULL, DROP IF EXISTS type_of_process_id');
        $this->addSql('ALTER TABLE node_position DROP INDEX IF EXISTS UNIQ_F881FCA0460D9FD7, ADD INDEX IF NOT EXISTS FK_1F52EE5A460D9FD8 (node_id)');
        $this->addSql('ALTER TABLE node_position DROP FOREIGN KEY IF EXISTS FK_F881FCA0460D9FD7');
        $this->addSql('ALTER TABLE node_position CHANGE node_id node_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE node_position ADD CONSTRAINT IF NOT EXISTS FK_1F52EE5A460D9FD8 FOREIGN KEY (node_id) REFERENCES node (id) ON DELETE CASCADE');
    }
}
