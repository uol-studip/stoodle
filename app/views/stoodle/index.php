<table class="default zebra-hover">
    <thead>
        <tr>
            <th class="topic" colspan="6"><?= _('Aktuelle Umfragen') ?></th>
        </tr>
        <tr>
            <th><?= _('Datum') ?></th>
            <th><?= _('Titel') ?></th>
            <th><?= _('Verbleibende Zeit') ?></th>
            <th><?= _('Teilnehmer') ?></th>
            <th><?= _('Teilgenommen?') ?></th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
    <? if (empty($stoodles['present'])): ?>
        <tr class="blank">
            <td colspan="6"><?= _('Es liegen keine aktuellen Umfragen vor.') ?></td>
        </tr>
    <? endif; ?>
    <? foreach ($stoodles['present'] as $stoodle): ?>
        <tr>
            <td><?= date('d.m.Y', $stoodle->start_date ?: $stoodle->mkdate) ?></td>
            <td><?= htmlReady($stoodle->title) ?></td>
            <td>
            <? if ($stoodle->end_date): ?>
                <abbr title="<?= date('d.m.Y H:i', $stoodle->end_date) ?>">
                    <?= spoken_time($stoodle->end_date - time()) ?>
                </abbr>
            <? else: ?>
                <?= _('unbegrenzt') ?>
            <? endif; ?>
            </td>
            <td><?= count($stoodle->getAnswers()) ?></td>
            <td>
            <? if ($stoodle->userParticipated()): ?>
                <?= Assets::img('icons/16/black/checkbox-checked') ?>
            <? else: ?>
                <?= Assets::img('icons/16/black/checkbox-unchecked') ?>
            <? endif; ?>
            </td>
            <td style="text-align: right;">
                <a href="<?= $controller->url_for('stoodle', $stoodle->stoodle_id) ?>">
                    <?= Assets::img('icons/16/blue/' . ($stoodle->userParticipated() ? 'test' : 'vote'),
                                    tooltip2(_('An der Umfrage teilnehmen'))) ?>
                </a>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
</table>

<? if (!empty($evaluated)): ?>
<br>
<table class="default zebra-hover">
    <thead>
        <tr>
            <th class="topic" colspan="4"><?= _('Ausgewertete Umfragen') ?></th>
        </tr>
        <tr>
            <th><?= _('Datum') ?></th>
            <th><?= _('Titel') ?></th>
            <th><?= _('Teilgenommen?') ?></th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($evaluated as $stoodle): ?>
        <tr>
            <td><?= date('d.m.Y', $stoodle->start_date ?: $stoodle->mkdate) ?></td>
            <td><?= htmlReady($stoodle->title) ?></td>
            <td>
            <? if ($stoodle->userParticipated()): ?>
                <?= Assets::img('icons/16/blue/checkbox-checked') ?>
            <? else: ?>
                <?= Assets::img('icons/16/blue/checkbox-unchecked') ?>
            <? endif; ?>
            </td>
            <td style="text-align: right;">
                <a href="<?= $controller->url_for('stoodle/result', $stoodle->stoodle_id) ?>">
                    <?= Assets::img('icons/16/blue/stat', tooltip2(_('Ergebnisse ansehen'))) ?>
                </a>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
</table>
<? endif; ?>
