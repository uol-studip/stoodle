<?
/**
 *
 */
class StoodleController extends StudipController
{
    /**
     *
     */
    public function before_filter(&$action, &$args)
    {
        $this->range_id = Request::option('cid');
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

        $layout = $GLOBALS['template_factory']->open('layouts/base');
        $layout->body_id = 'stoodle-plugin';
        $this->set_layout($layout);

        parent::before_filter($action, $args);

        $this->range_id = $this->dispatcher->range_id;
    }

    /**
     *
     */
    public function index_action()
    {
        $this->stoodles  = Stoodle::loadByRange($this->range_id);
        $this->evaluated = Stoodle::findEvaluatedByRange($this->range_id, array('is_public' => 1));

        $this->setInfoboxImage('infobox/administration');
        $this->addToInfobox(_('Informationen:'), _('Bitte beachten Sie, dass Auswertungen nicht-öffentlicher Umfragen nicht angezeigt werden.'), 'icons/16/black/info-circle');
    }

    /**
     *
     */
    public function display_action($id, $comments = null)
    {
        $this->stoodle  = new Stoodle($id);
        $this->comments = ($comments === 'all');

        if ($stoodle->start_date && $stoodle->state > time()) {
            PageLayout::postMessage(Messagebox::error(_('Die Umfrage wurde noch nicht gestartet. Sie können noch nicht teilnehmen.')));
            $this->redirect('stoodle');
            return;
        }
        if ($stoodle->end_date && $stoodle->end_date < time()) {
            PageLayout::postMessage(Messagebox::error(_('Die Umfrage ist bereits beendet. Sie können nicht mehr teilnehmen.')));
            $this->redirect('stoodle');
            return;
        }

        PageLayout::setTitle('Stoodle: ' . $this->stoodle->title);

        // extract users from stoodle and comments in order to avoid
        // unneccessary db traffic
        $users = array();

        foreach ($this->stoodle->getAnswers() as $user_id => $foo) {
            if (isset($users[$user_id])) {
                continue;
            }
            $users[$user_id] = User::find($user_id);
        }

        foreach ($this->stoodle->comments as $comment) {
            if (isset($users[$comment->user_id])) {
                continue;
            }
            $users[$comment->user_id] = User::find($comment->user_id);
        }
        $this->users = $users;

        $this->setInfoboxImage('infobox/administration');

        $infos = $this->get_template_factory()->render('stoodle/infobox', array('stoodle' => $this->stoodle));
        $this->addToInfobox(_('Informationen'), $infos, 'icons/16/black/info');

        $this->addToInfobox(_('Legende'), _('Zusage'), 'icons/16/green/accept');
        if ($this->stoodle->allow_maybe) {
            $this->addToInfobox(_('Legende'), _('Ungewiss'), 'icons/16/blue/question');
        }
        $this->addToInfobox(_('Legende'), _('Absage'), 'icons/16/red/decline');
    }

    public function participate_action($id)
    {
        $answer = new StoodleAnswer($id);

        $answer->clearSelection();
        foreach (Request::optionArray('selection') as $option_id => $state) {
            if ($state) {
                $answer->addToSelection($option_id, $state === 'maybe');
            }
        }

        $answer->store();

        PageLayout::postMessage(Messagebox::success(_('Ihre Teilnahme wurde gespeichert.')));
        $this->redirect($this->url_for('stoodle/display', $id));
    }

    /**
     *
     */
    public function comment_action($id)
    {
        $comment = new StoodleComment();
        $comment->stoodle_id = $id;
        $comment->user_id    = $GLOBALS['user']->id;
        $comment->comment    = trim(Request::get('comment'));

        if (empty($comment->comment)) {
            $message =  Messagebox::info(_('Sie können keinen leeren Kommentar hinzufügen.'));
        } else if ($comment->store() === false) {
            $message = Messagebox::error(_('Der Kommentar konnte nicht gespeichert werden.') . ' '
                                        ._('Bitte versuchen Sie es später noch einmal.'));
        } else {
            $message = Messagebox::success(_('Der Kommentar wurde hinzugefügt.'));
        }
        PageLayout::postMessage($message);
        $this->redirect('stoodle/' . $id . '#comments');
    }

    /**
     *
     */
    public function delete_comment_action($id)
    {
        $comment = new StoodleComment($id);
        $stoodle_id = $comment->stoodle_id;

        if ($comment->user_id != $GLOBALS['user']->id
            && !$GLOBALS['perm']->have_studip_perm('tutor', $this->range_id))
        {
            $message = Messagebox::error(_('Sie dürfen diesen Kommentar nicht löschen, da es nicht Ihrer ist'));
        } else if ($comment->delete() !== true) {
            $message = Messagebox::error(_('Fehler beim Löschen des Kommentars.') . ' '
                                        ._('Bitte versuchen Sie es später noch einmal.'));
        } else {
            $message = Messagebox::success(_('Der Kommentar wurde gelöscht.'));
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
            PageLayout::postMessage(Messagebox::error(_('Ungültige Stoodle-ID.')));
            $this->redirect('stoodle');
            return;
        }

        if (!$this->stoodle->evaluated) {
            PageLayout::postMessage(Messagebox::error(_('Die Umfrage ist noch nicht ausgewertet.')));
            $this->redirect('stoodle');
            return;
        }

        if (!$this->stoodle->is_public && !$GLOBALS['perm']->have_studip_perm('tutor', $this->range_id)) {
            PageLayout::postMessage(Messagebox::error(_('Die Umfrage ist nicht öffentlich. Sie haben keinen Zugriff auf diese Umfrage.')));
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

        $answers      = count($this->stoodle->getAnswers());
        $participants = count(Seminar::getInstance($this->range_id)->getMembers('autor'));
        $this->addToInfobox(_('Informationen'),
                            _('Laufzeit') . ': ' .
                            spoken_time($this->stoodle->end_date - ($this->stoodle->start_date ?: $this->stoodle->mkdate)),
                            'icons/16/black/date');
        $this->addToInfobox(_('Informationen'),
                            _('Start') . ': ' . date('d.m.Y H:i', $this->stoodle->start_date ?: $this->stoodle->mkdate));
        $this->addToInfobox(_('Informationen'),
                            _('Ende') . ': ' . date('d.m.Y H:i', $this->stoodle->end_date));
        $this->addToInfobox(_('Informationen'),
                            _('Teilnehmer') . ': ' . $answers . ' (' . round($participants ? 100 * $answers / $participants : 0, 2) . '%)',
                            'icons/16/black/stat');
        $this->addToInfobox(_('Informationen'),
                            sprintf(_('Die Umfrage war <em>%s</em> und <em>%s</em>.'),
                                    $this->stoodle->is_public ? _('öffentlich') : _('nicht öffentlich'),
                                    $this->stoodle->is_anonymous ? _('anonym') : _('nicht anonym')),
                            'icons/16/black/visibility-visible');
        if ($this->stoodle->allow_maybe) {
            $this->addToInfobox(_('Informationen'),
                                _('Eine Angabe von "vielleicht" war erlaubt.'),
                                'icons/16/black/question');
        }
        if ($this->stoodle->allow_comments) {
            $this->addToInfobox(_('Informationen'),
                                _('Kommentare waren erlaubt.'),
                                'icons/16/black/comment');
        }
        $this->setInfoboxImage('infobox/evaluation.jpg');
    }
}
