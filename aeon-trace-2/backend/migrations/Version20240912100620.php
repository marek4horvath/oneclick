<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240912100620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add steps to dpp (many to many).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE dpp_step (dpp_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', step_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_E77D5F8294AB09E (dpp_id), INDEX IDX_E77D5F873B21E9C (step_id), PRIMARY KEY(dpp_id, step_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dpp_step ADD CONSTRAINT FK_E77D5F8294AB09E FOREIGN KEY (dpp_id) REFERENCES dpp (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dpp_step ADD CONSTRAINT FK_E77D5F873B21E9C FOREIGN KEY (step_id) REFERENCES step (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp_step DROP FOREIGN KEY FK_E77D5F8294AB09E');
        $this->addSql('ALTER TABLE dpp_step DROP FOREIGN KEY FK_E77D5F873B21E9C');
        $this->addSql('DROP TABLE dpp_step');
    }
}
