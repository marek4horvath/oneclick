<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241024094647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Delete entity LogisticsTransport. Added new fieldy into logistics';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logistics_transport DROP FOREIGN KEY IF EXISTS FK_6A81B9627D418FFA');
        $this->addSql('ALTER TABLE logistics_transport DROP FOREIGN KEY IF EXISTS FK_6A81B962A7C218ED');
        $this->addSql('DROP TABLE logistics_transport');
        $this->addSql('ALTER TABLE logistics ADD IF NOT EXISTS departure_dime DATETIME DEFAULT NULL, ADD IF NOT EXISTS arrival_time DATETIME DEFAULT NULL, ADD IF NOT EXISTS transport_arrival_for_loading DATETIME DEFAULT NULL, ADD IF NOT EXISTS transport_finish_loading DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE logistics_transport (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', logistics_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', logistics_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', departure_dime DATETIME DEFAULT NULL, arrival_time DATETIME DEFAULT NULL, transport_arrival_for_loading DATETIME DEFAULT NULL, transport_finish_loading DATETIME DEFAULT NULL, INDEX IDX_6A81B962A7C218ED (logistics_template_id), INDEX IDX_6A81B9627D418FFA (logistics_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE logistics_transport ADD CONSTRAINT FK_6A81B9627D418FFA FOREIGN KEY (logistics_id) REFERENCES logistics (id)');
        $this->addSql('ALTER TABLE logistics_transport ADD CONSTRAINT FK_6A81B962A7C218ED FOREIGN KEY (logistics_template_id) REFERENCES logistics_template (id)');
        $this->addSql('ALTER TABLE logistics DROP departure_dime, DROP arrival_time, DROP transport_arrival_for_loading, DROP transport_finish_loading');
    }
}
