<h2 class="topic"><?= _('Auswertung der Umfrage') ?>: <?= $stoodle->title ?></h2>
<blockquote><?= formatReady($stoodle->description) ?></blockquote>

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

    <table class="default zebra-hover">
        <colgroup>
            <col width="2em">
            <col>
            <col width="30px">
        <? if ($stoodle->allow_maybe): ?>
            <col width="30px">
        <? endif; ?>
            <col width="40%">
        </colgroup>
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th><?= _('Antwort') ?></th>
                <th style="text-align: center;"><abbr title="<?= _('Kopfzahl') ?>">#</abbr></th>
            <? if ($stoodle->allow_maybe): ?>
                <th style="text-align: center; white-space: nowrap;"><abbr title="<?= _('Kopfzahl: Vielleicht') ?>"># ?</abbr></th>
            <? endif; ?>
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
                <td style="text-align: center;border-left: 1px solid #ccc;border-right: 1px solid #ccc;"><?= 0 + $maybes[$id] ?></td>
            <? endif; ?>
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

<? if (in_array($stoodle->type, words('range datetime'))): ?>
    <div>
        <label for="create_appointments">
            Termine eintragen:
            <?= ''; //tooltipicon(_('foo')) ?>
        </label>
        <input type="checkbox" name="create_appointments" id="create_appointments" value="1">
    <? if ($stoodle->type !== 'range'): ?>
        <br>TODO: Dauer?
    <? endif; ?>
    </div>
<? endif; ?>

    <div style="text-align: center;">
        <?= Studip\Button::createAccept(_('Auswerten'), 'evaluate') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/index')) ?>
    </div>
</form>