<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250516144112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create a entity to track input history.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product_input_history (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', product_input_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', updated_by_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, text_value VARCHAR(255) DEFAULT NULL, numerical_value DOUBLE PRECISION DEFAULT NULL, date_time_from DATETIME DEFAULT NULL, date_time_to DATETIME DEFAULT NULL, latitude_value DOUBLE PRECISION DEFAULT NULL, longitude_value DOUBLE PRECISION DEFAULT NULL, text_area_value LONGTEXT DEFAULT NULL, document VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, radio_value VARCHAR(255) DEFAULT NULL, checkbox_value JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', unit_measurement VARCHAR(255) DEFAULT NULL, measurement_type VARCHAR(255) DEFAULT NULL, measurement_value DOUBLE PRECISION DEFAULT NULL, automatic_calculation TINYINT(1) DEFAULT 0 NOT NULL, locked TINYINT(1) DEFAULT 0 NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', version INT DEFAULT NULL, INDEX IDX_E841F579FDF27041 (product_input_id), INDEX IDX_E841F579896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_input_history_product_input_image (product_input_history_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', product_input_image_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_3C1D2CED5571F2F9 (product_input_history_id), INDEX IDX_3C1D2CEDF5BD8F68 (product_input_image_id), PRIMARY KEY(product_input_history_id, product_input_image_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_input_history ADD CONSTRAINT FK_E841F579FDF27041 FOREIGN KEY (product_input_id) REFERENCES product_input (id)');
        $this->addSql('ALTER TABLE product_input_history ADD CONSTRAINT FK_E841F579896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product_input_history_product_input_image ADD CONSTRAINT FK_3C1D2CED5571F2F9 FOREIGN KEY (product_input_history_id) REFERENCES product_input_history (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_input_history_product_input_image ADD CONSTRAINT FK_3C1D2CEDF5BD8F68 FOREIGN KEY (product_input_image_id) REFERENCES product_input_image (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dpp ADD updatable TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE product_input ADD created_by_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD updated_by_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD updatable TINYINT(1) DEFAULT 0 NOT NULL, ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE product_input ADD CONSTRAINT FK_7974498BB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product_input ADD CONSTRAINT FK_7974498B896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_7974498BB03A8386 ON product_input (created_by_id)');
        $this->addSql('CREATE INDEX IDX_7974498B896DBBDE ON product_input (updated_by_id)');
        $this->addSql('ALTER TABLE product_step ADD updatable TINYINT(1) DEFAULT 0 NOT NULL, ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input_history DROP FOREIGN KEY FK_E841F579FDF27041');
        $this->addSql('ALTER TABLE product_input_history DROP FOREIGN KEY FK_E841F579896DBBDE');
        $this->addSql('ALTER TABLE product_input_history_product_input_image DROP FOREIGN KEY FK_3C1D2CED5571F2F9');
        $this->addSql('ALTER TABLE product_input_history_product_input_image DROP FOREIGN KEY FK_3C1D2CEDF5BD8F68');
        $this->addSql('DROP TABLE product_input_history');
        $this->addSql('DROP TABLE product_input_history_product_input_image');
        $this->addSql('ALTER TABLE product_input DROP FOREIGN KEY FK_7974498BB03A8386');
        $this->addSql('ALTER TABLE product_input DROP FOREIGN KEY FK_7974498B896DBBDE');
        $this->addSql('DROP INDEX IDX_7974498BB03A8386 ON product_input');
        $this->addSql('DROP INDEX IDX_7974498B896DBBDE ON product_input');
        $this->addSql('ALTER TABLE product_input DROP created_by_id, DROP updated_by_id, DROP updatable, DROP updated_at, DROP version');
        $this->addSql('ALTER TABLE dpp DROP updatable');
        $this->addSql('ALTER TABLE product_step DROP updatable, DROP updated_at');
    }
}
