<?
class StoodleOption extends SimpleORMap
{
    /**
     *
     * @param string $id primary key of table
     */
    function __construct($id = null)
    {
        $this->db_table = 'stoodle_options';
        parent::__construct($id);
    }

    public function getNewId()
    {
        return md5(uniqid('stoodle-option', true));
    }

    public function setResult($state)
    {
        $query = "UPDATE stoodle_options SET result = ? WHERE option_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array(
            (int)$state, $this->option_id
        ));
    }

    /**
     * returns new Stoodle instance for given id when found in db, else null
     * @param  string $id a stoodle id
     * @return mixed  a stoodle object or null
     **/
    public static function find($id)
    {
        return SimpleORMap::find(__CLASS__, $id);
    }

    public static function findByStoodle($stoodle_id)
    {
        $query = "SELECT option_id FROM stoodle_options WHERE stoodle_id = ? ORDER BY position";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($stoodle_id));
        $ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        return array_combine($ids, array_map('self::find', $ids));
    }

    /**
     * returns stoodle object for given id or null
     * the param could be a string, an assoc array containing primary key field
     * or an already matching object. In all these cases an object is returned
     *
     * @param mixed $id_or_object id as string, object or assoc array
     * @return Stoodle
     */
    public static function toObject($id_or_object)
    {
        return SimpleORMap::toObject(__CLASS__, $id_or_object);
    }
}
