<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241021095809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change ID types to BINARY(16) for UUIDs.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company_transport_type DROP FOREIGN KEY FK_7FC62F0F519B4C62');

        $this->addSql('ALTER TABLE transport_type CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE company_transport_type CHANGE transport_type_id transport_type_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');

        $this->addSql('ALTER TABLE company_transport_type ADD CONSTRAINT FK_7FC62F0F519B4C62 FOREIGN KEY (transport_type_id) REFERENCES transport_type (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company_transport_type DROP FOREIGN KEY FK_7FC62F0F519B4C62');

        $this->addSql('ALTER TABLE company_transport_type CHANGE transport_type_id transport_type_id INT NOT NULL');
        $this->addSql('ALTER TABLE transport_type CHANGE id id INT AUTO_INCREMENT NOT NULL');

        $this->addSql('ALTER TABLE company_transport_type ADD CONSTRAINT FK_7FC62F0F519B4C62 FOREIGN KEY (transport_type_id) REFERENCES transport_type (id)');
    }
}
