<table class="default">
    <thead>
        <tr>
            <th class="topic" colspan="9"><?= $title ?: '???' ?></th>
        </tr>
        <tr>
            <th><?= _('Titel') ?></th>
            <th><?= _('Start') ?></th>
            <th><?= _('Ende') ?></th>
            <th><?= _('Teilnehmer') ?></th>
            <th><?= _('Kommentare') ?>
            <th><?= _('Öffentlich') ?></th>
            <th><?= _('Anonym') ?></th>
            <th><?= _('Vielleicht') ?></th>
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
            <td><?= Assets::img('icons/16/blue/checkbox-' . ($stoodle->is_public ? 'checked' : 'unchecked')) ?></td>
            <td><?= Assets::img('icons/16/blue/checkbox-' . ($stoodle->is_anonymous ? 'checked' : 'unchecked')) ?></td>
            <td><?= Assets::img('icons/16/blue/checkbox-' . ($stoodle->allow_maybe ? 'checked' : 'unchecked')) ?></td>
        <? if ($stoodle->evaluated): ?>
            <td>&nbsp;</td>
        <? else: ?>
            <td style="text-align: right;">
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
            </td>
        <? endif; ?>
        </tr>
    <? endforeach; ?>
    </tbody>
</table>
