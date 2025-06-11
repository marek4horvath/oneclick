<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241024094206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added new fieldy into logistics';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logistics ADD IF NOT EXISTS type_of_transport VARCHAR(255) DEFAULT NULL, ADD IF NOT EXISTS starting_point_lat DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD IF NOT EXISTS starting_point_lng DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD IF NOT EXISTS destination_point_lat DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD IF NOT EXISTS destination_point_lng DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD IF NOT EXISTS total_distance DOUBLE PRECISION DEFAULT \'0\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logistics DROP type_of_transport, DROP starting_point_lat, DROP starting_point_lng, DROP destination_point_lat, DROP destination_point_lng, DROP total_distance');
    }
}
