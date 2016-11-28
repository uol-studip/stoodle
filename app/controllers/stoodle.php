<?php
use Stoodle\Answer;
use Stoodle\Comment;
use Stoodle\Stoodle;

/**
 *
 */
class StoodleController extends StudipController
{
    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
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

        if ($stoodle->start_date && $stoodle->state > time()) {
            PageLayout::postMessage(MessageBox::error(_('Die Umfrage wurde noch nicht gestartet. Sie können noch nicht teilnehmen.')));
            $this->redirect('stoodle');
            return;
        }
        if ($stoodle->end_date && $stoodle->end_date < time()) {
            PageLayout::postMessage(MessageBox::error(_('Die Umfrage ist bereits beendet. Sie können nicht mehr teilnehmen.')));
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

        PageLayout::postMessage(MessageBox::success(_('Ihre Teilnahme wurde gespeichert.')));
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
            $message =  MessageBox::info(_('Sie können keinen leeren Kommentar hinzufügen.'));
        } else if ($comment->store() === false) {
            $message = MessageBox::error(_('Der Kommentar konnte nicht gespeichert werden.') . ' '
                                        ._('Bitte versuchen Sie es später noch einmal.'));
        } else {
            $message = MessageBox::success(_('Der Kommentar wurde hinzugefügt.'));
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
            $message = MessageBox::error(_('Sie dürfen diesen Kommentar nicht löschen, da es nicht Ihrer ist'));
        } else if (!$comment->delete()) {
            $message = MessageBox::error(_('Fehler beim Löschen des Kommentars.') . ' '
                                        ._('Bitte versuchen Sie es später noch einmal.'));
        } else {
            $message = MessageBox::success(_('Der Kommentar wurde gelöscht.'));
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
            PageLayout::postMessage(MessageBox::error(_('Ungültige Stoodle-ID.')));
            $this->redirect('stoodle');
            return;
        }

        if (!$this->stoodle->evaluated) {
            PageLayout::postMessage(MessageBox::error(_('Die Umfrage ist noch nicht ausgewertet.')));
            $this->redirect('stoodle');
            return;
        }

        if (!$this->stoodle->is_public && !$GLOBALS['perm']->have_studip_perm('tutor', $this->range_id)) {
            PageLayout::postMessage(MessageBox::error(_('Die Umfrage ist nicht öffentlich. Sie haben keinen Zugriff auf diese Umfrage.')));
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
            $widget->setTitle(_('Informationen'));
            $widget->addElement($this->sidebarElement(_('Bitte beachten Sie, dass Auswertungen nicht-öffentlicher Umfragen nicht angezeigt werden.'), $this->plugin->getSidebarIcon('info-circle', 'info')));
            $sidebar->addWidget($widget);
        } elseif ($action === 'display') {
            // General info
            $widget = new ListWidget();
            $widget->setTitle(_('Informationen'));

            $start = sprintf('%s: %s', _('Start'), $stoodle->start_date ? strtotime('%x', $stoodle->start_date) : _('offen'));
            $widget->addElement($this->sidebarElement($start, $this->plugin->getSidebarIcon('info', 'info')));

            $end = sprintf('%s: %s', _('Ende'), $stoodle->end_date ? strtotime('%x', $stoodle->end_date) : _('offen'));
            $widget->addElement($this->sidebarElement($end));

            $widget->addElement($this->sidebarElement(
                $stoodle->is_public
                    ? _('Die Ergebnisse der Umfrage sind öffentlich einsehbar.')
                    : _('Die Ergebnisse der Umfrage sind nicht öffentlich einsehbar.')
            ));
            if ($stoodle->is_anonymous) {
                $widget->addElement($this->sidebarElement(_('Die Umfrage ist anonym.')));
            }
            $sidebar->addWidget($widget);

            // Legend
            $legend = new ListWidget();
            $legend->setTitle(_('Legende'));

            $legend->addElement($this->sidebarElement(_('Zusage'), $this->plugin->getSidebarIcon('accept', 'status-green')));
            if ($this->stoodle->allow_maybe) {
                $legend->addElement($this->sidebarElement(_('Ungewiss'), $this->plugin->getSidebarIcon('question', 'clickable')));
            }
            $legend->addElement($this->sidebarElement(_('Absage'), $this->plugin->getSidebarIcon('decline', 'attention')));
        
            $sidebar->addWidget($legend);
        } elseif ($action === 'result') {
            $answers      = count($this->stoodle->getAnswers());
            $participants = count(Seminar::getInstance($this->range_id)->getMembers('autor'));

            $widget = new ListWidget();
            $widget->setTitle(_('Informationen'));
            
            $widget->addElement($this->sidebarElement(
                spoken_time($stoodle->end_date - ($stoodle->start_date ?: $stoodle->mkdate)),
                $this->plugin->getSidebarIcon('date', 'info')
            ));
            
            $start = sprintf('%s: %s', _('Start'),
                             strtotime('%x %X', $stoodle->start_date ?: $stoodle->mkdate));
            $widget->addElement($this->sidebarElement($start));
            
            $end = sprintf('%s: %s', _('Ende'),
                             strtotime('%x %X', $stoodle->end_date));
            $widget->addElement($this->sidebarElement($end));

            $members = sprintf('%s: %u (%.2f%%)', _('Teilnehmer'), $answers,
                               round($participants ? 100 * $answers / $participants : 0, 2));
            $widget->addElement($this->sidebarElement($members, $this->plugin->getSidebarIcon('stat', 'info')));

            $info = sprintf(_('Die Umfrage war <em>%s</em> und <em>%s</em>.'),
                            $stoodle->is_public ? _('öffentlich') : _('nicht öffentlich'),
                            $stoodle->is_anonymous ? _('anonym') : _('nicht anonym'));
            $widget->addElement($this->sidebarElement($info, $this->plugin->getSidebarIcon('visibility-visible', 'info')));
    
            if ($stoodle->allow_maybe) {
                $widget->addElement($this->sidebarElement(
                    _('Eine Angabe von "vielleicht" war erlaubt.'),
                    $this->plugin->getSidebarIcon('question', 'info')
                ));
            }
            if ($this->stoodle->allow_comments) {
                $widget->addElement($this->sidebarElement(
                    _('Kommentare waren erlaubt.'),
                    $this->plugin->getSidebarIcon('comment', 'info')
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
