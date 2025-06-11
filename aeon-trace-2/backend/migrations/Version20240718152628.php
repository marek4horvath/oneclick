<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240718152628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Input entity with relation to Step.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE input (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', step_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', type VARCHAR(255) DEFAULT \'\' NOT NULL, name VARCHAR(255) DEFAULT \'\' NOT NULL, sort INT DEFAULT 0 NOT NULL, INDEX IDX_D82832D773B21E9C (step_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE input ADD CONSTRAINT FK_D82832D773B21E9C FOREIGN KEY (step_id) REFERENCES step (id)');
        $this->addSql('ALTER TABLE step ADD step_image VARCHAR(255) DEFAULT \'\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE input DROP FOREIGN KEY FK_D82832D773B21E9C');
        $this->addSql('DROP TABLE input');
        $this->addSql('ALTER TABLE step DROP step_image');
    }
}
