<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240718081313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make parent node hold more than one child node.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE node DROP INDEX UNIQ_857FE845727ACA70, ADD INDEX IDX_857FE845727ACA70 (parent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE node DROP INDEX IDX_857FE845727ACA70, ADD UNIQUE INDEX UNIQ_857FE845727ACA70 (parent_id)');
    }
}
