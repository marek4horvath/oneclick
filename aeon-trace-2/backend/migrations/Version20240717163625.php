<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class Version20240717163625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add slug for company (user).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD slug VARCHAR(128) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649989D9B62 ON user (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_8D93D649989D9B62 ON user');
        $this->addSql('ALTER TABLE user DROP slug');
    }

    public function postUp(Schema $schema): void
    {
        $slugger = new AsciiSlugger();
        $data = [];

        foreach ($this->connection->executeQuery('SELECT id, name FROM user')->fetchAllAssociative() as $item) {
            $data[] = [
                'slug' => $slugger->slug($item['name'])->lower()->toString(),
                'id' => $item['id'],
            ];
        }

        foreach ($data as $item) {
            $this->updateSlug($item['id'], $item['slug']);
        }
    }

    /**
     * @throws Exception
     */
    protected function updateSlug($id, $slug): void
    {
        $query = $this->connection->prepare('UPDATE user SET slug=:slug WHERE id=:id;');
        try {
            $query->bindValue('slug', $slug);
            $query->bindValue('id', $id);
            $query->executeQuery();
        } catch (Exception $e) {
            if ($e->getCode() === 1062) {
                $this->updateSlug($id, $slug . '-');
            } else {
                throw new Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }
    }
}
