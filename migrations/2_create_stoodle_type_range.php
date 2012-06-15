<?
class CreateStoodleTypeRange extends DBMigration
{
    function description()
    {
        return 'Creates new stoodle type "range" in database';
    }

    function up()
    {
        DBManager::get()->exec("ALTER TABLE `stoodle`
                                MODIFY COLUMN `type` ENUM('text', 'date', 'time', 'datetime', 'range') NOT NULL DEFAULT 'date'");
    }
    
    function down()
    {
        DBManager::get()->exec("ALTER TABLE `stoodle`
                                MODIFY COLUMN `type` ENUM('text', 'date', 'time', 'datetime') NOT NULL DEFAULT 'date'");
    }
}
