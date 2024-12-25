<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateInitialTables extends AbstractMigration
{
    public function up(): void
    {
        $this->createTables();
        $this->createConstraints();
        $this->insertDefaults();
    }

    private function insertDefaults()
    {
        $pass = '$2y$10$GGArVO/7.xPDg6D5Kl6GHeELUg2Dnod68ynkFaZ7R2Vfx/K1oZ96O'; // testtest
        $sql = "INSERT INTO `user` (
id, `email`, `password`, `isActive`, `displayName`, `firstName`, `lastName`, `role`, `cms`, `uuid`
) VALUES (
'a90aa890-5fb4-4d26-82af-0c004734e1b8', 'test@example.com', '{$pass}', '1', 'admin', 'admin', 'admin', 1, 'a:0:{}', '');";
        $this->query($sql);
        $this->query("INSERT INTO `language` (id, name, code) VALUES ('6d28e700-b96b-4c8a-ad9a-5f68d6de0819', 'English', 'en')");
        $this->query("INSERT INTO `language` (id, name, code) VALUES ('a065ee05-db1f-4af4-be98-0632b11a3d6b', 'Serbian', 'sr')");
    }

    private function createTables()
    {
        $sql = "CREATE TABLE user (
        id VARCHAR(36) NOT NULL, firstName VARCHAR(128) NOT NULL, lastName VARCHAR(128) NOT NULL, email VARCHAR(128) NOT NULL, 
        password VARCHAR(128) NOT NULL, role SMALLINT NOT NULL, isActive INT NOT NULL, displayName VARCHAR(128) NOT NULL, 
        uuid VARCHAR(128) NOT NULL, ipv4 INT UNSIGNED DEFAULT NULL, lastLogin DATETIME DEFAULT NULL, 
        cms LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)', createdAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, 
        updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), 
        PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;";
        $this->query($sql);
        $sql = "CREATE TABLE forgotPasswordToken (
    id VARCHAR(36) NOT NULL, token VARCHAR(128) NOT NULL, entityId VARCHAR(255) NOT NULL, entityType INT NOT NULL, 
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, 
    PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;";
        $this->query($sql);
        $sql = "CREATE TABLE translation (
    id VARCHAR(36) NOT NULL, groupName VARCHAR(255) DEFAULT NULL, originalString VARCHAR(255) NOT NULL, translatedString VARCHAR(255) NOT NULL, 
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, 
    languageId VARCHAR(36) DEFAULT NULL, UNIQUE INDEX UNIQ_B469456FDC9ABBF1 (originalString), INDEX IDX_B469456F940D8C7E (languageId), 
    PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;";
        $this->query($sql);
        $sql = "CREATE TABLE language (
    id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, createdAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, 
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, 
    PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;";
        $this->query($sql);
    }

    private function createConstraints()
    {
        $sql = "ALTER TABLE translation ADD CONSTRAINT FK_B469456F940D8C7E FOREIGN KEY (languageId) REFERENCES language (id);";
        $this->query($sql);
    }

    public function down(): void
    {
        $sql = "
        SET FOREIGN_KEY_CHECKS = 0;
        DROP TABLE language;
        DROP TABLE translation;
        DROP TABLE forgotPasswordToken;
        DROP TABLE user;
        SET FOREIGN_KEY_CHECKS = 1;";
        $this->query($sql);
    }
}
