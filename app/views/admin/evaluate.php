<h2 class="topic"><?= _('Auswertung der Umfrage') ?>: <?= $stoodle->title ?></h2>
<? if ($stoodle->description): ?>
<blockquote><?= formatReady($stoodle->description) ?></blockquote>
<? endif; ?>

<dl class="evaluation">
    <dt><?= _('Teilnehmer') ?></dt>
    <dd>
        <?= $answers = count($stoodle->getAnswers()) ?>
        (<?= round($participants ? 100 * $answers / $participants : 0, 2) ?>%)
    </dd>
    <dt><?= _('Laufzeit') ?></dt>
    <dd><?= spoken_time($stoodle->end_date - ($stoodle->start_date ?: $stoodle->mkdate)) ?></dd>
</dl>

<form action="<?= $controller->url_for('admin/evaluate/' . $stoodle->stoodle_id) ?>" method="post">

    <table class="default">
        <colgroup>
            <col width="2em">
            <col>
            <col width="30px">
        <? if ($stoodle->allow_maybe): ?>
            <col width="30px">
        <? endif; ?>
            <col width="30px">
            <col width="40%">
        </colgroup>
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th><?= _('Antwort') ?></th>
                <th style="text-align: center;">
                    <?= Icon::create('accept', 'info', tooltip2(_('Kopfzahl: Zugesagt'))) ?>
                </th>
            <? if ($stoodle->allow_maybe): ?>
                <th style="text-align: center;">
                    <?= Icon::create('question', 'info', tooltip2(_('Kopfzahl: Vielleicht'))) ?>
                </th>
            <? endif; ?>
                <th style="text-align: center;">
                    <?= Icon::create('decline', 'info', tooltip2(_('Kopfzahl: Abgesagt'))) ?>
                </th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($stoodle->options as $id => $option): ?>
            <tr>
                <td>
                    <input type="checkbox" name="result[]" value="<?= $id ?>">
                </td>
                <td><?= $stoodle->formatOption($id) ?></td>
                <td style="text-align: center;border-left: 1px solid #ccc;"><?= 0 + $selections[$id] ?></td>
            <? if ($stoodle->allow_maybe): ?>
                <td style="text-align: center;border-left: 1px solid #ccc;"><?= 0 + $maybes[$id] ?></td>
            <? endif; ?>
                <td style="text-align: center;border-left: 1px solid #ccc;border-right: 1px solid #ccc;">
                    <?= $answers - @$maybes[$id] - $selections[$id] ?>
                </td>
                <td class="bars">
                <? if ($selections[$id]): ?>
                    <div class="bar <?= $selections[$id] == $selections_max ? 'max' : '' ?>" style="width:<?= round(100 * $selections[$id] / $max, 2) ?>%;"><?= $selections[$id] ?></div>
                <? endif; ?>
                <? if ($stoodle->allow_maybe && $maybes[$id]): ?>
                    <div class="bar maybe" style="width:<?= round(100 * $maybes[$id] / $max, 2) ?>%;">+<?= $maybes[$id] ?> ?</div>
                <? endif; ?>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
    </table>
    
    <h3 class="topic stoodle-participants">
        <?= _('Teilnehmerliste') ?>
    </h3>
    <table class="collapsed default stoodle-list">
        <thead>
            <tr>
                <td colspan="2">&nbsp;</td>
            <? foreach ($stoodle->options as $id => $option): ?>
                <th><?= $stoodle->formatOption($id) ?></th>
            <? endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?= $this->render_partial('stoodle-participants.php', array('admin' => true)) ?>
        </tbody>
    </table>

<? if (in_array($stoodle->type, words('range datetime'))): ?>
    <h3 class="topic"><?= _('Optionen') ?></h3>
    <div class="appointments">
        <label for="create_appointments">
            <?= _('Termine eintragen:') ?>
        </label>
        <input type="checkbox" name="create_appointments" id="create_appointments" value="1">
        <div style="margin: 1em 0 0;">
            <label>
                <input type="radio" name="appointments_for" value="all" checked>
                <?= _('für <u>alle</u> Teilnehmer dieser Veranstaltung') ?>
                <?= tooltipIcon(_('Dies beinhaltet Tutoren und Dozenten')) ?>
            </label>
            <label>
                <input type="radio" name="appointments_for" value="stoodle">
                <?= _('für alle Teilnehmer dieser Umfrage') ?>
            </label>
            <label>
                <input type="radio" name="appointments_for" value="valid">
                <?= _('für alle Teilnehmer dieser Umfrage, denen der Termin laut Angabe passt') ?>
            </label>
        <? if ($stoodle->type !== 'range'): ?>
            <label style="margin-top: 1em">
                <?= _('Dauer in Stunden') ?>
                <input type="text" name="appointment_duration" value="2">
                <?= tooltipicon('Auch Werte wie "0.5" für eine halbe Stunde sind zulässig.') ?>
            </label>
        <? endif; ?>
        </div>
    </div>
<? endif; ?>

    <div style="text-align: center;">
        <?= Studip\Button::createAccept(_('Auswerten'), 'evaluate') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/index')) ?>
    </div>
</form>