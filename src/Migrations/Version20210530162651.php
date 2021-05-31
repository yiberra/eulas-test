<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210530162651 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {

        $this->addSql("CREATE TABLE `json_file` (
            `id` bigint(15) UNSIGNED NOT NULL COMMENT 'File ID',
            `name` varchar(255) NOT NULL COMMENT 'File name',
            `state` varchar(100) NOT NULL COMMENT 'File state',
            `createdAt` datetime NOT NULL COMMENT 'Created file date',
            `updatedAt` datetime DEFAULT current_timestamp() COMMENT 'Updated file date'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->addSql("ALTER TABLE `json_file`
        ADD PRIMARY KEY (`id`)");

        $this->addSql("ALTER TABLE `json_file`
        MODIFY `id` bigint(15) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'File ID'");



        $this->addSql("CREATE TABLE `product` (
            `id` bigint(15) NOT NULL,
            `styleNumber` varchar(255) NOT NULL COMMENT 'Product styleNumber',
            `name` varchar(255) NOT NULL COMMENT 'Product name',
            `price_amount` int(10) NOT NULL COMMENT 'Product price amount',
            `price_currency` varchar(5) DEFAULT NULL COMMENT 'Product price currency',
            `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Product images array',
            `state` varchar(100) NOT NULL COMMENT 'Product state'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->addSql("ALTER TABLE `product`
        ADD PRIMARY KEY (`id`),
        ADD UNIQUE KEY `styleNumber` (`styleNumber`)");

        $this->addSql("ALTER TABLE `product`
        MODIFY `id` bigint(15) NOT NULL AUTO_INCREMENT");

    }

    public function down(Schema $schema) : void
    {
        $this->addSql("DROP TABLE `json_file`");
        $this->addSql("DROP TABLE `product`");
    }
}
