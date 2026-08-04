<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260804132300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the Ship table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ship (id UUID NOT NULL, name VARCHAR(255) DEFAULT NULL, class VARCHAR(255) NOT NULL, hull_current INT NOT NULL, hull_max INT NOT NULL, PRIMARY KEY (id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ship');
    }
}
