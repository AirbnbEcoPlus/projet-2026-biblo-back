<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260312153746 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adherent DROP id_adh');
        $this->addSql('ALTER TABLE categorie DROP id_cat');
        $this->addSql('ALTER TABLE emprunt DROP id_emp');
        $this->addSql('ALTER TABLE reservation DROP id_resa');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adherent ADD id_adh INT NOT NULL');
        $this->addSql('ALTER TABLE categorie ADD id_cat INT NOT NULL');
        $this->addSql('ALTER TABLE emprunt ADD id_emp INT NOT NULL');
        $this->addSql('ALTER TABLE reservation ADD id_resa INT NOT NULL');
    }
}
