<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241021101712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop FOREIGN KEY FK_7FC62F0F519B4C62';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company_transport_type DROP FOREIGN KEY FK_7FC62F0F519B4C62');
        $this->addSql('ALTER TABLE company_transport_type ADD CONSTRAINT FK_7FC62F0F519B4C62 FOREIGN KEY (transport_type_id) REFERENCES transport_type (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company_transport_type DROP FOREIGN KEY FK_7FC62F0F519B4C62');
        $this->addSql('ALTER TABLE company_transport_type ADD CONSTRAINT FK_7FC62F0F519B4C62 FOREIGN KEY (transport_type_id) REFERENCES transport_type (id)');
    }
}
