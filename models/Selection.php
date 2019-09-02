<?php
namespace Stoodle;

class Selection extends \SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'stoodle_selection';

        parent::configure($config);
    }
}
