<h2 class="topic"><?= $_('Auswertung:') ?> <?= htmlReady($stoodle->title) ?></h2>
<blockquote><?= formatReady($stoodle->description) ?></blockquote>

<? if (count($stoodle->results) > 1): ?>
<h3><?= $_('Ausgewählte Ergebnisse') ?></h3>
<ul>
<? foreach ($stoodle->results as $id => $value): ?>
    <li><?= $stoodle->formatOption($id) ?></li>
<? endforeach; ?>
</ul>
<? elseif (count($stoodle->results) === 1): ?>
<h3><?= $_('Ausgewähltes Ergebnis') ?></h3>
<p><?= $stoodle->formatOption(key($stoodle->results)) ?></p>
<? endif; ?>

<h3><?= $_('Übersicht der Umfrage') ?></h3>
<table class="default">
    <colgroup>
        <col>
        <col width="30px">
    <? if ($stoodle->allow_maybe): ?>
        <col width="30px">
    <? endif; ?>
        <col width="50%">
    </colgroup>
    <tbody>
    <? foreach ($stoodle->options as $id => $option): ?>
        <tr <? if (isset($stoodle->results[$id])) echo 'style="font-weight: bold;"'; ?>>
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

<? if ($stoodle->allow_comments && count($stoodle->comments)): ?>
<?= $this->render_partial('stoodle/comments') ?>
<? endif; ?>

<? if ($GLOBALS['perm']->have_studip_perm('tutor', $range_id)): ?>
<h3 class="topic stoodle-participants">
    <?= $_('Teilnehmerliste') ?>
</h3>
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
        <?= $this->render_partial('stoodle-participants') ?>
    </tbody>
</table>
<? endif; ?>
