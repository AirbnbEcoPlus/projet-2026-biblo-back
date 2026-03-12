<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260312165403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE adherent (id INT AUTO_INCREMENT NOT NULL, date_adhesion DATE NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, date_naiss DATE NOT NULL, email VARCHAR(255) DEFAULT NULL, adresse_postale VARCHAR(255) DEFAULT NULL, num_tel VARCHAR(255) DEFAULT NULL, photo VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE auteur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, date_naissance DATE DEFAULT NULL, date_deces VARCHAR(255) DEFAULT NULL, nationalite VARCHAR(255) DEFAULT NULL, photo VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY `FK_23A0E66BCF5E72D`');
        $this->addSql('DROP TABLE article');
        $this->addSql('ALTER TABLE categorie ADD nom VARCHAR(255) NOT NULL, ADD description VARCHAR(255) NOT NULL, DROP libelle');
        $this->addSql('ALTER TABLE emprunt DROP id_emp');
        $this->addSql('ALTER TABLE emprunt ADD CONSTRAINT FK_364071D725F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE emprunt ADD CONSTRAINT FK_364071D737D925CB FOREIGN KEY (livre_id) REFERENCES livre (id)');
        $this->addSql('ALTER TABLE livre DROP id_livre');
        $this->addSql('ALTER TABLE livre ADD CONSTRAINT FK_AC634F99B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE livre_auteur ADD CONSTRAINT FK_A11876B537D925CB FOREIGN KEY (livre_id) REFERENCES livre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE livre_auteur ADD CONSTRAINT FK_A11876B560BB6FE6 FOREIGN KEY (auteur_id) REFERENCES auteur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE livre_categorie ADD CONSTRAINT FK_E61B069E37D925CB FOREIGN KEY (livre_id) REFERENCES livre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE livre_categorie ADD CONSTRAINT FK_E61B069EBCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation DROP id_resa');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495525F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_uca1400_ai_ci`, contenu LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_uca1400_ai_ci`, publie TINYINT NOT NULL, date_creation DATETIME NOT NULL, numero_ordre INT NOT NULL, categorie_id INT NOT NULL, INDEX IDX_23A0E66BCF5E72D (categorie_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_uca1400_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT `FK_23A0E66BCF5E72D` FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        $this->addSql('DROP TABLE adherent');
        $this->addSql('DROP TABLE auteur');
        $this->addSql('ALTER TABLE categorie ADD libelle VARCHAR(100) NOT NULL, DROP nom, DROP description');
        $this->addSql('ALTER TABLE emprunt DROP FOREIGN KEY FK_364071D725F06C53');
        $this->addSql('ALTER TABLE emprunt DROP FOREIGN KEY FK_364071D737D925CB');
        $this->addSql('ALTER TABLE emprunt ADD id_emp INT NOT NULL');
        $this->addSql('ALTER TABLE livre DROP FOREIGN KEY FK_AC634F99B83297E7');
        $this->addSql('ALTER TABLE livre ADD id_livre INT NOT NULL');
        $this->addSql('ALTER TABLE livre_auteur DROP FOREIGN KEY FK_A11876B537D925CB');
        $this->addSql('ALTER TABLE livre_auteur DROP FOREIGN KEY FK_A11876B560BB6FE6');
        $this->addSql('ALTER TABLE livre_categorie DROP FOREIGN KEY FK_E61B069E37D925CB');
        $this->addSql('ALTER TABLE livre_categorie DROP FOREIGN KEY FK_E61B069EBCF5E72D');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495525F06C53');
        $this->addSql('ALTER TABLE reservation ADD id_resa INT NOT NULL');
    }
}
