<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240807160900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add node\'s siblings (many to many relation).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE node_node (node_source BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', node_target BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_42DB65D3EB986AD6 (node_source), INDEX IDX_42DB65D3F27D3A59 (node_target), PRIMARY KEY(node_source, node_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE node_node ADD CONSTRAINT FK_42DB65D3EB986AD6 FOREIGN KEY (node_source) REFERENCES node (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE node_node ADD CONSTRAINT FK_42DB65D3F27D3A59 FOREIGN KEY (node_target) REFERENCES node (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE node_node DROP FOREIGN KEY FK_42DB65D3EB986AD6');
        $this->addSql('ALTER TABLE node_node DROP FOREIGN KEY FK_42DB65D3F27D3A59');
        $this->addSql('DROP TABLE node_node');
    }
}
