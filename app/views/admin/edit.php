<?php
    $types = [
        'date'     => $_('Datum'),
        'time'     => $_('Uhrzeit'),
        'datetime' => $_('Datum und Uhrzeit'),
        'range'    => $_('Zeitspanne'),
        'text'     => $_('Freitext'),
    ];

    $formatValue = function ($type, $value) use ($_) {
        if ($type === 'text') {
            return 'value="' . htmlReady($value) . '"';
        }

        $templates = [
            'date'     => $_('%d.%m.%Y'),
            'time'     => $_('%H:%M Uhr'),
            'datetime' => $_('%d.%m.%Y %H:%M'),
        ];

        if ($type === 'range') {
            $type = 'datetime';
        }

        return $value ? 'value="' . strftime($templates[$type], $value) . '"' : '';
    };
?>

<noscript>
    <?= MessageBox::error($_('Sie haben Javascript deaktiviert. Dadurch ist die Funktionsweise dieser Seite beeinträchtigt.')) ?>
</noscript>

<? if (array_sum($options_count)): ?>
<?= MessageBox::info(
        sprintf($_('Diese Umfrage hat bereits %u Teilnehmer. Sie können sie daher nicht mehr in vollem Umfang bearbeiten.'), count($answers)),
        [
            $_('Der Typ der Umfrage kann nicht mehr verändert werden.'),
            $_('Von Teilnehmern bereits gewählte Antwortmöglichkeiten können nicht mehr verändert werden.')
        ], true) ?>
<? endif; ?>

<form action="<?= $controller->url_for('admin/edit', $id) ?>" method="post">
<table class="default stoodle">
    <caption>
        <?= $id ? $_('Umfrage bearbeiten') : $_('Neue Umfrage erstellen') ?>
    </caption>
    <colgroup>
        <col width="200">
        <col>
        <col width="200">
    </colgroup>
    <thead>
        <tr>
            <th colspan="3"><?= $_('Grunddaten') ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <label for="title"><?= $_('Titel') ?> *</label>
            </td>
            <td colspan="2">
                <input type="text" name="title" id="title"
                       required value="<?= htmlReady($title) ?>"
                       style="width:99%">
            </td>
        </tr>
        <tr>
            <td>
                <label for="description"><?= $_('Beschreibung') ?></label>
            </td>
            <td colspan="2">
                <textarea class="add_toolbar" name="description" id="description"
                          style="width:99%"><?= htmlReady($description) ?></textarea>
            </td>
        </tr>
        <tr>
            <td>
                <label for="type"><?= $_('Typ') ?></label>
            </td>
            <td colspan="2">
                <select id="type" name="type" <? if (!$stoodle->isNew()) echo 'readonly'; ?>>
                <? foreach ($types as $t => $n): ?>
                    <option value="<?= $t ?>" <? if ($type == $t) echo 'selected'; ?>>
                        <?= htmlReady($n) ?>
                    </option>
                <? endforeach; ?>
                </select>
            <? if (!$stoodle->isNew()): ?>
                <?= tooltipIcon($_('Der Typ einer Umfrage kann im Nachhinein nicht mehr geändert werden'), true) ?>
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
            <th colspan="3"><?= $_('Laufzeit der Umfrage') ?></th>
        </tr>
    </thead>
    <tbody class="dates">
        <tr>
            <td>
                <label for="start_date">
                    <?= $_('Start') ?>
                    <?= tooltipicon($_('Wenn Sie keinen festen Startzeitpunkt angeben möchten, '
                                    . 'können Sie den Haken bei "offen" setzen, um die '
                                    . 'Umfrage unverzüglich zu starten.')) ?>
                </label>
            </td>
            <td colspan="2">
                <input type="checkbox" name="start_date" value="foo"
                       id="start_date_switch" class="studip-checkbox"
                       data-disables="#start_date" data-gains-focus
                       <? if (!$start_date) echo 'checked'; ?>>
                <label for="start_date_switch">
                    <?= $_('Offen') ?>
                </label>
                <label>
                    <?= $_('bzw.')?>
                    <input type="text" name="start_date" id="start_date" class="datetime"
                           <?= $formatValue('datetime', $start_date) ?>>
                </label>
            </td>
        </tr>
        <tr>
            <td>
                <label for="end_date">
                    <?= $_('Ende') ?>
                    <?= tooltipicon($_('Wenn Sie keinen festen Endzeitpunkt angeben möchten, '
                                    . 'können Sie den Haken bei "offen" setzen, um die '
                                    . 'Umfrage unbegrenzt laufen zu lassen. Sie muss dann '
                                    . 'manuell in der Verwaltung beendet werden.')) ?>
                </label>
            </td>
            <td colspan="2">
                <input type="checkbox" name="end_date" value=""
                       id="end_date_switch" class="studip-checkbox"
                       data-disables="#end_date" data-gains-focus
                       <? if (!$end_date) echo 'checked'; ?>>
                <label for="end_date_switch">
                    <?= $_('Offen') ?>
                </label>
                <label>
                    <?= $_('bzw.')?>
                    <input type="text" name="end_date" id="end_date" class="datetime"
                           <?= $formatValue('datetime', $end_date) ?>>
                </label>
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
            <th colspan="3"><?= $_('Optionen') ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <label for="allow_comments"><?= $_('Kommentare erlauben') ?></label>
            </td>
            <td colspan="2">
                <input type="hidden" name="allow_comments" value="0">
                <input type="checkbox" name="allow_comments" id="allow_comments" value="1"
                       <? if ($allow_comments) echo 'checked'; ?>>
            </td>
        </tr>
        <tr>
            <td>
                <label for="is_public"><?= $_('Für alle einsehbar') ?></label>
            </td>
            <td colspan="2">
                <input type="hidden" name="is_public" value="0">
                <input type="checkbox" name="is_public" id="is_public" value="1"
                       <? if ($is_public) echo 'checked'; ?>>
                <?= tooltipicon($_('Die gegebenen Antworten der Teilnehmer sowie '
                                 .'das Ergebnis der Umfrage sind für andere Teilnehmer '
                                 .'sichtbar.')) ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="is_anonymous"><?= $_('Anonyme Teilnahme') ?></label>
            </td>
            <td colspan="2">
                <input type="hidden" name="is_anonymous" value="0">
                <input type="checkbox" name="is_anonymous" id="is_anonymous" value="1"
                       <? if ($is_anonymous) echo 'checked'; ?>>
                <?= tooltipicon($_('Die Namen der Teilnehmer sind für andere Teilnehmer nicht sichtbar.')) ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="allow_maybe"><?= $_('"Vielleicht"') ?></label>
            </td>
            <td colspan="2">
                <input type="hidden" name="allow_maybe" value="0">
                <input type="checkbox" name="allow_maybe" id="allow_maybe" value="1"
                       <? if ($allow_maybe) echo 'checked'; ?>>
                <?= tooltipicon($_('Teilnehmer können auch "Vielleicht" als Antwort geben.')) ?>
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
            <th colspan="3"><?= $_('Antwortmöglichkeiten') ?></th>
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
                       <? if ($options_count[$id]) echo 'readonly'; ?>>
            </td>
            <td>
                #<?= $index + 1 ?>
            </td>
            <td>
                <input type="<?= in_array($type, ['range', 'time', 'date', 'datetime']) ? 'text' : $type ?>"
                       name="options[<?= $id ?>]"
                       data-type="<?= $type ?>"
                    <? if ($options_count[$id]) echo 'readonly'; ?>
                    <?= $formatValue($type, $value) ?>
                    <? if (isset($focussed) && $focussed == $index) echo 'autofocus'; ?>>
                <span class="type-range">
                    <?= $_('bis') ?>
                    <input type="<?= in_array($type, ['range', 'time', 'date', 'datetime']) ? 'text' : $type ?>"
                        data-type="<?= $type ?>"
                        name="additional[<?= $id ?>]"
                        <? if ($options_count[$id]) echo 'readonly'; ?>
                        <?= $formatValue($type, $additional) ?>>
                </span>
            <? if ($options_count[$id]): ?>
                <small>(<?= sprintf($_('bereits %u Mal gewählt'), $options_count[$id]) ?>)</small>
            <? endif; ?>
            </td>
            <td class="actions">
            <? if ($index > 0): ?>
                <button name="move[up]" value="<?= $index ?>" title="<?= $_('Antwort nach oben verschieben') ?>">
                    <?= Icon::create('arr_2up', Icon::ROLE_SORT)->asImg(tooltip2($_('Antwort nach oben verschieben'))) ?>
                </button>
            <? else: ?>
                <button disabled>
                    <?= Icon::create('arr_2up', Icon::ROLE_INACTIVE) ?>
                </button>
            <? endif; ?>
            <? if ($index < count($options) - 1): ?>
                <button name="move[down]" value="<?= $index ?>" title="<?= $_('Antwort nach unten verschieben') ?>">
                    <?= Icon::create('arr_2down', Icon::ROLE_SORT)->asImg(tooltip2($_('Antwort nach unten verschieben'))) ?>
                </button>
            <? else: ?>
                <button disabled>
                    <?= Icon::create('arr_2down', Icon::ROLE_INACTIVE) ?>
                </button>
            <? endif; ?>
            <? if ($options_count[$id]): ?>
                <button disabled>
                    <?= Icon::create('trash', Icon::ROLE_INACTIVE) ?>
                </button>
            <? else: ?>
                <button name="remove" value="<?= $id ?>" title="<?= $_('Antwort löschen') ?>">
                    <?= Icon::create('trash')->asImg(tooltip2($_('Antwort löschen'))) ?>
                </button>
            <? endif; ?>
            </td>
        </tr>
    <? $index += 1; endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4">
                <?= Studip\Button::createCancel($_('Markierte Einträge entfernen'), 'remove') ?>
                <div style="float: right;">
                    <select name="add-quantity">
                    <? for ($i = 1; $i <= 10; $i += 1): ?>
                        <option><?= $i ?></option>
                    <? endfor; ?>
                    </select>
                    <?= Studip\Button::create($_('Weitere Antwortmöglichkeit(en) hinzufügen'), 'add') ?>
                </div>
            </td>
        </tr>
    </tfoot>
</table>

<div style="text-align: center">
        <?= Studip\Button::createAccept($_('Speichern'), 'store') ?>
        <?= Studip\LinkButton::createCancel($_('Abbrechen'), $controller->url_for('admin')) ?>
</div>
</form>

<? if (count($answers) > 0): ?>
<form action="<?= $controller->url_for('admin/mail', $stoodle->stoodle_id) ?>" method="post" data-dialog>
<table class="default stoodle-list">
    <caption>
        <?= $_('Teilnehmerliste') ?>
    </caption>
    <thead>
        <tr>
            <td colspan="2">&nbsp;</td>
        <? foreach ($stoodle->options as $id => $option): ?>
            <th><?= $stoodle->formatOption($id) ?></th>
        <? endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?= $this->render_partial('stoodle-participants', ['show_mail' => !$stoodle->is_anonymous, 'admin' => true]) ?>
    </tbody>
<? if (!$stoodle->is_anonymous): ?>
    <tfoot>
        <tr>
            <td colspan="<?= 2 + count($stoodle->options) ?>">
                <?= Studip\Button::createAccept($_('Nachricht verschicken')) ?>
                <?= Studip\ResetButton::create($_('Auswahl zurücksetzen')) ?>
            </td>
        </tr>
    </tfoot>
<? endif; ?>
</table>
</form>
<? endif; ?>
