<?php
class Stoodle extends SimpleORMap
{
    public $options  = array();
    public $results  = array();
    public $comments = array();

    public function __construct($id = null)
    {
        parent::__construct($id);

        if (!$this->isNew()) {
            foreach (StoodleOption::findByStoodle($id) as $option) {
                $this->options[$option->option_id] = $option->value;
                if ($option->result) {
                    $this->results[$option->option_id] = $option->value;
                }
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
            foreach (array_keys($this->options) as $id) {
                $option = new StoodleOption($id);
                $option->delete();
            }
            foreach ($this->comments as $comment) {
                $comment->delete();
            }

            StoodleAnswer::removeByStoodleId($this->stoodle_id);
        }
        parent::delete();
    }

    protected $answers = null;
    public function getAnswers()
    {
        if ($this->isNew()) {
            return array();
        }
        
        if ($this->answers === null) {
            $this->answers = StoodleAnswer::getByStoodleId($this->stoodle_id);
        }

        return $this->answers;
    }
    
    public function getAnsweredOptions()
    {
        $answers = $this->getAnswers();
        
        $options = array();
        foreach ($answers as $user_id => $answer) {
            foreach ($answer['selection'] as $option_id) {
                @$options[$option_id][] = $user_id;
            }
            foreach ($answer['maybes'] as $option_id) {
                @$options[$option_id][] = $user_id;
            }
        }
        return $options;
    }
    
    public function getOptionsCount($maybe = false)
    {
        $count = array_fill_keys(array_keys($this->options), 0);
        foreach (self::getAnswers() as $user_id => $options) {
            if ($maybe === null) {
                $options = array_merge($options['maybes'], $options['selection']);
            } else if ($maybe) {
                $options = $options['maybes'];
            } else {
                $options = $options['selection'];
            }
            foreach ($options as $option_id) {
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

    public function formatOption($option_id, $raw = false)
    {
        $templates = array(
            'date'       => _('%d.%m.'),
            'datetime'   => _('%d.%m. %H:%M Uhr'),
            'time'       => _('%H:%M Uhr'),
            'short-time' => _('%H:%M')
        );
        
        $value = $this->options[$option_id];
        
        switch ($raw ?: $this->type) {
            case 'range':
                list($start, $end) = explode('-', $value);
                $same_day = (date('Ymd', $start) === date('Ymd', $end));
                return strftime($same_day ? $templates['date'] . ' ' . $templates['short-time'] : $templates['datetime'], $start)
                     . ' - '
                     . strftime($same_day ? $templates['time'] : $templates['datetime'], $end);
            case 'date':
            case 'time':
            case 'datetime':
                return strftime($templates[$this->type], $value);
            default:
                return $value;
        }
    }

    public static function findByRange($range_id)
    {
        $query = "SELECT stoodle_id FROM stoodle WHERE range_id = ? AND evaluated IS NULL ORDER BY start_date, end_date";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($range_id));
        $ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        return array_map('self::find', $ids);
    }

    public static function findEvaluatedByRange($range_id, $filters = array())
    {
        $conditions = '';
        foreach ($filters as $column => $value) {
            $conditions .= " AND $column = ?";
        }
        
        $query = "SELECT stoodle_id
                  FROM stoodle
                  WHERE range_id = ? AND evaluated IS NOT NULL {$conditions}
                  ORDER BY start_date, end_date";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array_merge(array($range_id), array_values($filters)));
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

    public static function getMaxPosition($range_id)
    {
        $query = "SELECT MAX(position) FROM stoodle WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($range_id));
        return 1 + $statement->fetchColumn();
    }
}
