<?php
require_once __DIR__ . '/bootstrap.php';

/**
 * Stoodle.class.php
 *
 * Shameless doodle clone
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @version 2.3
 **/
class StoodlePlugin extends Stoodle\Plugin implements StandardPlugin, PrivacyPlugin
{
    const GETTEXT_DOMAIN = 'stoodle';

    use Stoodle\PluginLocalizationTrait;

    public function __construct()
    {
        parent::__construct();

        $this->initializeLocalization();

        NotificationCenter::addObserver($this, 'removeUserData', 'UserDataDidRemove');
    }

    public function getTitle()
    {
        return $this->_('Stoodle');
    }

    public function getTabNavigation($course_id)
    {
        $navigation = new Navigation($this->_('Stoodle'), PluginEngine::getURL($this, [], 'stoodle/index'));
        $navigation->setImage(Icon::create('assessment', Icon::ROLE_INFO_ALT));
        $navigation->setActiveImage(Icon::create('assessment', Icon::ROLE_INFO));

        if ($GLOBALS['perm']->have_studip_perm('tutor', $course_id)) {
            $navigation->addSubNavigation('index', new Navigation(
                $this->_('Übersicht'),
                PluginEngine::getLink($this, [], 'stoodle/index')
            ));
            $navigation->addSubNavigation('administration', new Navigation(
                $this->_('Verwaltung'),
                PluginEngine::GetLink($this, [], 'admin')
            ));
        }
        return ['stoodle' => $navigation];
    }

    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        $icon = Icon::create(
            'vote-stopped',
            Icon::ROLE_CLICKABLE,
            ['title' => $this->_('Stoodle: keine laufenden Umfragen')]
        );

        $navigation = new Navigation(
            $this->getTitle(),
            PluginEngine::getURL($this, [], 'stoodle/index')
        );
        $navigation->setImage($icon);

        $query = "SELECT COUNT(DISTINCT stoodle.stoodle_id) AS running,
                         SUM(stoodle_answers.user_id IS NULL) AS open,
                         SUM(GREATEST(stoodle.chdate, IFNULL(stoodle.start_date, 0)) > :last_visit) AS new
                  FROM stoodle
                  LEFT JOIN stoodle_answers USING (stoodle_id)
                  WHERE range_id = :course_id
                    AND stoodle_answers.user_id = :user_id
                    AND (start_date IS NULL OR start_date <= UNIX_TIMESTAMP())
                    AND (end_date IS NULL OR end_date >= UNIX_TIMESTAMP())";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':last_visit', $last_visit);
        $statement->bindValue(':course_id', $course_id);
        $statement->bindValue(':user_id', $user_id);
        $statement->execute();

        $data = $statement->fetch(PDO::FETCH_ASSOC);

        if ($data['running'] === 0) {
            return $navigation;
        }

        $message = sprintf(
            $this->_('%u laufende Umfrage(n)'),
            $data['running']
        );
        if ($data['open'] > 0) {
            $message .= ', ' . sprintf(
                $this->_('an %u noch nicht teilgenommen'),
                $data['open']
            );
        }

        if ($data['new'] > 0) {
            $icon = Icon::create('vote+new', Icon::ROLE_STATUS_RED, ['title' => $message]);
        } else {
            $icon = Icon::create('vote', Icon::ROLE_NAVIGATION, ['title' => $message]);
        }
        $navigation->setImage($icon);

        return $navigation;
    }

    public function getNotificationObjects($course_id, $since, $user_id)
    {
        return [];
    }

    public function getInfoTemplate($course_id)
    {
        return null;
    }

    /**
     * This method dispatches all actions.
     *
     * @param string   part of the dispatch path that was not consumed
     */
    public function perform($unconsumed_path)
    {
        PageLayout::setTitle(Context::get()->getFullname() . ' - ' . $this->getTitle());

        $manifest = $this->getMetadata();
        Helpbar::get()->addPlainText($this->_('Informationen'), $manifest['description']);

        $this->addStylesheet('assets/jquery-timepicker/jquery-ui-timepicker-addon.css');
        $this->addStylesheet('assets/stoodle.less');

        $this->addScript('assets/date-js/date-de-DE.js');
        $this->addScript('assets/stoodle.js');
        $this->addScript('assets/stoodle-config.js');

        $range_id = Request::option('cid', Context::get()->id);

        $app_path = $this->getPluginPath() . '/app';

        URLHelper::removeLinkParam('cid');
        $app_url = rtrim(PluginEngine::getURL($this, [], ''), '/');
        URLHelper::addLinkParam('cid', $range_id);

        $dispatcher = new Trails_Dispatcher($app_path, $app_url, 'stoodle');
        $dispatcher->current_plugin = $this;
        $dispatcher->range_id       = $range_id;
        $dispatcher->dispatch($unconsumed_path);
    }

    public function removeUserData($event, $user_id, $type)
    {
        Stoodle\Stoodle::deleteByUser_id($user_id);
        Stoodle\Selection::deleteByUser_id($user_id);
        Stoodle\Comment::deleteByUser_id($user_id);
        Stoodle\Answer::removeByUserId($user_id);
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $store object to store data into
     */
    public function exportUserData(StoredUserData $storage)
    {
        $options = [];
        $storage->addTabularData('Stoodle: Erstellte Umfragen', 'stoodle', Stoodle\Stoodle::findAndMapBySQL(
            function ($stoodle) use (&$options) {
                foreach ($stoodle->options as $option) {
                    $options[] = $option->toRawArray();
                }
                return $stoodle->toRawArray();
            },
            'user_id = ?',
            [$storage->user_id]
        ));
        $storage->addTabularData('Stoodle: Antwortmöglichkeiten der Umfragen', 'stoodle_options', $options);

        $storage->addTabularData('Stoodle: Abgegebene Kommentare', 'stoodle_comments', Stoodle\Comment::findAndMapBySQL(
            function ($comment) {
                return $comment->toRawArray();
            },
            'user_id = ?',
            [$storage->user_id]
        ));

        $storage->addTabularData(
            'Stoodle: Abgegebene Antworten 1',
            'stoodle_answers',
            DBManager::get()->fetchAll("SELECT * FROM stoodle_answers WHERE user_id = ?", [$storage->user_id])
        );
        $storage->addTabularData(
            'Stoodle: Abgegebene Antworten 2',
            'stoodle_selection',
            DBManager::get()->fetchAll("SELECT * FROM stoodle_selection WHERE user_id = ?", [$storage->user_id])
        );
    }
}
