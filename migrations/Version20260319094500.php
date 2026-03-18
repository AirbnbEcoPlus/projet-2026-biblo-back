<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260319094500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create audit_log table for generic entity action history';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE audit_log (id INT AUTO_INCREMENT NOT NULL, entity_class VARCHAR(255) NOT NULL, entity_id VARCHAR(64) DEFAULT NULL, action VARCHAR(20) NOT NULL, user_email VARCHAR(180) DEFAULT NULL, data JSON DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX idx_audit_entity_class (entity_class), INDEX idx_audit_action (action), INDEX idx_audit_created_at (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE audit_log');
    }
}
