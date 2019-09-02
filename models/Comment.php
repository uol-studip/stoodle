<?php
namespace Stoodle;

use DBManager;
use PDO;
use SimpleORMap;

class Comment extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'stoodle_comments';

        $config['belongs_to']['stoodle'] = [
            'class_name'  => Stoodle::class,
            'foreign_key' => 'stoodle_id',
        ];

        parent::configure($config);
    }

    public static function findByStoodle($stoodle_id)
    {
        $temp = self::findBySQL('stoodle_id = ? ORDER BY mkdate DESC');

        $result = [];
        foreach ($temp as $row) {
            $result[$id] = $row;
        }

        return $result;
    }
}
