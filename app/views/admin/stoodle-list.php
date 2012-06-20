<table class="default stoodles">
    <colgroup>
        <col>
        <col width="130px">
        <col width="130px">
        <col width="20px">
        <col width="20px">
        <col width="20px">
        <col width="20px">
        <col width="20px">
        <col width="100px">
    </colgroup>
    <thead>
        <tr>
            <th class="topic" colspan="9"><?= $title ?: '???' ?></th>
        </tr>
        <tr>
            <th><?= _('Titel') ?></th>
            <th><?= _('Start') ?></th>
            <th><?= _('Ende') ?></th>
            <th><abbr title="<?= _('Anzahl der Teilnehmer') ?>">#</abbr></th>
            <th><?= Assets::img('icons/16/black/comment', tooltip2(_('Anzahl der Kommentare'))) ?></th>
            <th><?= Assets::img('icons/16/black/visibility-visible', tooltip2(_('Öffentlich'))) ?></th>
            <th><?= Assets::img('icons/16/black/visibility-invisible', tooltip2(_('Anonym'))) ?></th>
            <th><?= Assets::img('icons/16/black/question', tooltip2(_('Vielleicht'))) ?></th>
            <th>&nbsp;</th>
    </thead>
    <tbody>
    <? if (empty($stoodles)): ?>
        <tr class="blank">
            <td colspan="9"><?= _('Es liegen keine Umfragen vor.') ?></td>
        </tr>
    <? endif; ?>
    <? foreach ($stoodles as $stoodle): ?>
        <tr class="<?= TextHelper::cycle('cycle_even', 'cycle_odd') ?>">
            <td><?= htmlReady($stoodle->title) ?></td>
            <td><?= $stoodle->start_date ? date('d.m.Y H:i', $stoodle->start_date) : _('offen') ?></td>
            <td><?= $stoodle->end_date ? date('d.m.Y H:i', $stoodle->end_date) : _('offen') ?></td>
            <td><?= count($stoodle->getAnswers()) ?></td>
            <td><?= $stoodle->allow_comments ? count($stoodle->comments) : '-' ?></td>
            <td><?= Assets::img('icons/16/black/checkbox-' . ($stoodle->is_public ? 'checked' : 'unchecked')) ?></td>
            <td><?= Assets::img('icons/16/black/checkbox-' . ($stoodle->is_anonymous ? 'checked' : 'unchecked')) ?></td>
            <td><?= Assets::img('icons/16/black/checkbox-' . ($stoodle->allow_maybe ? 'checked' : 'unchecked')) ?></td>
            <td style="text-align: right;">
        <? if ($stoodle->evaluated): ?>
                <a href="<?= $controller->url_for('stoodle/result', $stoodle->stoodle_id) ?>">
                    <?= Assets::img('icons/16/blue/stat', tooltip2(_('Ergebnisse ansehen'))) ?>
                </a>
        <? else: ?>
            <? if ($stoodle->end_date && $stoodle->end_date < time()): ?>
                <a href="<?= $controller->url_for('admin/evaluate', $stoodle->stoodle_id) ?>">
                    <?= Assets::img('icons/16/blue/test', tooltip2(_('Umfrage auswerten'))) ?>
                </a>
                <a href="<?= $controller->url_for('admin/resume', $stoodle->stoodle_id) ?>">
                    <?= Assets::img('icons/16/blue/lock-unlocked', tooltip2(_('Umfrage fortsetzen'))) ?>
                </a>
            <? else: ?>
                <a href="<?= $controller->url_for('admin/stop', $stoodle->stoodle_id) ?>">
                    <?= Assets::img('icons/16/blue/lock-locked', tooltip2(_('Umfrage beenden'))) ?>
                </a>
            <? endif; ?>
                <a href="<?= $controller->url_for('admin/edit', $stoodle->stoodle_id) ?>">
                    <?= Assets::img('icons/16/blue/edit', tooltip2(_('Umfrage bearbeiten'))) ?>
                </a>
                <a href="<?= $controller->url_for('admin/delete', $stoodle->stoodle_id) ?>">
                    <?= Assets::img('icons/16/blue/trash', tooltip2(_('Umfrage löschen'))) ?>
                </a>
        <? endif; ?>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
</table>
