<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240719120745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Dpp Input and Dpp Input Image Tables, drop node from dpp.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE dpp_input (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', dpp_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) DEFAULT \'\' NOT NULL, text_value VARCHAR(255) DEFAULT NULL, text_area_value LONGTEXT DEFAULT NULL, document VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, INDEX IDX_446CFA55294AB09E (dpp_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dpp_input_image (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', input_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', image VARCHAR(255) DEFAULT \'\', INDEX IDX_17A154DC36421AD6 (input_id), INDEX IDX_17A154DCC53D045F (image), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dpp_input ADD CONSTRAINT FK_446CFA55294AB09E FOREIGN KEY (dpp_id) REFERENCES dpp (id)');
        $this->addSql('ALTER TABLE dpp_input_image ADD CONSTRAINT FK_17A154DC36421AD6 FOREIGN KEY (input_id) REFERENCES dpp_input (id)');
        $this->addSql('ALTER TABLE dpp DROP FOREIGN KEY FK_CFA5B224460D9FD7');
        $this->addSql('DROP INDEX IDX_CFA5B224460D9FD7 ON dpp');
        $this->addSql('ALTER TABLE dpp DROP node_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp_input DROP FOREIGN KEY FK_446CFA55294AB09E');
        $this->addSql('ALTER TABLE dpp_input_image DROP FOREIGN KEY FK_17A154DC36421AD6');
        $this->addSql('DROP TABLE dpp_input');
        $this->addSql('DROP TABLE dpp_input_image');
        $this->addSql('ALTER TABLE dpp ADD node_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE dpp ADD CONSTRAINT FK_CFA5B224460D9FD7 FOREIGN KEY (node_id) REFERENCES node (id)');
        $this->addSql('CREATE INDEX IDX_CFA5B224460D9FD7 ON dpp (node_id)');
    }
}
