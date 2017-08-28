<table class="default">
    <caption><?= $_('Aktuelle Umfragen') ?></caption>
    <thead>
        <tr>
            <th><?= $_('Datum') ?></th>
            <th><?= $_('Titel') ?></th>
            <th><?= $_('Verbleibende Zeit') ?></th>
            <th><?= $_('Teilnehmer') ?></th>
            <th><?= $_('Teilgenommen?') ?></th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody class="stoodle-overview">
    <? if (empty($stoodles['present'])): ?>
        <tr class="empty">
            <td colspan="6"><?= $_('Es liegen keine aktuellen Umfragen vor.') ?></td>
        </tr>
    <? endif; ?>
    <? foreach ($stoodles['present'] as $stoodle): ?>
        <tr>
            <td><?= date('d.m.Y', $stoodle->start_date ?: $stoodle->mkdate) ?></td>
            <td><?= htmlReady($stoodle->title) ?></td>
            <td>
            <? if ($stoodle->end_date): ?>
                <abbr title="<?= date('%x %H:%M', $stoodle->end_date) ?>">
                    <?= spoken_time($stoodle->end_date - time()) ?>
                </abbr>
            <? else: ?>
                <?= $_('unbegrenzt') ?>
            <? endif; ?>
            </td>
            <td><?= count($stoodle->getAnswers()) ?></td>
            <td>
            <? if ($stoodle->userParticipated()): ?>
                <?= $plugin->getIcon('checkbox-checked', 'info') ?>
            <? else: ?>
                <?= $plugin->getIcon('checkbox-unchecked', 'info') ?>
            <? endif; ?>
            </td>
            <td class="actions">
                <a href="<?= $controller->url_for('stoodle', $stoodle->stoodle_id) ?>">
                    <?= $plugin->getIcon(
                        $stoodle->userParticipated() ? 'test' : 'vote',
                        'clickable',
                        array_merge(
                            tooltip2($$_('An der Umfrage teilnehmen')),
                            ['class' => 'text-top']
                        )
                    ) ?>
                    <?= $_('Teilnehmen') ?>
                </a>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
</table>

<? if (!empty($evaluated)): ?>
<br>
<table class="default">
    <caption><?= $_('Ausgewertete Umfragen') ?></caption>
    <thead>
        <tr>
            <th><?= $_('Datum') ?></th>
            <th><?= $_('Titel') ?></th>
            <th><?= $_('Teilgenommen?') ?></th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($evaluated as $stoodle): ?>
        <tr>
            <td><?= strftime('%x %H:%M', $stoodle->start_date ?: $stoodle->mkdate) ?></td>
            <td><?= htmlReady($stoodle->title) ?></td>
            <td>
            <? if ($stoodle->userParticipated()): ?>
                <?= $plugin->getIcon('checkbox-checked', 'clickable') ?>
            <? else: ?>
                <?= $plugin->getIcon('checkbox-unchecked', 'clickable') ?>
            <? endif; ?>
            </td>
            <td class="actions">
                <a href="<?= $controller->url_for('stoodle/result', $stoodle->stoodle_id) ?>">
                    <?= $plugin->getIcon('stat', 'clickable', tooltip2($_('Ergebnisse ansehen'))) ?>
                </a>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
</table>
<? endif; ?>
