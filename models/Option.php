<?php
namespace Stoodle;

use DBManager;
use PDO;
use SimpleORMap;

class Option extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'stoodle_options';

        $config['belongs_to']['stoodle'] = [
            'class_name'  => Stoodle::class,
            'foreign_key' => 'stoodle_id',
        ];

        $config['registered_callbacks']['before_delete'][] = function (Option $option) {
            Selection::deleteBySQL(
                'stoodle_id = ? AND option_id = ?',
                [$option->stoodle_id, $option->id]
            );
        };

        parent::configure($config);
    }

    public function getNewId()
    {
        return md5(uniqid('stoodle-option', true));
    }

    public function setResult($state)
    {
        $query = "UPDATE stoodle_options SET result = ? WHERE option_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            (int) $state, $this->option_id
        ]);
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
