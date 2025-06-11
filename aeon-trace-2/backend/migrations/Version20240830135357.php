<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240830135357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add dpp to step.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE step ADD dpp_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE step ADD CONSTRAINT FK_43B9FE3C294AB09E FOREIGN KEY (dpp_id) REFERENCES dpp (id)');
        $this->addSql('CREATE INDEX IDX_43B9FE3C294AB09E ON step (dpp_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE step DROP FOREIGN KEY FK_43B9FE3C294AB09E');
        $this->addSql('DROP INDEX IDX_43B9FE3C294AB09E ON step');
        $this->addSql('ALTER TABLE step DROP dpp_id');
    }
}
