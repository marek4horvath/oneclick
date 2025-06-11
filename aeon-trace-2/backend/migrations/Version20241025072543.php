<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241025072543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added fieldy logistic_steps into logistics';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logistic_steps DROP FOREIGN KEY IF EXISTS FK_E35043757D418FFA');
        $this->addSql('DROP TABLE logistic_steps');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE logistic_steps (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', logistics_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_E35043757D418FFA (logistics_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE logistic_steps ADD CONSTRAINT FK_E35043757D418FFA FOREIGN KEY (logistics_id) REFERENCES logistics (id)');
    }
}
