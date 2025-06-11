<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240820140629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add step and node many to many relation.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE node_step (node_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', step_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_841D73AA460D9FD7 (node_id), INDEX IDX_841D73AA73B21E9C (step_id), PRIMARY KEY(node_id, step_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE node_step ADD CONSTRAINT FK_841D73AA460D9FD7 FOREIGN KEY (node_id) REFERENCES node (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE node_step ADD CONSTRAINT FK_841D73AA73B21E9C FOREIGN KEY (step_id) REFERENCES step (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE node_step DROP FOREIGN KEY FK_841D73AA460D9FD7');
        $this->addSql('ALTER TABLE node_step DROP FOREIGN KEY FK_841D73AA73B21E9C');
        $this->addSql('DROP TABLE node_step');
    }
}
