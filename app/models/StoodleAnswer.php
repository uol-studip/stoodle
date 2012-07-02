<?
class StoodleAnswer
{
    public static function getByStoodleId($stoodle_id)
    {
        static $result = array();

        if (!isset($result[$stoodle_id])) {
            $result[$stoodle_id] = array();

            $query = "SELECT user_id FROM stoodle_answers WHERE stoodle_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($stoodle_id));
            $user_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

            foreach ($user_ids as $user_id) {
                $answer = new self($stoodle_id, $user_id);

                $result[$stoodle_id][$user_id] = array(
                    'selection' => $answer->getSelection(),
                    'maybes'    => $answer->getMaybes(),
                );
            }
        }

        return $result[$stoodle_id];
    }

    public static function removeByStoodleId($stoodle_id)
    {
        $query = "DELETE FROM stoodle_answers WHERE stoodle_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($stoodle_id));

        $query = "DELETE FROM stoodle_selection WHERE stoodle_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($stoodle_id));
    }
    
    protected $stoodle_id;
    protected $user_id;
    protected $selection = array();
    protected $maybes = array();
    
    public function __construct($stoodle_id, $user_id = null)
    {
        $this->stoodle_id = $stoodle_id;
        $this->user_id = $user_id ?: $GLOBALS['user']->id;

        $this->loadSelection();
    }
    
    public function getSelection()
    {
        return $this->selection;
    }
    
    public function getMaybes()
    {
        return $this->maybes;
    }

    private function loadSelection()
    {
        $this->clearSelection();

        $query = "SELECT option_id, maybe FROM stoodle_selection WHERE stoodle_id = ? AND user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($this->stoodle_id, $this->user_id));

        foreach ($statement as $row) {
            $this->addToSelection($row['option_id'], $row['maybe']);
        }
    }

    public function clearSelection()
    {
        $this->selection = $this->maybes = array();
    }
    
    public function addToSelection($option_id, $maybe = false)
    {
        if ($maybe) {
            $this->maybes[] = $option_id;
        } else {
            $this->selection[] = $option_id;
        }
    }

    public function store()
    {
        // Remove old selection
        $this->removeSelection();
        
        // Store selection
        $query = "INSERT INTO stoodle_selection (stoodle_id, user_id, option_id, maybe) VALUES (?, ?, ?, ?)";
        $statement = DBManager::get()->prepare($query);

        foreach ($this->selection as $option_id) {
            $statement->execute(array($this->stoodle_id, $this->user_id, $option_id, 0));
        }
        foreach ($this->maybes as $option_id) {
            $statement->execute(array($this->stoodle_id, $this->user_id, $option_id, 1));
        }

        // Store answer
        $query = "INSERT IGNORE INTO stoodle_answers (stoodle_id, user_id, mkdate) VALUES (?, ?, UNIX_TIMESTAMP())";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($this->stoodle_id, $this->user_id));
    }
    
    private function removeSelection()
    {
        $query = "DELETE FROM stoodle_selection WHERE stoodle_id = ? AND user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($this->stoodle_id, $this->user_id));
    }
}
