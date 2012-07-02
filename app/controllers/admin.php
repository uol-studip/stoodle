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

        $layout_file = in_array($action, words('edit evaluate'))
                     ? 'layouts/base_without_infobox'
                     : 'layouts/base';
        $layout = $GLOBALS['template_factory']->open($layout_file);
        $layout->body_id = 'stoodle-plugin';
        $this->set_layout($layout);

        $this->range_id = $this->dispatcher->range_id;

        if (!$GLOBALS['perm']->have_studip_perm('tutor', $this->range_id)) {
            throw new AccessDeniedException(_('Sie haben keinen Zugriff auf diesen Bereich.'));
        }
        
        // We need this since the messaging section of Stud.IP still uses the old
        // mechanism to display messages
        if (!empty($_SESSION['sms_msg'])) {
            $msgs = array_chunk(explode('�', $_SESSION['sms_msg']), 2);
            foreach ($msgs as $msg) {
                if ($msg[0] === 'msg') {
                    $type = 'success';
                } elseif ($msg[0] === 'error') {
                    $type = 'error';
                } else {
                    $type = 'info';
                }
                PageLayout::postMessage(Messagebox::$type($msg[1]));
            }
            unset($_SESSION['sms_msg']);
        }
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
        // Integrate addiotional
        $this->options        = $this->extractOptions($stoodle->options, $this->type === 'range');
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

            $this->options = $this->reduceOptions($this->options, $invalid);
            if (count($this->options) < 1) {
                $errors[] = _('Bitte geben Sie mindestens eine Antwortm�glichkeit ein.');
            }
            
            if (count($invalid) > 0) {
                $errors[] = _('Sie haben nicht alle Zeitspannen g�ltig ausgef�llt (fehlendes Start- bzw. Enddatum).');
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

                // echo '<pre>';
                // var_dump($this->options);
                // die;
                $stoodle->store();
                $stoodle->setOptions($this->options);

                $message = $new
                         ? _('Der Eintrag wurde erfolgreich erstellt.')
                         : _('Der Eintrag wurde erfolgreich bearbeitet.');
                PageLayout::postMessage(Messagebox::success($message));
                $this->redirect('admin');
            } else {
                PageLayout::postMessage(Messagebox::error(_('Es sind Fehler aufgetreten:'), $errors));
            }
        }

        if (empty($this->options)) {
            $this->options = array('');
        }
        $this->stoodle = $stoodle;
    }

    private function extractOptions($defaults = array(), $include_additional = false)
    {
        $options = Request::getArray('options') ?: $defaults ?: array(StoodleOption::getNewId() => '');

        if ($include_additional && isset($_REQUEST['options'], $_REQUEST['additional'])) {
            $additional = Request::getArray('additional');
            foreach ($options as $id => $value) {
                if (isset($additional[$id]) and $additional[$id] and $additional[$id] < $value) {
                    $value = $additional[$id] . '-' . $value;
                } else {
                    $value .= '-' . $additional[$id];
                }
                $options[$id] = $value;
            }
        }

        return $options;
    }
    
    private function reduceOptions($options, &$invalid = array())
    {
        $result = $invalid = array();
        foreach ($options as $id => $option) {
            if (empty($option) || ($this->type === 'range' && $option === '-')) {
                continue;
            }
            if ($this->type === 'range' && ($option[0] === '-' || $option[strlen($option) - 1] === '-')) {
                $invalid[] = $id;
            }
            $result[$id] = $option;
        }
        return $result;
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

        if ($stoodle->evaluated !== null) {
            PageLayout::postMessage(Messagebox::error(_('Die Umfrage wurde bereits ausgewertet.')));
            $this->redirect('admin');
        }

        if (Request::submitted('evaluate')) {
            $details = array();

            $stoodle->evaluated     = time();
            $stoodle->evaluated_uid = $GLOBALS['user']->id;
            $stoodle->store();

            $results = Request::optionArray('result');
            foreach (array_keys($stoodle->options) as $option_id) {
                $option = new StoodleOption($option_id);
                $option->setResult(in_array($option_id, $results));
            }

            if (!empty($results) && Request::int('create_appointments')
                && in_array($stoodle->type, words('datetime range')))
            {
                $target = Request::option('appointments_for');
                if ($target === 'valid') {
                    $answers = $stoodle->getAnswers();
                    $targets = array();

                    foreach ($answers as $user_id => $answer) {
                        $temp = array_merge($answer['selection'], $answer['maybes']);
                        if (count(array_intersect($temp, $results))) {
                            $targets[] = $user_id;
                        }
                    }
                } elseif ($target === 'stoodle') {
                    $answers = $stoodle->getAnswers();
                    $targets = array_keys($answers);
                } else {
                    $seminar = Seminar::GetInstance($this->range_id);
                    $targets = array();
                    foreach (words('autor tutor dozent') as $type) {
                        $temp    = $seminar->getMembers($type);
                        $ids     = array_keys($temp);
                        $targets = array_merge($targets, $ids);
                    }
                }

                $duration = round(Request::float('appointment_duration') * 60 * 60);

                foreach ($results as $option_id) {
                    $option = $stoodle->options[$option_id];
                    if ($stoodle->type !== $range) {
                        $option .= '-' . ($option + $duration);
                    }
                    list($start, $end) = explode('-', $option);

                    foreach ($targets as $user_id) {
                        $calendar = new SingleCalendar($user_id, Calendar::PERMISSION_WRITABLE);
                        $calendar->addEvent();
                        $calendar->event->setProperty('DTSTART', $start);
                        $calendar->event->setProperty('DTEND', $end);
                        $calendar->event->setProperty('SUMMARY', $stoodle->title);
                        $calendar->event->setProperty('STUDIP_CATEGORY', 1);
                        $calendar->event->setProperty('CATEGORIES', '');
                        $calendar->event->setProperty('CLASS', 'PRIVATE');
                        $calendar->event->setRepeat(array('rtype' => 'SINGLE', 'expire' => Calendar::CALENDAR_END));
                        $calendar->event->save();
                    }
                }

                // Rebind course parameter since Calendar::getInstance() removes it
                URLHelper::addLinkParam('cid', $this->range_id);

                $details[] = sprintf(_('Es wurden %u Termin(e) f�r %u Person(en) eingetragen.'),
                                     count($targets) * count($results), count($results));
            }

            Pagelayout::postMessage(Messagebox::success(_('Die Umfrage wurde ausgewertet.'), $details));
            $this->redirect('admin');
            return;
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

        PageLayout::postMessage(Messagebox::success(_('Die Umfrage wurde erfolgreich gel�scht.')));
        $this->redirect('admin');
    }

    /**
     * 
     **/
    public function mail_action($id)
    {
        $stoodle = new Stoodle($id);
        $answers = $stoodle->getAnsweredOptions();
        
        $mail_to = Request::optionArray('mail_to');
        if (empty($mail_to)) {
            PageLayout::postMessage(Messagebox::error(_('Sie haben keine Empf�nger ausgew�hlt.')));
            $this->redirect('admin/edit/' . $id);
            return;
        }
        
        foreach ($mail_to as $value) {
            if ($value === 'all') {
                $mail_to = array('all');
                break;
            }
        }
        
        $recipients = array();
        foreach ($mail_to as $option_id) {
            if ($option_id === 'all') {
                $recipients = array_keys($stoodle->getAnswers());
            } else {
                $recipients = array_merge($recipients, (array)@$answers[$option_id]);
            }
        }

        if (empty($mail_to)) {
            PageLayout::postMessage(Messagebox::error(_('Es wurden keine g�ltigen Empf�nger gefunden.')));
            $this->redirect('admin/edit/' . $id);
            return;
        }

        $recipients = array_filter(array_map('get_username', $recipients));
        $url = $this->url_for('admin/edit', $id);
        if (strlen(dirname($_SERVER['SCRIPT_NAME'])) > 1) {
            $url = str_replace(dirname($_SERVER['SCRIPT_NAME']), '', $url);
        }
        $url = ltrim($url, '/');
        $parameters = array(
            'rec_uname' => $recipients,
            'subject'   => sprintf('Stoodle "%s"', $stoodle->title),
            'sms_source_page' => $url,
        );
        $url = URLHelper::getURL('sms_send.php', $parameters);
        $this->redirect($url);
    }

/** from Stud.IP 2.3 **/

    /**
     * Spawns a new infobox variable on this object, if neccessary.
     *
     * @since Stud.IP 2.3
     **/
    private function populateInfobox() {
        if (!isset($this->infobox)) {
            $this->infobox = array(
                'picture' => 'blank.gif',
                'content' => array()
            );
        }
    }

    /**
     * Sets the header image for the infobox.
     *
     * @param String $image Image to display, path is relative to :assets:/images
     *
     * @since Stud.IP 2.3
     **/
    function setInfoBoxImage($image) {
        $this->populateInfobox();
        $this->infobox['picture'] = $image;
    }

    /**
     * Adds an item to a certain category section of the infobox. Categories
     * are created in the order this method is invoked. Multiple occurences of
     * a category will add items to the category.
     *
     * @param String $category The item's category title used as the header
     * above displayed category - write spoken not
     * tech language ^^
     * @param String $text The content of the item, may contain html
     * @param String $icon Icon to display in front the item, path is
     * relative to :assets:/images
     *
     * @since Stud.IP 2.3
     **/
    function addToInfobox($category, $text, $icon = 'blank.gif') {
        $this->populateInfobox();
        $infobox = $this->infobox;
        if (!isset($infobox['content'][$category])) {
            $infobox['content'][$category] = array(
                'kategorie' => $category,
                'eintrag' => array(),
            );
        }
        $infobox['content'][$category]['eintrag'][] = compact('icon', 'text');
        $this->infobox = $infobox;
    }
}
