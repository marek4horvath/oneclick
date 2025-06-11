<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250107121208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adjusts indexes and foreign keys in the node_position, step_position tables and deletes the transport_arrival_for_loading and transport_finish_loading columns from the logistics table';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('ALTER TABLE logistics DROP COLUMN IF EXISTS transport_arrival_for_loading');
        $this->addSql('ALTER TABLE logistics DROP COLUMN IF EXISTS transport_finish_loading');
        $this->addSql('ALTER TABLE node_position DROP FOREIGN KEY IF EXISTS  FK_1F52EE5A460D9FD8');
        $this->addSql('DROP INDEX IF EXISTS FK_1F52EE5A460D9FD8 ON node_position');
        $this->addSql('ALTER TABLE node_position CHANGE node_id node_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS UNIQ_F881FCA0460D9FD7 ON node_position (node_id)');
        $this->addSql('ALTER TABLE node_position ADD CONSTRAINT FK_F881FCA0460D9FD7 FOREIGN KEY IF NOT EXISTS (node_id) REFERENCES node (id)');
        $this->addSql('ALTER TABLE step_position DROP FOREIGN KEY IF EXISTS FK_1F52EE5A460D9FD9');
        $this->addSql('DROP INDEX IF EXISTS FK_1F52EE5A460D9FD9 ON step_position');
        $this->addSql('ALTER TABLE step_position CHANGE step_id step_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS UNIQ_7629E69A73B21E9C ON step_position (step_id)');
        $this->addSql('ALTER TABLE step_position ADD CONSTRAINT FK_7629E69A73B21E9C FOREIGN KEY IF NOT EXISTS (step_id) REFERENCES step (id)');
    }

    public function down(Schema $schema): void
    {

        $this->addSql('ALTER TABLE logistics ADD IF NOT EXISTS transport_arrival_for_loading DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE logistics ADD IF NOT EXISTS transport_finish_loading DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE step_position DROP FOREIGN KEY IF EXISTS FK_7629E69A73B21E9C');
        $this->addSql('DROP INDEX UNIQ_7629E69A73B21E9C ON step_position');
        $this->addSql('ALTER TABLE step_position CHANGE step_id step_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('CREATE INDEX FK_1F52EE5A460D9FD9 ON step_position (step_id)');
        $this->addSql('ALTER TABLE step_position ADD CONSTRAINT IF NOT EXISTS FK_1F52EE5A460D9FD9 FOREIGN KEY (step_id) REFERENCES step (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE node_position DROP FOREIGN KEY IF EXISTS FK_F881FCA0460D9FD7');
        $this->addSql('DROP INDEX IF EXISTS UNIQ_F881FCA0460D9FD7 ON node_position');
        $this->addSql('ALTER TABLE node_position CHANGE node_id node_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('CREATE INDEX FK_1F52EE5A460D9FD8 ON node_position (node_id)');
        $this->addSql('ALTER TABLE node_position ADD CONSTRAINT IF NOT EXISTS FK_1F52EE5A460D9FD8 FOREIGN KEY (node_id) REFERENCES node (id) ON DELETE CASCADE');
    }
}
