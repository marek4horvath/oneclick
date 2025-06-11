<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250408115022 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add fields related to creating DPP to product step, in order to make product step behave as DPP.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS product_step_material_received_from (product_step_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', logistics_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_FE0BF3D6A26B052C (product_step_id), INDEX IDX_FE0BF3D67D418FFA (logistics_id), PRIMARY KEY(product_step_id, logistics_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_step_material_received_from ADD CONSTRAINT FK_FE0BF3D6A26B052C FOREIGN KEY (product_step_id) REFERENCES product_step (id)');
        $this->addSql('ALTER TABLE product_step_material_received_from ADD CONSTRAINT FK_FE0BF3D67D418FFA FOREIGN KEY (logistics_id) REFERENCES logistics (id)');
        $this->addSql('ALTER TABLE product_step ADD node_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD company_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD company_site_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD materials_sent_with_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD dpp_name VARCHAR(255) NOT NULL, ADD ongoing_dpp TINYINT(1) DEFAULT 0 NOT NULL, ADD create_empty_dpp TINYINT(1) DEFAULT 0 NOT NULL, ADD state VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product_step ADD CONSTRAINT FK_CE51DC9D460D9FD7 FOREIGN KEY (node_id) REFERENCES node (id)');
        $this->addSql('ALTER TABLE product_step ADD CONSTRAINT FK_CE51DC9D979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product_step ADD CONSTRAINT FK_CE51DC9DD6EF0C91 FOREIGN KEY (company_site_id) REFERENCES company_site (id)');
        $this->addSql('ALTER TABLE product_step ADD CONSTRAINT FK_CE51DC9D37D52E5F FOREIGN KEY (materials_sent_with_id) REFERENCES logistics (id)');
        $this->addSql('CREATE INDEX IDX_CE51DC9D460D9FD7 ON product_step (node_id)');
        $this->addSql('CREATE INDEX IDX_CE51DC9D979B1AD6 ON product_step (company_id)');
        $this->addSql('CREATE INDEX IDX_CE51DC9DD6EF0C91 ON product_step (company_site_id)');
        $this->addSql('CREATE INDEX IDX_CE51DC9D37D52E5F ON product_step (materials_sent_with_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_step_material_received_from DROP FOREIGN KEY FK_FE0BF3D6A26B052C');
        $this->addSql('ALTER TABLE product_step_material_received_from DROP FOREIGN KEY FK_FE0BF3D67D418FFA');
        $this->addSql('DROP TABLE product_step_material_received_from');
        $this->addSql('ALTER TABLE product_step DROP FOREIGN KEY FK_CE51DC9D460D9FD7');
        $this->addSql('ALTER TABLE product_step DROP FOREIGN KEY FK_CE51DC9D979B1AD6');
        $this->addSql('ALTER TABLE product_step DROP FOREIGN KEY FK_CE51DC9DD6EF0C91');
        $this->addSql('ALTER TABLE product_step DROP FOREIGN KEY FK_CE51DC9D37D52E5F');
        $this->addSql('DROP INDEX IDX_CE51DC9D460D9FD7 ON product_step');
        $this->addSql('DROP INDEX IDX_CE51DC9D979B1AD6 ON product_step');
        $this->addSql('DROP INDEX IDX_CE51DC9DD6EF0C91 ON product_step');
        $this->addSql('DROP INDEX IDX_CE51DC9D37D52E5F ON product_step');
        $this->addSql('ALTER TABLE product_step DROP node_id, DROP company_id, DROP company_site_id, DROP materials_sent_with_id, DROP dpp_name, DROP ongoing_dpp, DROP create_empty_dpp, DROP state');
    }
}
