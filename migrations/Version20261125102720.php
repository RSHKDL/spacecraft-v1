<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261125102720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create ship_design table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ship_design (id UUID NOT NULL, name TEXT NOT NULL, revision INT NOT NULL, class VARCHAR(255) NOT NULL, components JSONB NOT NULL, PRIMARY KEY (id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ship_design');
    }
}
