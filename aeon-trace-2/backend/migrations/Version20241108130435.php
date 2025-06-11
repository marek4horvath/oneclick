<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241108130435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change relation between Node Parent and a Child to ManyToMany';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS node_parents_node_children (node_source BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', node_target BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_D7E05915EB986AD6 (node_source), INDEX IDX_D7E05915F27D3A59 (node_target), PRIMARY KEY(node_source, node_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE node_parents_node_children ADD CONSTRAINT FK_D7E05915EB986AD6 FOREIGN KEY IF NOT EXISTS (node_source) REFERENCES node (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE node_parents_node_children ADD CONSTRAINT FK_D7E05915F27D3A59 FOREIGN KEY IF NOT EXISTS (node_target) REFERENCES node (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY IF EXISTS FK_857FE845727ACA70');
        $this->addSql('DROP INDEX IF EXISTS IDX_857FE845727ACA70 ON node');
        $this->addSql('ALTER TABLE node DROP IF EXISTS parent_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE node_parents_node_children DROP FOREIGN KEY IF EXISTS FK_D7E05915EB986AD6');
        $this->addSql('ALTER TABLE node_parents_node_children DROP FOREIGN KEY IF EXISTS FK_D7E05915F27D3A59');
        $this->addSql('DROP TABLE IF EXISTS node_parents_node_children');
        $this->addSql('ALTER TABLE node ADD IF NOT EXISTS parent_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE845727ACA70 FOREIGN KEY IF NOT EXISTS (parent_id) REFERENCES node (id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_857FE845727ACA70 ON node (parent_id)');
    }
}
