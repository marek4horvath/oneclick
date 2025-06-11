<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240902122317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add input category entity and link it to input.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE input_category (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) DEFAULT \'\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE input ADD input_category_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE input ADD CONSTRAINT FK_D82832D7E78CA1A7 FOREIGN KEY (input_category_id) REFERENCES input_category (id)');
        $this->addSql('CREATE INDEX IDX_D82832D7E78CA1A7 ON input (input_category_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE input DROP FOREIGN KEY FK_D82832D7E78CA1A7');
        $this->addSql('DROP TABLE input_category');
        $this->addSql('DROP INDEX IDX_D82832D7E78CA1A7 ON input');
        $this->addSql('ALTER TABLE input DROP input_category_id');
    }
}
