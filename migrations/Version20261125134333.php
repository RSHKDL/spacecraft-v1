<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261125134333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ship references its design (id, revision) and stores its loadout';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ship ADD design_id UUID NOT NULL');
        $this->addSql('ALTER TABLE ship ADD design_revision INT NOT NULL');
        $this->addSql('ALTER TABLE ship ADD loadout JSONB NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ship DROP design_id');
        $this->addSql('ALTER TABLE ship DROP design_revision');
        $this->addSql('ALTER TABLE ship DROP loadout');
    }
}
