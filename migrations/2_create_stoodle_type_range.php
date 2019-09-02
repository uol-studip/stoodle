<?php
class CreateStoodleTypeRange extends Migration
{
    public function description()
    {
        return 'Creates new stoodle type "range" in database';
    }

    public function up()
    {
        $query = "ALTER TABLE `stoodle`
                  MODIFY COLUMN `type` ENUM('text', 'date', 'time', 'datetime', 'range') NOT NULL DEFAULT 'date'";
        DBManager::get()->exec($query);

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        $query = "ALTER TABLE `stoodle`
                  MODIFY COLUMN `type` ENUM('text', 'date', 'time', 'datetime') NOT NULL DEFAULT 'date'";
        DBManager::get()->exec($query);

        SimpleORMap::expireTableScheme();
    }
}
