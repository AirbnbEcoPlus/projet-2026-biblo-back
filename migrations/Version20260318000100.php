<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260318000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add description column to livre';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE livre ADD description LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE livre DROP description');
    }
}
