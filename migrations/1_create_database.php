<?php
class CreateDatabase extends Migration
{
    public function description()
    {
        return 'Creates neccessary db tables for stoodle';
    }

    public function up()
    {
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `stoodle` (
            `stoodle_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NULL DEFAULT NULL,
            `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `mkdate` INT(11) UNSIGNED NOT NULL,
            `chdate` INT(11) UNSIGNED NOT NULL,
            `position` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `start_date` INT(11) UNSIGNED NULL DEFAULT NULL,
            `end_date` INT(11) UNSIGNED NULL DEFAULT NULL,
            `type` ENUM('date') NOT NULL DEFAULT 'date',
            `is_public` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
            `is_anonymous` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            `allow_maybe` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
            `allow_comments` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
            `evaluated` INT(11) UNSIGNED NULL DEFAULT NULL,
            `evaluated_uid` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL,
            PRIMARY KEY (`stoodle_id`)
        ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `stoodle_options` (
            `option_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `stoodle_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `value` VARCHAR(255) NOT NULL,
            `position` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `mkdate` INT(11) UNSIGNED NOT NULL,
            `chdate` INT(11) UNSIGNED NOT NULL,
            `result` TINYINT(1) UNSIGNED NULL DEFAULT NULL,
            PRIMARY KEY (`option_id`)
        ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `stoodle_answers` (
            `stoodle_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `mkdate` INT(11) UNSIGNED NOT NULL,
            PRIMARY KEY (`stoodle_id`, `user_id`)
        ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `stoodle_comments` (
            `comment_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `stoodle_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `comment` TEXT NOT NULL,
            `mkdate` INT(11) UNSIGNED NOT NULL,
            PRIMARY KEY (`comment_id`)
        ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `stoodle_selection` (
            `stoodle_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `option_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `maybe` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (`stoodle_id`, `user_id`, `option_id`)
        ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");
    }

    public function down()
    {
        DBManager::get()->query("DROP TABLE IF EXISTS `stoodle`, `stoodle_options`, `stoodle_answers`, `stoodle_comments`, `stoodle_selection`");
    }
}
