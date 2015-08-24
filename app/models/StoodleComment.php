<?php
class StoodleComment extends SimpleORMap
{
    /**
     *
     * @param string $id primary key of table
     */
    function __construct($id = null)
    {
        $this->db_table = 'stoodle_comments';
        parent::__construct($id);
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
