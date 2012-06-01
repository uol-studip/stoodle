<?
/**
 * 
 */
class AdminController extends StudipController
{
    /**
     * 
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setTitle(_('Stoodle'));
        Navigation::activateItem('/course/stoodle/administration');

        $layout = $GLOBALS['template_factory']->open('layouts/base');
        $layout->body_id = 'stoodle-plugin';
        $this->set_layout($layout);

        $this->range_id = $this->dispatcher->range_id;
    }

    /**
     * 
     */
    public function index_action()
    {
        $this->stoodles  = Stoodle::findByRange($this->range_id);
        $this->evaluated = Stoodle::findEvaluatedByRange($this->range_id);

        $create = sprintf('<a href="%s">%s</a>',
                          $this->url_for('admin/edit'),
                          _('Neue Umfrage erstellen'));

        $this->setInfoboxImage('infobox/administration');
        $this->addToInfobox(_('Aktionen'), $create, 'icons/16/black/plus');
    }

    /**
     * 
     */
    public function edit_action($id = null)
    {
        $layout = $GLOBALS['template_factory']->open('layouts/base_without_infobox');
        $layout->body_id = 'stoodle-plugin';
        $this->set_layout($layout);

        $this->id = $id;
        
        $stoodle = new Stoodle($id);

        $this->title          = trim(Request::get('title', $stoodle->title));
        $this->description    = trim(Request::get('description', $stoodle->description)) ?: null;
        $this->type           = Request::option('type', $stoodle->type ?: 'date');
        $this->start_date     = Request::int('start_date', $stoodle->start_date) ?: null;
        $this->end_date       = Request::int('end_date', $stoodle->end_date) ?: null;
        $this->is_public      = Request::int('is_public', $stoodle->isNew() ? 1 : $stoodle->is_public);
        $this->is_anonymous   = Request::int('is_anonymous', $stoodle->isNew() ? 0 : $stoodle->is_anonymous);
        $this->allow_maybe    = Request::int('allow_maybe', $stoodle->isNew() ? 0 : $stoodle->allow_maybe);
        $this->allow_comments = Request::int('allow_comments', $stoodle->isNew() ? 1 : $stoodle->allow_comments);
        $this->options        = array_filter(Request::getArray('options')) ?: $stoodle->options ?: array(StoodleOption::getNewId() => '');
        $this->options_count  = $stoodle->getOptionsCount(null);
        $this->answers        = $stoodle->getAnswers();

        if (Request::submitted('move')) {
            list($direction, $index) = each(Request::getArray('move'));

            $keys   = array_keys($this->options);
            $key = array_splice($keys, $index, 1);
            array_splice($keys, $direction == 'up' ? $index - 1 : $index + 1, 0, $key);

            $values = array_values($this->options);
            $value = array_splice($values, $index, 1);
            array_splice($values, $direction == 'up' ? $index - 1 : $index + 1, 0, $value);
            
            $this->options = array_combine($keys, $values);
        } else if (Request::submitted('remove')) {
            $index = Request::int('remove');
            unset($this->options[$index]);
            $this->options = array_merge($this->options);
        } else if (Request::submitted('add')) {
            $this->focussed = count($this->options);
            $this->options[StoodleOption::getNewId()] = '';
        } else if (Request::submitted('store')) {
            $errors = array();

            if (empty($this->title)) {
                $errors[] = _('Bitte geben Sie einen Titel an');
            }
            if ($this->start_date && $this->end_date && $this->start_date > $this->end_date) {
                $errors[] = _('Das Enddatum muss vor dem Startdatum liegen.');
            }
            if (count($this->options) < 1) {
                $errors[] = _('Bitte geben Sie mindestens eine Antwortmöglichkeit ein.');
            }

            if (empty($errors)) {
                $new = $stoodle->isNew();
                
                $stoodle->title          = $this->title;
                $stoodle->description    = $this->description;
                $stoodle->type           = $this->type;
                $stoodle->start_date     = $this->start_date;
                $stoodle->end_date       = $this->end_date;
                $stoodle->is_public      = $this->is_public;
                $stoodle->is_anonymous   = $this->is_anonymous;
                $stoodle->allow_maybe    = $this->allow_maybe;
                $stoodle->allow_comments = $this->allow_comments;
                $stoodle->range_id       = $this->range_id;
                $stoodle->user_id        = $GLOBALS['user']->id;

                $stoodle->store();
                $stoodle->setOptions($this->options);

                $message = $new
                         ? _('Der Eintrag wurde erfolgreich erstellt.')
                         : _('Der Eintrag wurde erfolgreich bearbeitet.');
                PageLayout::postMessage(Messagebox::success($message));
                $this->redirect('admin');
                return;
            } else {
                PageLayout::postMessage(Messagebox::error(_('Es sind Fehler aufgetreten:'), $errors));
            }
        }

        if (empty($this->options)) {
            $this->options = array('');
        }
    }
    
    public function stop_action($id)
    {
        $stoodle = new Stoodle($id);
        $stoodle->end_date = time() - 1;
        $stoodle->store();
        
        PageLayout::postMessage(Messagebox::success(_('Die Umfrage wurde beendet.')));
        $this->redirect('admin');
    }

    public function resume_action($id)
    {
        $stoodle = new Stoodle($id);
        $stoodle->end_date = null;
        $stoodle->store();
        
        PageLayout::postMessage(Messagebox::success(_('Die Umfrage wurde fortgesetzt.')));
        $this->redirect('admin');
    }

    public function evaluate_action($id)
    {
        $stoodle = new Stoodle($id);

        if (Request::submitted('evaluate')) {
            $stoodle->evaluated     = time();
            $stoodle->evaluated_uid = $GLOBALS['user']->id;
            $stoodle->store();

            $results = Request::optionArray('result');
            foreach (array_keys($stoodle->options) as $option_id) {
                $option = new StoodleOption($option_id);
                $option->setResult(in_array($option_id, $results));
            }

            if (Request::int('create_appointments') && in_array($stoodle->type, words('datetime range'))) {
            }

            Pagelayout::postMessage(Messagebox::success('Die Umfrage wurde ausgewertet.'));
            $this->redirect('admin');
        }
        
        $this->stoodle        = $stoodle;
        $this->selections     = $stoodle->getOptionsCount();
        $this->maybes         = $stoodle->getOptionsCount(true);
        $this->max            = max($stoodle->getOptionsCount(null));
        $this->participants   = count(Seminar::getInstance($this->range_id)->getMembers('autor'));

        $this->selections_max = max($this->selections);
        $this->maybes_max     = max($this->maybes);
    }

    /**
     * 
     */
    public function delete_action($id)
    {
        $stoodle = new Stoodle($id);
        $stoodle->delete();

        PageLayout::postMessage(Messagebox::success(_('Die Umfrage wurde erfolgreich gelöscht.')));
        $this->redirect('admin');
    }
}
