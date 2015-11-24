<?
    $types = array(
        'date'     => _('Datum'),
        'time'     => _('Uhrzeit'),
        'datetime' => _('Datum und Uhrzeit'),
        'range'    => _('Zeitspanne'),
        'text'     => _('Freitext'),
    );

    $formatValue = function ($type, $value) {
        if ($type === 'text') {
            return 'value="' . htmlReady($value) . '"';
        }

        $templates = array(
            'date'     => _('%d.%m.%Y'),
            'time'     => _('%H:%M Uhr'),
            'datetime' => _('%d.%m.%Y %H:%M'),
        );

        if ($type === 'range') {
            $type = 'datetime';
        }

        return $value ? 'value="' . strftime($templates[$type], $value) . '"' : '';
    };
?>

<noscript>
    <?= MessageBox::error(_('Sie haben Javascript deaktiviert. Dadurch ist die Funktionsweise dieser Seite beeintr�chtigt.')) ?>
</noscript>

<? if (array_sum($options_count)): ?>
<?= MessageBox::info(
        sprintf(_('Diese Umfrage hat bereits %u Teilnehmer. Sie k�nnen sie daher nicht mehr in vollem Umfang bearbeiten.'), count($answers)),
        array(
            _('Der Typ der Umfrage kann nicht mehr ver�ndert werden.'),
            _('Von Teilnehmern bereits gew�hlte Antwortm�glichkeiten k�nnen nicht mehr ver�ndert werden.')
        ), true) ?>
<? endif; ?>

<form action="<?= $controller->url_for('admin/edit', $id) ?>" method="post">
<h3 class="topic"><?= $id ? _('Umfrage bearbeiten') : _('Neue Umfrage erstellen') ?></h3>
<table class="default stoodle">
    <colgroup>
        <col width="200">
        <col>
        <col width="200">
    </colgroup>
    <thead>
        <tr>
            <th colspan="3"><?= _('Grunddaten') ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <label for="title"><?= _('Titel') ?> *</label>
            </td>
            <td colspan="2">
                <input type="text" name="title" id="title"
                       required value="<?= htmlReady($title) ?>"
                       style="width:99%">
            </td>
        </tr>
        <tr>
            <td>
                <label for="description"><?= _('Beschreibung') ?></label>
            </td>
            <td colspan="2">
                <textarea class="add_toolbar" name="description" id="description" 
                          style="width:99%"><?= htmlReady($description) ?></textarea>
            </td>
        </tr>
        <tr>
            <td>
                <label for="type"><?= _('Typ') ?></label>
            </td>
            <td colspan="2">
                <select id="type" name="type" <? if (!$stoodle->isNew()) echo 'disabled'; ?>>
                <? foreach ($types as $t => $n): ?>
                    <option value="<?= $t ?>" <? if ($type == $t) echo 'selected'; ?>>
                        <?= htmlReady($n) ?>
                    </option>
                <? endforeach; ?>
                </select>
            <? if (!$stoodle->isNew()): ?>
                <?= tooltipIcon(_('Der Typ einer Umfrage kann im Nachhinein nicht mehr ge�ndert werden'), true) ?>
                <input type="hidden" name="type" value="<?= $type ?>">
            <? endif; ?>
            </td>
        </tr>
    </tbody>
</table>

<table class="default stoodle">
    <colgroup>
        <col width="200">
        <col>
        <col width="200">
    </colgroup>
    <thead>
        <tr>
            <th colspan="3"><?= _('Laufzeit der Umfrage') ?></th>
        </tr>
    </thead>
    <tbody class="dates">
        <tr>
            <td>
                <label for="start_date"><?= _('Start') ?></label>
            </td>
            <td colspan="2">
                <input type="datetime" name="start_date" id="start_date"
                       <?= $formatValue('datetime', $start_date) ?>>
                <label>
                    <input type="checkbox" name="start_date" value="foo"
                           <? if (!$start_date) echo 'checked'; ?>>
                    <?= _('Offen') ?>
                </label>
                <?= tooltipicon(_('Wenn Sie keinen festen Startzeitpunkt angeben m�chten, '
                                 .'k�nnen Sie den Haken bei "offen" setzen, um die '
                                 .'Umfrage unverz�glich zu starten.')) ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="end_date"><?= _('Ende') ?></label>
            </td>
            <td colspan="2">
                <input type="datetime" name="end_date" id="end_date"
                       <?= $formatValue('datetime', $end_date) ?>>
                <label>
                    <input type="checkbox" name="end_date" value=""
                           <? if (!$end_date) echo 'checked'; ?>>
                    <?= _('Offen') ?>
                </label>
                <?= tooltipicon(_('Wenn Sie keinen festen Endzeitpunkt angeben m�chten, '
                                 .'k�nnen Sie den Haken bei "offen" setzen, um die '
                                 .'Umfrage unbegrenzt laufen zu lassen. Sie muss dann '
                                 .'manuell in der Verwaltung beendet werden.')) ?>
            </td>
        </tr>
    </tbody>
</table>

<table class="default stoodle">
    <colgroup>
        <col width="200">
        <col>
        <col width="200">
    </colgroup>
    <thead>
        <tr>
            <th colspan="3"><?= _('Optionen') ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <label for="allow_comments"><?= _('Kommentare erlauben') ?></label>
            </td>
            <td colspan="2">
                <input type="hidden" name="allow_comments" value="0">
                <input type="checkbox" name="allow_comments" id="allow_comments" value="1"
                       <? if ($allow_comments) echo 'checked'; ?>>
            </td>
        </tr>
        <tr>
            <td>
                <label for="is_public"><?= _('F�r alle einsehbar') ?></label>
            </td>
            <td colspan="2">
                <input type="hidden" name="is_public" value="0">
                <input type="checkbox" name="is_public" id="is_public" value="1"
                       <? if ($is_public) echo 'checked'; ?>>
                <?= tooltipicon(_('Die gegebenen Antworten der Teilnehmer sowie '
                                 .'das Ergebnis der Umfrage sind f�r andere Teilnehmer '
                                 .'sichtbar.')) ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="is_anonymous"><?= _('Anonyme Teilnahme') ?></label>
            </td>
            <td colspan="2">
                <input type="hidden" name="is_anonymous" value="0">
                <input type="checkbox" name="is_anonymous" id="is_anonymous" value="1"
                       <? if ($is_anonymous) echo 'checked'; ?>>
                <?= tooltipicon(_('Die Namen der Teilnehmer sind f�r andere Teilnehmer nicht sichtbar.')) ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="allow_maybe"><?= _('"Vielleicht"') ?></label>
            </td>
            <td colspan="2">
                <input type="hidden" name="allow_maybe" value="0">
                <input type="checkbox" name="allow_maybe" id="allow_maybe" value="1"
                       <? if ($allow_maybe) echo 'checked'; ?>>
                <?= tooltipicon(_('Teilnehmer k�nnen auch "Vielleicht" als Antwort geben.')) ?>
            </td>
        </tr>
    </tbody>
</table>

<table class="default stoodle">
    <colgroup>
        <col width="20">
        <col width="50">
        <col>
        <col width="200">
    </colgroup>
    <thead>
        <tr>
            <th>
                <input type="checkbox" name="ids[]" value="all" data-proxyfor=".options :checkbox[name='ids[]']">
            </th>
            <th colspan="3"><?= _('Antwortm�glichkeiten') ?></th>
        </tr>
    </thead>
    <tbody class="options">
    <? $index = 0; foreach ($options as $id => $value):
           if ($type === 'range') {
               list($value, $additional) = explode('-', $value);
           }
    ?>
        <tr>
            <td>
                <input type="checkbox" name="ids[]" value="<?= $id ?>"
                       <? if ($options_count[$id]) echo 'disabled'; ?>>
            </td>
            <td>
                #<?= $index + 1 ?>
            </td>
            <td>
                <input type="<?= $type ?>" name="options[<?= $id ?>]"
                    <? if ($options_count[$id]) echo 'disabled'; ?>
                    <?= $formatValue($type, $value) ?>
                    <? if (isset($focussed) && $focussed == $index) echo 'autofocus'; ?>>
                <span class="type-range">
                    <?= _('bis') ?>
                    <input type="<?= $type ?>" name="additional[<?= $id ?>]"
                        <? if ($options_count[$id]) echo 'disabled'; ?>
                        <?= $formatValue($type, $additional) ?>>
                </span>
            <? if ($options_count[$id]): ?>
                <small>(<?= sprintf(_('bereits %u Mal gew�hlt'), $options_count[$id]) ?>)</small>
            <? endif; ?>
            </td>
            <td style="text-align: right;" class="actions">
            <? if ($index > 0): ?>
                <button name="move[up]" value="<?= $index ?>" title="<?= _('Antwort nach oben verschieben') ?>">
                    <?= Assets::img('icons/16/yellow/arr_2up', array('alt' => _('Antwort nach oben verschieben'))) ?>
                </button>
            <? else: ?>
                <button disabled>
                    <?= Assets::img('icons/16/grey/arr_2up') ?>
                </button>
            <? endif; ?>
            <? if ($index < count($options) - 1): ?>
                <button name="move[down]" value="<?= $index ?>" title="<?= _('Antwort nach unten verschieben') ?>">
                    <?= Assets::img('icons/16/yellow/arr_2down', array('alt' => _('Antwort nach unten verschieben'))) ?>
                </button>
            <? else: ?>
                <button disabled>
                    <?= Assets::img('icons/16/grey/arr_2down') ?>
                </button>
            <? endif; ?>
            <? if ($options_count[$id]): ?>
                <button disabled>
                    <?= Assets::img('icons/16/grey/trash') ?>
                </button>
            <? else: ?>
                <button name="remove" value="<?= $id ?>" title="<?= _('Antwort l�schen') ?>">
                    <?= Assets::img('icons/16/blue/trash', array('alt' => _('Antwort l�schen'))) ?>
                </button>
            <? endif; ?>
            </td>
        </tr>
    <? $index += 1; endforeach; ?>
        <tr>
            <td colspan="4" class="printhead">
                <?= Studip\Button::createCancel(_('Markierte Eintr�ge entfernen'), 'remove') ?>
                <div style="float: right;">
                    <select name="add-quantity">
                    <? for ($i = 1; $i <= 10; $i += 1): ?>
                        <option><?= $i ?></option>
                    <? endfor; ?>
                    </select>
                    <?= Studip\Button::create(_('Weitere Antwortm�glichkeit(en) hinzuf�gen'), 'add') ?>
                </div>
            </td>
        </tr>
    </tbody>
</table>

<div style="text-align: center;">
    <div class="button-group">
        <?= Studip\Button::createAccept(_('Speichern'), 'store') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin')) ?>
    </div>
</div>
</form>

<? if (count($answers)): ?>
<h3 class="topic">
    <?= _('Teilnehmerliste') ?>
</h3>
<form action="<?= $controller->url_for('admin/mail', $stoodle->stoodle_id) ?>" method="post">
<table class="default stoodle-list">
    <thead>
        <tr>
            <td colspan="2">&nbsp;</td>
        <? foreach ($stoodle->options as $id => $option): ?>
            <th><?= $stoodle->formatOption($id) ?></th>
        <? endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?= $this->render_partial('stoodle-participants', array('show_mail' => !$stoodle->is_anonymous)) ?>
    </tbody>
<? if (!$stoodle->is_anonymous): ?>
    <tfoot>
        <tr>
            <td colspan="<?= 2 + count($stoodle->options) ?>">
                <?= Studip\Button::createAccept(_('Nachricht verschicken')) ?>
                <?= Studip\ResetButton::create(_('Auswahl zur�cksetzen')) ?>
            </td>
        </tr>
    </tfoot>
<? endif; ?>
</table>
</form>
<? endif; ?>