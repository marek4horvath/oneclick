<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240801083606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migrate company <-> product relation to company <-> productTemplate relation.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company_product DROP FOREIGN KEY FK_F3181E7A4584665A');
        $this->addSql('ALTER TABLE company_product DROP FOREIGN KEY FK_F3181E7A979B1AD6');
        $this->addSql('ALTER TABLE product_company DROP FOREIGN KEY FK_9E6612FF4584665A');
        $this->addSql('ALTER TABLE product_company DROP FOREIGN KEY FK_9E6612FF979B1AD6');
        $this->addSql('DROP TABLE company_product');
        $this->addSql('DROP TABLE product_company');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE845D6EF0C91');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE845979B1AD6');
        $this->addSql('DROP INDEX IDX_857FE845D6EF0C91 ON node');
        $this->addSql('DROP INDEX IDX_857FE845979B1AD6 ON node');
        $this->addSql('ALTER TABLE node DROP company_id, DROP company_site_id');
        $this->addSql('ALTER TABLE product_template DROP FOREIGN KEY FK_5CD07514979B1AD6');
        $this->addSql('DROP INDEX IDX_5CD07514979B1AD6 ON product_template');
        $this->addSql('ALTER TABLE product_template DROP company_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company_product ADD CONSTRAINT FK_F3181E7A4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE company_product ADD CONSTRAINT FK_F3181E7A979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_company ADD CONSTRAINT FK_9E6612FF4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_company ADD CONSTRAINT FK_9E6612FF979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE node ADD company_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD company_site_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE845D6EF0C91 FOREIGN KEY (company_site_id) REFERENCES company_site (id)');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE845979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_857FE845D6EF0C91 ON node (company_site_id)');
        $this->addSql('CREATE INDEX IDX_857FE845979B1AD6 ON node (company_id)');
        $this->addSql('ALTER TABLE product_template ADD company_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE product_template ADD CONSTRAINT FK_5CD07514979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_5CD07514979B1AD6 ON product_template (company_id)');
    }
}
