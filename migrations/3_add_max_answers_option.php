<?php
class AddMaxAnswersOption extends Migration
{
    public function up()
    {
        $query = "ALTER TABLE `stoodle`
                  ADD COLUMN `max_answers` TINYINT(3) UNSIGNED NULL DEFAULT NULL AFTER `allow_comments`";
        DBManager::get()->exec($query);

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        $query = "ALTER TABLE `stoodle`
                  DROP COLUMN `max_answers`";
        DBManager::get()->exec($query);

        SimpleORMap::expireTableScheme();
    }
}
