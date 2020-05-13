<?php
class AddMaxAnswerersOption extends Migration
{
    public function __construct($verbose = false)
    {
        parent::__construct($verbose);

        require_once __DIR__ . '/../lib/RouteMaps/Stoodle.php';
    }

    public function up()
    {
        // Adjust database
        $query = "ALTER TABLE `stoodle`
                  ADD COLUMN `max_answerers` TINYINT(3) UNSIGNED NULL DEFAULT NULL AFTER `max_answers`";
        DBManager::get()->exec($query);

        SimpleORMap::expireTableScheme();

        // Register routemaps
        $permissions = RESTAPI\ConsumerPermissions::get('global');
        $permissions->activateRouteMap(new Stoodle\RouteMaps\Stoodle());
    }

    public function down()
    {
        // Adjust database
        $query = "ALTER TABLE `stoodle`
                  DROP COLUMN `max_answerers`";
        DBManager::get()->exec($query);

        SimpleORMap::expireTableScheme();

        // Unregister routemaps
        $permissions = RESTAPI\ConsumerPermissions::get('global');
        $permissions->deactivateRouteMap(new Stoodle\RouteMaps\Stoodle());
    }
}
