<?php
use Stoodle\Answer;
use Stoodle\Comment;
use Stoodle\Stoodle;

/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class StoodleController extends StudipController
{
    /**
     * Constructs the controller and provide translations methods.
     *
     * @param object $dispatcher
     * @see https://stackoverflow.com/a/12583603/982902 if you need to overwrite
     *      the constructor of the controller
     */
    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);

        $this->plugin = $dispatcher->current_plugin;

        // Localization
        $this->_ = function ($string) use ($dispatcher) {
            return call_user_func_array(
                [$dispatcher->current_plugin, '_'],
                func_get_args()
            );
        };

        $this->_n = function ($string0, $tring1, $n) use ($dispatcher) {
            return call_user_func_array(
                [$dispatcher->current_plugin, '_n'],
                func_get_args()
            );
        };
    }

    /**
     * Intercepts all non-resolvable method calls in order to correctly handle
     * calls to _ and _n.
     *
     * @param string $method
     * @param array  $arguments
     * @return mixed
     * @throws RuntimeException when method is not found
     */
    public function __call($method, $arguments)
    {
        $variables = get_object_vars($this);
        if (isset($variables[$method]) && is_callable($variables[$method])) {
            return call_user_func_array($variables[$method], $arguments);
        }
        throw new RuntimeException("Method {$method} does not exist");
    }

    /**
     *
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->range_id = $this->dispatcher->range_id;
        if (!$this->range_id) {
            throw new CheckObjectException();
        }

        if (preg_match('/^[0-9a-f]{32}$/', $action)) {
            array_unshift($args, $action);
            $action = 'display';
        }

        if (Navigation::hasItem('/course/stoodle/index')) {
            Navigation::activateItem('/course/stoodle/index');
        } else {
            Navigation::activateItem('/course/stoodle');
        }

        $layout = $this->get_template_factory()->open('layout.php');
        $layout->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        $this->set_layout($layout);

        $this->range_id = $this->dispatcher->range_id;
    }

    /**
     *
     */
    public function index_action()
    {
        $this->stoodles  = Stoodle::loadByRange($this->range_id);
        $this->evaluated = Stoodle::findEvaluatedByRange($this->range_id, array('is_public' => 1));

        $this->setupSidebar('index');
    }

    /**
     *
     */
    public function display_action($id, $comments = null)
    {
        $this->stoodle  = new Stoodle($id);
        $this->comments = ($comments === 'all');

        if ($this->stoodle->start_date && $this->stoodle->start_date > time()) {
            PageLayout::postError($this->_('Die Umfrage wurde noch nicht gestartet. Sie können noch nicht teilnehmen.'));
            $this->redirect('stoodle');
            return;
        }
        if ($this->stoodle->end_date && $this->stoodle->end_date < time()) {
            PageLayout::postError($this->_('Die Umfrage ist bereits beendet. Sie können nicht mehr teilnehmen.'));
            $this->redirect('stoodle');
            return;
        }

        PageLayout::setTitle('Stoodle: ' . $this->stoodle->title);
        $this->setupSidebar('display', $this->stoodle);
    }

    public function participate_action($id)
    {
        $answer = new Answer($id);

        $answer->clearSelection();
        foreach (Request::optionArray('selection') as $option_id => $state) {
            if ($state) {
                $answer->addToSelection($option_id, $state === 'maybe');
            }
        }

        $answer->store();

        PageLayout::postSuccess($this->_('Ihre Teilnahme wurde gespeichert.'));
        $this->redirect($this->url_for('stoodle/display', $id));
    }

    /**
     *
     */
    public function comment_action($id)
    {
        $comment = new Comment();
        $comment->stoodle_id = $id;
        $comment->user_id    = $GLOBALS['user']->id;
        $comment->comment    = trim(Request::get('comment'));

        if (empty($comment->comment)) {
            $message =  MessageBox::info(
                $this->_('Sie können keinen leeren Kommentar hinzufügen.')
            );
        } elseif ($comment->store() === false) {
            $message = MessageBox::error(
                $this->_('Der Kommentar konnte nicht gespeichert werden.') . ' '
                . $this->_('Bitte versuchen Sie es später noch einmal.')
            );
        } else {
            $message = MessageBox::success(
                $this->_('Der Kommentar wurde hinzugefügt.')
            );
        }
        PageLayout::postMessage($message);
        $this->redirect('stoodle/' . $id . '#comments');
    }

    /**
     *
     */
    public function delete_comment_action($id)
    {
        $comment = Comment::find($id);
        $stoodle_id = $comment->stoodle_id;

        if ($comment->user_id != $GLOBALS['user']->id
            && !$GLOBALS['perm']->have_studip_perm('tutor', $this->range_id))
        {
            $message = MessageBox::error(
                $this->_('Sie dürfen diesen Kommentar nicht löschen, da es nicht Ihrer ist')
            );
        } elseif (!$comment->delete()) {
            $message = MessageBox::error(
                $this->_('Fehler beim Löschen des Kommentars.') . ' '
                . $this->_('Bitte versuchen Sie es später noch einmal.')
            );
        } else {
            $message = MessageBox::success(
                $this->_('Der Kommentar wurde gelöscht.')
            );
        }

        PageLayout::postMessage($message);
        $this->redirect('stoodle/' . $stoodle_id . '#comments');
    }

    /**
     *
     */
    public function result_action($id)
    {
        $this->stoodle = new Stoodle($id);
        if (!$this->stoodle) {
            PageLayout::postError($this->_('Ungültige Stoodle-ID.'));
            $this->redirect('stoodle');
            return;
        }

        if (!$this->stoodle->evaluated) {
            PageLayout::postError($this->_('Die Umfrage ist noch nicht ausgewertet.'));
            $this->redirect('stoodle');
            return;
        }

        if (!$this->stoodle->is_public && !$GLOBALS['perm']->have_studip_perm('tutor', $this->range_id)) {
            PageLayout::postError(
                $this->_('Die Umfrage ist nicht öffentlich. Sie haben keinen Zugriff auf diese Umfrage.')
            );
            $this->redirect('stoodle');
            return;
        }

        $this->selections     = $this->stoodle->getOptionsCount();
        $this->maybes         = $this->stoodle->getOptionsCount(true);
        $this->max            = max($this->stoodle->getOptionsCount(null));

        $this->selections_max = max($this->selections);
        $this->maybes_max     = max($this->maybes);

        foreach ($this->stoodle->comments as $comment) {
            if (isset($users[$comment->user_id])) {
                continue;
            }
            $users[$comment->user_id] = User::find($comment->user_id);
        }
        $this->users    = $users;
        $this->comments = true;

        $this->setupSidebar('result', $this->stoodle);
    }

    /**
     *
     */
    protected function setupSidebar($action, $stoodle = null)
    {
        $sidebar = Sidebar::get();

        if ($action === 'index') {
            $widget = new ListWidget();
            $widget->setTitle($this->_('Informationen'));
            $widget->addElement($this->sidebarElement(
                $this->_('Bitte beachten Sie, dass Auswertungen nicht-öffentlicher Umfragen nicht angezeigt werden.'),
                $this->plugin->getIcon('info-circle', 'info')
            ));
            $sidebar->addWidget($widget);
        } elseif ($action === 'display') {
            // General info
            $widget = new ListWidget();
            $widget->setTitle($this->_('Informationen'));

            $start = sprintf(
                '%s: %s',
                $this->_('Start'),
                $stoodle->start_date ? strftime('%x', $stoodle->start_date) : $this->_('offen')
            );
            $widget->addElement($this->sidebarElement(
                $start,
                $this->plugin->getIcon('info', 'info')
            ));

            $end = sprintf(
                '%s: %s',
                $this->_('Ende'),
                $stoodle->end_date ? strftime('%x', $stoodle->end_date) : $this->_('offen')
            );
            $widget->addElement($this->sidebarElement($end));

            $widget->addElement($this->sidebarElement(
                $stoodle->is_public
                    ? $this->_('Die Ergebnisse der Umfrage sind öffentlich einsehbar.')
                    : $this->_('Die Ergebnisse der Umfrage sind nicht öffentlich einsehbar.')
            ));
            if ($stoodle->is_anonymous) {
                $widget->addElement($this->sidebarElement($this->_('Die Umfrage ist anonym.')));
            }
            $sidebar->addWidget($widget);

            // Legend
            $legend = new ListWidget();
            $legend->setTitle($this->_('Legende'));

            $legend->addElement($this->sidebarElement(
                $this->_('Zusage'),
                $this->plugin->getIcon('accept', 'status-green')
            ));
            if ($this->stoodle->allow_maybe) {
                $legend->addElement($this->sidebarElement(
                    $this->_('Ungewiss'),
                    $this->plugin->getIcon('question', 'clickable')
                ));
            }
            $legend->addElement($this->sidebarElement(
                $this->_('Absage'),
                $this->plugin->getIcon('decline', 'status-red')
            ));

            $sidebar->addWidget($legend);
        } elseif ($action === 'result') {
            $answers      = count($this->stoodle->getAnswers());
            $participants = count($this->stoodle->getRangeMembers());

            $widget = new ListWidget();
            $widget->setTitle($this->_('Informationen'));

            $widget->addElement($this->sidebarElement(
                spoken_time($stoodle->end_date - ($stoodle->start_date ?: $stoodle->mkdate)),
                $this->plugin->getIcon('date', 'info')
            ));

            $start = sprintf(
                '%s: %s',
                $this->_('Start'),
                strtotime('%x %H:%M', $stoodle->start_date ?: $stoodle->mkdate)
            );
            $widget->addElement($this->sidebarElement($start));

            $end = sprintf(
                '%s: %s',
                $this->_('Ende'),
                strtotime('%x %H:%M', $stoodle->end_date)
            );
            $widget->addElement($this->sidebarElement($end));

            $members = sprintf(
                '%s: %u (%.2f%%)',
                $this->_('Teilnehmer'),
                $answers,
                round($participants ? 100 * $answers / $participants : 0, 2)
            );
            $widget->addElement($this->sidebarElement(
                $members,
                $this->plugin->getIcon('stat', 'info')
            ));

            $info = sprintf(
                $this->_('Die Umfrage war <em>%s</em> und <em>%s</em>.'),
                $stoodle->is_public ? $this->_('öffentlich') : $this->_('nicht öffentlich'),
                $stoodle->is_anonymous ? $this->_('anonym') : $this->_('nicht anonym')
            );
            $widget->addElement($this->sidebarElement(
                $info,
                $this->plugin->getIcon('visibility-visible', 'info')
            ));

            if ($stoodle->allow_maybe) {
                $widget->addElement($this->sidebarElement(
                    $this->_('Eine Angabe von "vielleicht" war erlaubt.'),
                    $this->plugin->getIcon('question', 'info')
                ));
            }
            if ($this->stoodle->allow_comments) {
                $widget->addElement($this->sidebarElement(
                    $this->_('Kommentare waren erlaubt.'),
                    $this->plugin->getIcon('comment', 'info')
                ));
            }
            $sidebar->addWidget($widget);
        }
    }

    /**
     *
     */
    protected function sidebarElement($content, $icon = null)
    {
        $element = new WidgetElement($content);
        $element->icon = $icon;
        return $element;
    }
}
