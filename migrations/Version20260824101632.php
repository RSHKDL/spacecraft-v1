<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260824101632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the fleet and fleet assignments tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE fleet (id UUID NOT NULL, flagship_id UUID DEFAULT NULL, name_value VARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE fleet_assignment (ship_id UUID NOT NULL, fleet_id UUID NOT NULL, PRIMARY KEY (ship_id))');
        $this->addSql('CREATE INDEX IDX_8547749D4B061DF9 ON fleet_assignment (fleet_id)');
        $this->addSql('ALTER TABLE fleet_assignment ADD CONSTRAINT FK_8547749D4B061DF9 FOREIGN KEY (fleet_id) REFERENCES fleet (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fleet_assignment DROP CONSTRAINT FK_8547749D4B061DF9');
        $this->addSql('DROP TABLE fleet');
        $this->addSql('DROP TABLE fleet_assignment');
    }
}
