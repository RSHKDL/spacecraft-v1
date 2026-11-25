<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261125095615 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename fleet.name_value to fleet.name';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fleet RENAME COLUMN name_value TO name');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fleet RENAME COLUMN name TO name_value');
    }
}
