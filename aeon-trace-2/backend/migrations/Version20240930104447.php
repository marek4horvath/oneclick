<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240930104447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add product template reference to node';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE node ADD product_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE845A9F591A7 FOREIGN KEY (product_template_id) REFERENCES product_template (id)');
        $this->addSql('CREATE INDEX IDX_857FE845A9F591A7 ON node (product_template_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE845A9F591A7');
        $this->addSql('DROP INDEX IDX_857FE845A9F591A7 ON node');
        $this->addSql('ALTER TABLE node DROP product_template_id');
    }
}
