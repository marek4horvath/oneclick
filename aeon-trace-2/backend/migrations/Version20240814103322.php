<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240814103322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add product template reference to product.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product ADD product_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADA9F591A7 FOREIGN KEY (product_template_id) REFERENCES product_template (id)');
        $this->addSql('CREATE INDEX IDX_D34A04ADA9F591A7 ON product (product_template_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADA9F591A7');
        $this->addSql('DROP INDEX IDX_D34A04ADA9F591A7 ON product');
        $this->addSql('ALTER TABLE product DROP product_template_id');
    }
}
