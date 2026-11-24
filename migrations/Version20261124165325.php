<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261124165325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'ComponentModel aggregate persisted as doctrine entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE component_model (id UUID NOT NULL, type VARCHAR(255) NOT NULL, ship_classes JSONB NOT NULL, power INT NOT NULL, discontinued BOOLEAN NOT NULL, PRIMARY KEY (id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE component_model');
    }
}
