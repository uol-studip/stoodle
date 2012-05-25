<?
class Stoodle extends SimpleORMap
{
    public $options  = array();
    public $comments = array();

    public function __construct($id = null)
    {
        parent::__construct($id);

        if (!$this->isNew()) {
            foreach (StoodleOption::findByStoodle($id) as $option) {
                $this->options[$option->option_id] = $option->value;
            }
            $this->comments = StoodleComment::findByStoodle($id);
        }
    }

    public function store()
    {
        if ($this->isNew()) {
            $this->position = self::getMaxPosition($this->range_id);
        }

        return parent::store();
    }

    public function setOptions($opts)
    {
        $options = array();

        foreach ($opts as $id => $value) {
            if (preg_match('/^-\d+$/', $id)) {
                $id = StoodleOption::getNewId();
            }
            $options[$id] = $value;
        }

        $delete = array_diff(array_keys($this->options), array_keys($options));

        foreach ($delete as $id) {
            $option = new StoodleOption($id);
            $option->delete();
        }

        $position = 0;
        foreach ($options as $id => $value) {
            $option = new StoodleOption($id);
            $option->stoodle_id = $this->stoodle_id;
            $option->value = $value;
            $option->position = $position++;
            $option->store();
        }
    }

    public function delete()
    {
        if (!$this->isNew()) {
            foreach ($this->options as $option) {
                $option->delete();
            }
            foreach ($this->comments as $comment) {
                $comment->delete();
            }

            StoodleAnswer::removeByStoodleId($this->stoodle_id);
        }
        parent::delete();
    }

    public function getAnswers()
    {
        static $answers = null;

        if ($answers === null) {
            $answers = StoodleAnswer::getByStoodleId($this->stoodle_id);
        }

        return $answers;
    }
    
    public function getOptionsCount($maybe = false)
    {
        $count = array_fill_keys(array_keys($this->options), 0);
        foreach (self::getAnswers() as $user_id => $options) {
            foreach ($options[$maybe ? 'maybes' : 'selection'] as $option_id) {
                $count[$option_id] += 1;
            }
        }

        return $count;
    }

    public function userParticipated($user_id = null)
    {
        $answers = self::getAnswers();

        $user_id = $user_id ?: $GLOBALS['user']->id;
        return isset($answers[$user_id]);
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

    public static function findByRange($range_id)
    {
        $query = "SELECT stoodle_id FROM stoodle WHERE range_id = ? ORDER BY start_date, end_date";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($range_id));
        $ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        return array_map('self::find', $ids);
    }

    public static function loadByRange($range_id, $type = null)
    {
        $stoodles = self::findByRange($range_id);

        $result = array('past' => array(), 'present' => array(), 'future' => array());
        foreach ($stoodles as $stoodle) {
            $index = 'present';
            if ($stoodle->end_date && $stoodle->end_date < time()) {
                $index = 'past';
            } elseif ($stoodle->start_date && $stoodle->start_date > time()) {
                $index = 'future';
            }
            $result[$index][] = $stoodle;
        }
        return $result;
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

    public static function getMaxPosition($range_id)
    {
        $query = "SELECT MAX(position) FROM stoodle WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($range_id));
        return 1 + $statement->fetchColumn();
    }
}
