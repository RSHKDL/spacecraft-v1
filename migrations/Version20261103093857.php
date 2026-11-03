<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261103093857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change FleetName and ShipName to type TEXT';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fleet ALTER name_value TYPE TEXT');
        $this->addSql('ALTER TABLE ship ALTER name TYPE TEXT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fleet ALTER name_value TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE ship ALTER name TYPE VARCHAR(255)');
    }
}
