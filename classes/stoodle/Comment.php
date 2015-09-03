<?php
namespace Stoodle;

use DBManager;
use PDO;
use SimpleORMap;

class Comment extends SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'stoodle_comments';
        
        parent::configure($config);
    }
    
    public static function findByStoodle($stoodle_id)
    {
        $query = "SELECT comment_id FROM stoodle_comments WHERE stoodle_id = ? ORDER BY mkdate DESC";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($stoodle_id));
        $ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        return empty($ids) ? array() : array_combine($ids, array_map('self::find', $ids));
    }
}
