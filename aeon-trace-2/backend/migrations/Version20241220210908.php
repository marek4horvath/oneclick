<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241220210908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add table with step node positions for treeflow nodes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE IF NOT EXISTS step_position (
                id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)',
                step_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)',
                x FLOAT DEFAULT NULL,
                y FLOAT DEFAULT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");

        $this->addSql('ALTER TABLE step_position ADD CONSTRAINT FK_1F52EE5A460D9FD9 FOREIGN KEY (step_id) REFERENCES step (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE step_position DROP FOREIGN KEY FK_1F52EE5A460D9FD9');
        $this->addSql('DROP TABLE IF EXISTS step_position');
    }
}
