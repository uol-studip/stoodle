<?php
use Stoodle\Option;
use Stoodle\Stoodle;

/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class AdminController extends \Stoodle\Controller
{
    /**
     *
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        Navigation::activateItem('/course/stoodle/administration');

        $this->range_id = $this->dispatcher->range_id;

        if (!$GLOBALS['perm']->have_studip_perm('tutor', $this->range_id)) {
            throw new AccessDeniedException();
        }

        $this->setPageTitle('Stoodle: ' . $this->_('Verwaltung'));
    }

    public function index_action()
    {
        $this->stoodles  = Stoodle::findByRange($this->range_id);
        $this->evaluated = Stoodle::findEvaluatedByRange($this->range_id);

        Sidebar::get()->addWidget(new ActionsWidget())->addLink(
            $this->_('Neue Umfrage erstellen'),
            $this->url_for('admin/edit'),
            Icon::create('add')
        );
    }

    /**
     *
     */
    public function edit_action(Stoodle $stoodle = null)
    {
        $this->id = $stoodle->id;

        $this->title          = trim(Request::get('title', $stoodle->title));
        $this->description    = trim(Request::get('description', $stoodle->description)) ?: null;
        $this->type           = Request::option('type', $stoodle->type ?: 'date');
        $this->start_date     = Request::int('start_date', $stoodle->start_date) ?: null;
        $this->end_date       = Request::int('end_date', $stoodle->end_date) ?: null;
        $this->is_public      = Request::int('is_public', $stoodle->isNew() ? 1 : $stoodle->is_public);
        $this->is_anonymous   = Request::int('is_anonymous', $stoodle->isNew() ? 0 : $stoodle->is_anonymous);
        $this->allow_maybe    = Request::int('allow_maybe', $stoodle->isNew() ? 0 : $stoodle->allow_maybe);
        $this->allow_comments = Request::int('allow_comments', $stoodle->isNew() ? 1 : $stoodle->allow_comments);
        $this->max_answers    = Request::int('max_answers', $stoodle->isNew() ? null : $stoodle->max_answers);
        // Integrate additional
        $this->options        = $this->extractOptions($stoodle->options, $this->type === 'range');
        $this->options_count  = $stoodle->getOptionsCount(null);
        $this->answers        = $stoodle->answers;
        $this->max_answered   = $this->answers ? max(array_map(function ($answer) {
                                    return count($answer['selection']) + count($answer['maybes']);
                                }, $this->answers)) : 0;
        $this->editable       = array_sum($this->options_count) === 0;

        // Ensure anonymous cannot be changed when at least one answer has been given
        if (!$this->editable && $stoodle->getPristineValue('is_anonymous') && !$this->is_anonymous) {
            $this->is_anonymous = true;
        }

        if (Request::submitted('move')) {
            list($direction, $index) = each(Request::getArray('move'));

            $keys = array_keys($this->options);
            $key  = array_splice($keys, $index, 1);
            array_splice($keys, $direction == 'up' ? $index - 1 : $index + 1, 0, $key);

            $values = array_values($this->options);
            $value = array_splice($values, $index, 1);
            array_splice($values, $direction == 'up' ? $index - 1 : $index + 1, 0, $value);

            $this->options = array_combine($keys, $values);
        } elseif (Request::submitted('remove')) {
            $index = Request::option('remove');
            if (empty($index)) {
                $ids = Request::optionArray('ids');
                if (in_array('all', $ids)) {
                    $this->options = [];
                } else {
                    foreach ($ids as $id) {
                        unset($this->options[$id]);
                    }
                }
                if (empty($this->options)) {
                    $this->options[Option::getNewId()] = '';
                }
            } else {
                unset($this->options[$index]);
                $this->options = array_merge($this->options);
            }
        } elseif (Request::submitted('add')) {
            $quantity = Request::int('add-quantity', 1);
            for ($i = 1; $i <= $quantity; $i += 1) {
                $value = '';
                $last  = end($this->options);
                if (!empty($last)) {
                    if ($this->type === 'date') {
                        $value = strtotime('+1 day', $last);
                    } elseif ($this->type === 'time' || $this->type === 'datetime') {
                        $value = strtotime('+1 hour', $last);
                    } elseif ($this->type === 'range' && $last !== '-') {
                        list(, $end) = explode('-', $last);
                        $start = strtotime('+1 hour', $end);
                        $end   = strtotime('+1 hour', $start);
                        $value = $start . '-' . $end;
                    }
                }
                $this->options[Option::getNewId()] = $value;
            }
            $this->focussed = count($this->options);
        } elseif (Request::submitted('store')) {
            $errors = [];

            if (empty($this->title)) {
                $errors[] = $this->_('Bitte geben Sie einen Titel an');
            }
            if ($this->start_date && $this->end_date && $this->start_date > $this->end_date) {
                $errors[] = $this->_('Das Enddatum muss vor dem Startdatum liegen.');
            }

            $this->options = $this->reduceOptions($this->options, $invalid);
            if (count($this->options) < 1) {
                $errors[] = $this->_('Bitte geben Sie mindestens eine Antwortmöglichkeit ein.');
            }

            if (count($invalid) > 0) {
                $errors[] = $this->_('Sie haben nicht alle Zeitspannen gültig ausgefüllt (fehlendes Start- bzw. Enddatum).');
            }

            if (empty($errors)) {
                $new = $stoodle->isNew();

                $stoodle->title          = $this->title;
                $stoodle->description    = $this->description;
                $stoodle->start_date     = $this->start_date;
                $stoodle->end_date       = $this->end_date;
                $stoodle->is_public      = $this->is_public;
                $stoodle->is_anonymous   = $this->is_anonymous;
                $stoodle->allow_maybe    = $this->allow_maybe;
                $stoodle->allow_comments = $this->allow_comments;
                $stoodle->max_answers    = $this->max_answers ?: null;

                if ($new) {
                    $stoodle->type     = $this->type;
                    $stoodle->range_id = $this->range_id;
                    $stoodle->user_id  = $GLOBALS['user']->id;
                }

                $stoodle->store();
                $stoodle->setOptions($this->options);

                $message = $new
                         ? $this->_('Der Eintrag wurde erfolgreich erstellt.')
                         : $this->_('Der Eintrag wurde erfolgreich bearbeitet.');
                PageLayout::postSuccess($message);
                $this->redirect('admin');
            } else {
                PageLayout::postError($this->_('Es sind Fehler aufgetreten:'), $errors);
            }
        }

        if (empty($this->options)) {
            $this->options = [''];
        }
        $this->stoodle = $stoodle;
    }

    private function extractOptions($defaults = [], $include_additional = false)
    {
        $options = Request::getArray('options') ?: $defaults ?: [Option::getNewId() => ''];

        if ($include_additional && isset($_REQUEST['options'], $_REQUEST['additional'])) {
            $additional = Request::getArray('additional');
            $comments   = Request::getArray('comment');
            foreach ($options as $id => $value) {
                if (isset($additional[$id]) and $additional[$id] and $additional[$id] < $value) {
                    $value = $additional[$id] . '-' . $value;
                } else {
                    $value .= "-{$additional[$id]}";
                }
                if (isset($comments[$id])) {
                    $value .= "-{$comments[$id]}";
                }
                $options[$id] = $value;
            }
        }

        return $options;
    }

    private function reduceOptions($options, &$invalid = [])
    {
        $result = $invalid = [];
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

    public function stop_action(Stoodle $stoodle)
    {
        $stoodle->end_date = time() - 1;
        $stoodle->store();

        PageLayout::postSuccess($this->_('Die Umfrage wurde beendet.'));
        $this->redirect('admin');
    }

    public function resume_action(Stoodle $stoodle)
    {
        $stoodle->end_date = null;
        $stoodle->store();

        PageLayout::postSuccess($this->_('Die Umfrage wurde fortgesetzt.'));
        $this->redirect('admin');
    }

    public function evaluate_action(Stoodle $stoodle)
    {
        if ($stoodle->evaluated !== null) {
            PageLayout::postError($this->_('Die Umfrage wurde bereits ausgewertet.'));
            $this->redirect('admin');
        }

        if (Request::submitted('evaluate')) {
            $details = [];

            $stoodle->evaluated     = time();
            $stoodle->evaluated_uid = $GLOBALS['user']->id;
            $stoodle->store();

            $results = Request::optionArray('result');
            foreach (array_keys($stoodle->options) as $option_id) {
                $option = new Option($option_id);
                $option->setResult(in_array($option_id, $results));
            }

            if (!empty($results) && Request::int('create_appointments')
                && in_array($stoodle->type, ['datetime', 'range']))
            {
                $target = Request::option('appointments_for');
                if ($target === 'valid') {
                    $targets = [];
                    foreach ($stoodle->answers as $user_id => $answer) {
                        $temp = array_merge($answer['selection'], $answer['maybes']);
                        if (count(array_intersect($temp, $results))) {
                            $targets[] = $user_id;
                        }
                    }
                } elseif ($target === 'stoodle') {
                    $targets = array_keys($stoodle->answers);
                } else {
                    $targets = [];
                    foreach (['', 'autor', 'tutor', 'dozent'] as $type) {
                        $temp    = $stoodle->getRangeMembers($type);
                        $ids     = array_keys($temp);
                        $targets = array_merge($targets, $ids);
                    }
                    $targets = array_unique($targets);
                }

                $duration = round(Request::float('appointment_duration') * 60 * 60);

                foreach ($results as $option_id) {
                    $option = $stoodle->options[$option_id];
                    if ($stoodle->type !== 'range') {
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
                        $calendar->event->setRepeat(['rtype' => 'SINGLE', 'expire' => Calendar::CALENDAR_END]);
                        $calendar->event->save();
                    }
                }

                // Rebind course parameter since Calendar::getInstance() removes it
                URLHelper::addLinkParam('cid', $this->range_id);

                $details[] = sprintf(
                    $this->_('Es wurden %u Termin(e) für %u Person(en) eingetragen.'),
                    count($targets) * count($results),
                    count($results)
                );
            }

            Pagelayout::postSuccess(
                $this->_('Die Umfrage wurde ausgewertet.'),
                $details
            );
            $this->redirect('admin');
            return;
        }

        $this->stoodle        = $stoodle;
        $this->selections     = $stoodle->getOptionsCount();
        $this->maybes         = $stoodle->getOptionsCount(true);
        $this->max            = max($stoodle->getOptionsCount(null));
        $this->participants   = count($stoodle->getRangeMembers());

        $this->selections_max = max($this->selections);
        $this->maybes_max     = max($this->maybes);
    }

    /**
     *
     */
    public function delete_action(Stoodle $stoodle)
    {
        $stoodle->delete();

        PageLayout::postSuccess($this->_('Die Umfrage wurde erfolgreich gelöscht.'));
        $this->redirect('admin');
    }

    /**
     *
     **/
    public function mail_action(Stoodle $stoodle)
    {
        $answers = $stoodle->getAnsweredOptions();

        $mail_to = Request::optionArray('mail_to');
        if (empty($mail_to)) {
            PageLayout::postError($this->_('Sie haben keine Empfänger ausgewählt.'));
            $this->relocate('admin/edit/' . $stoodle->id);
            return;
        }

        foreach ($mail_to as $value) {
            if ($value === 'all') {
                $mail_to = ['all'];
                break;
            }
        }

        $recipients = [];
        foreach ($mail_to as $option_id) {
            if ($option_id === 'all') {
                $recipients = array_keys($stoodle->answers);
            } else {
                $recipients = array_merge($recipients, (array)@$answers[$option_id]);
            }
        }

        if (empty($mail_to)) {
            PageLayout::postError($this->_('Es wurden keine gültigen Empfänger gefunden.'));
            $this->relocate("admin/edit/{$stoodle->id}");
            return;
        }

        $recipients = array_filter(array_map('get_username', $recipients));
        $url = $this->editURL($stoodle);
        if (mb_strlen(dirname($_SERVER['SCRIPT_NAME'])) > 1) {
            $url = str_replace(dirname($_SERVER['SCRIPT_NAME']), '', $url);
        }
        $url = ltrim($url, '/');
        $parameters = [
            'rec_uname' => $recipients,
            'subject'   => sprintf('Stoodle "%s"', $stoodle->title),
            'sms_source_page' => $url,
        ];
        $url = URLHelper::getURL('dispatch.php/messages/write', $parameters);

        $this->redirect($url);
    }
}
