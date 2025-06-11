<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240920085424 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create LogisticsTemplate and Logistics entities. Add relations to nodes and dpps.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE logistics (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', company_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', from_node_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', to_node_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', logistics_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', supply_chain_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) DEFAULT \'\' NOT NULL, description LONGTEXT DEFAULT \'\' NOT NULL, INDEX IDX_AE8ABD69979B1AD6 (company_id), INDEX IDX_AE8ABD69C0537C78 (from_node_id), INDEX IDX_AE8ABD69C895A222 (to_node_id), INDEX IDX_AE8ABD69A7C218ED (logistics_template_id), INDEX IDX_AE8ABD695D0D6533 (supply_chain_template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE logistics_template (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', company_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', steps_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) DEFAULT \'\' NOT NULL, description LONGTEXT DEFAULT \'\' NOT NULL, INDEX IDX_BE36B48979B1AD6 (company_id), INDEX IDX_BE36B4849CCB89A (steps_template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE logistics ADD CONSTRAINT FK_AE8ABD69979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE logistics ADD CONSTRAINT FK_AE8ABD69C0537C78 FOREIGN KEY (from_node_id) REFERENCES node (id)');
        $this->addSql('ALTER TABLE logistics ADD CONSTRAINT FK_AE8ABD69C895A222 FOREIGN KEY (to_node_id) REFERENCES node (id)');
        $this->addSql('ALTER TABLE logistics ADD CONSTRAINT FK_AE8ABD69A7C218ED FOREIGN KEY (logistics_template_id) REFERENCES logistics_template (id)');
        $this->addSql('ALTER TABLE logistics ADD CONSTRAINT FK_AE8ABD695D0D6533 FOREIGN KEY (supply_chain_template_id) REFERENCES supply_chain_template (id)');
        $this->addSql('ALTER TABLE logistics_template ADD CONSTRAINT FK_BE36B48979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE logistics_template ADD CONSTRAINT FK_BE36B4849CCB89A FOREIGN KEY (steps_template_id) REFERENCES steps_template (id)');
        $this->addSql('ALTER TABLE dpp ADD materials_sent_with_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD materials_received_from_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE dpp ADD CONSTRAINT FK_CFA5B22437D52E5F FOREIGN KEY (materials_sent_with_id) REFERENCES logistics (id)');
        $this->addSql('ALTER TABLE dpp ADD CONSTRAINT FK_CFA5B224C4100549 FOREIGN KEY (materials_received_from_id) REFERENCES logistics (id)');
        $this->addSql('CREATE INDEX IDX_CFA5B22437D52E5F ON dpp (materials_sent_with_id)');
        $this->addSql('CREATE INDEX IDX_CFA5B224C4100549 ON dpp (materials_received_from_id)');
        $this->addSql('ALTER TABLE product_input ADD type_of_transport VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product_step ADD logistics_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE product_step ADD CONSTRAINT FK_CE51DC9D7D418FFA FOREIGN KEY (logistics_id) REFERENCES logistics (id)');
        $this->addSql('CREATE INDEX IDX_CE51DC9D7D418FFA ON product_step (logistics_id)');
        $this->addSql('ALTER TABLE user ADD logistics_company TINYINT(1) DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp DROP FOREIGN KEY FK_CFA5B22437D52E5F');
        $this->addSql('ALTER TABLE dpp DROP FOREIGN KEY FK_CFA5B224C4100549');
        $this->addSql('ALTER TABLE product_step DROP FOREIGN KEY FK_CE51DC9D7D418FFA');
        $this->addSql('ALTER TABLE logistics DROP FOREIGN KEY FK_AE8ABD69979B1AD6');
        $this->addSql('ALTER TABLE logistics DROP FOREIGN KEY FK_AE8ABD69C0537C78');
        $this->addSql('ALTER TABLE logistics DROP FOREIGN KEY FK_AE8ABD69C895A222');
        $this->addSql('ALTER TABLE logistics DROP FOREIGN KEY FK_AE8ABD69A7C218ED');
        $this->addSql('ALTER TABLE logistics DROP FOREIGN KEY FK_AE8ABD695D0D6533');
        $this->addSql('ALTER TABLE logistics_template DROP FOREIGN KEY FK_BE36B48979B1AD6');
        $this->addSql('ALTER TABLE logistics_template DROP FOREIGN KEY FK_BE36B4849CCB89A');
        $this->addSql('DROP TABLE logistics');
        $this->addSql('DROP TABLE logistics_template');
        $this->addSql('DROP INDEX IDX_CFA5B22437D52E5F ON dpp');
        $this->addSql('DROP INDEX IDX_CFA5B224C4100549 ON dpp');
        $this->addSql('ALTER TABLE dpp DROP materials_sent_with_id, DROP materials_received_from_id');
        $this->addSql('ALTER TABLE product_input DROP type_of_transport');
        $this->addSql('DROP INDEX IDX_CE51DC9D7D418FFA ON product_step');
        $this->addSql('ALTER TABLE product_step DROP logistics_id');
        $this->addSql('ALTER TABLE user DROP logistics_company');
    }
}
