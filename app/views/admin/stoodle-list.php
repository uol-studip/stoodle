<form action="<?= $controller->bulk() ?>" method="post">
<table class="default stoodles" id="<?= htmlReady($id) ?>-list">
    <colgroup>
        <col style="width: 24px">
        <col>
        <col style="width: 130px">
        <col style="width: 130px">
        <col style="width: 20px">
        <col style="width: 20px">
        <col style="width: 20px">
        <col style="width: 20px">
        <col style="width: 20px">
        <col style="width: 100px">
    </colgroup>
    <thead>
        <tr>
            <th class="topic" colspan="10"><?= htmlReady($title) ?: '???' ?></th>
        </tr>
        <tr>
            <th>
                <input type="checkbox"
                       data-proxyfor="#<?= htmlReady($id) ?>-list tbody :checkbox"
                       data-activates="#<?= htmlReady($id) ?>-list tfoot .button">
            </th>
            <th><?= $_('Titel') ?></th>
            <th><?= $_('Start') ?></th>
            <th><?= $_('Ende') ?></th>
            <th><abbr title="<?= $_('Anzahl der Teilnehmer') ?>">#</abbr></th>
            <th><?= Icon::create('comment', Icon::ROLE_INFO)->asImg(tooltip2($_('Anzahl der Kommentare'))) ?></th>
            <th><?= Icon::create('visibility-visible', Icon::ROLE_INFO)->asImg(tooltip2($_('Öffentlich'))) ?></th>
            <th><?= Icon::create('visibility-invisible', Icon::ROLE_INFO)->asImg(tooltip2($_('Anonym'))) ?></th>
            <th><?= Icon::create('question', Icon::ROLE_INFO)->asImg(tooltip2($_('Vielleicht'))) ?></th>
            <th>&nbsp;</th>
    </thead>
    <tbody>
    <? if (!$stoodles): ?>
        <tr class="blank">
            <td colspan="10"><?= $_('Es liegen keine Umfragen vor.') ?></td>
        </tr>
    <? endif; ?>
    <? foreach ($stoodles as $stoodle): ?>
        <tr>
            <td>
                <input type="checkbox" name="ids[]"
                       value="<?= htmlReady($stoodle->id) ?>">
            </td>
            <td><?= htmlReady($stoodle->title) ?></td>
            <td><?= $stoodle->start_date ? strftime('%x %R', $stoodle->start_date) : $_('offen') ?></td>
            <td><?= $stoodle->end_date ? strftime('%x %R', $stoodle->end_date) : $_('offen') ?></td>
            <td><?= count($stoodle->answers) ?></td>
            <td><?= $stoodle->allow_comments ? count($stoodle->comments) : '-' ?></td>
            <td><?= Icon::create('checkbox-' . ($stoodle->is_public ? 'checked' : 'unchecked'), Icon::ROLE_INFO) ?></td>
            <td><?= Icon::create('checkbox-' . ($stoodle->is_anonymous ? 'checked' : 'unchecked'), Icon::ROLE_INFO) ?></td>
            <td><?= Icon::create('checkbox-' . ($stoodle->allow_maybe ? 'checked' : 'unchecked'), Icon::ROLE_INFO) ?></td>
            <td class="actions">
        <? if ($stoodle->evaluated): ?>
                <a href="<?= $controller->link_for('stoodle/result', $stoodle->stoodle_id) ?>">
                    <?= Icon::create('stat')->asImg(tooltip2($_('Ergebnisse ansehen'))) ?>
                </a>
        <? else: ?>
            <? if ($stoodle->end_date && $stoodle->end_date < time()): ?>
                <a href="<?= $controller->evaluate($stoodle) ?>">
                    <?= Icon::create('test')->asImg(tooltip2($_('Umfrage auswerten'))) ?>
                </a>
                <a href="<?= $controller->resume($stoodle) ?>">
                    <?= Icon::create('lock-unlocked')->asImg(tooltip2($_('Umfrage fortsetzen'))) ?>
                </a>
            <? else: ?>
                <a href="<?= $controller->stop($stoodle) ?>">
                    <?= Icon::create('lock-locked')->asImg(tooltip2($_('Umfrage beenden'))) ?>
                </a>
            <? endif; ?>
                <a href="<?= $controller->edit($stoodle) ?>">
                    <?= Icon::create('edit')->asImg(tooltip2($_('Umfrage bearbeiten'))) ?>
                </a>
        <? endif; ?>
                <?= Icon::create('trash')->asInput(tooltip2($_('Umfrage löschen')) + [
                    'data-confirm' => $_('Soll diese Umfrage wirklich gelöscht werden?'),
                    'formaction'   => $controller->deleteURL($stoodle),
                ]) ?>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="10">
            <? if ($id !== 'evaluated'): ?>
                <?= Studip\Button::create(_('Umfragen fortsetzen'), 'resume') ?>
                <?= Studip\Button::create(_('Umfragen beenden'), 'stop') ?>
            <? endif; ?>
                <?= Studip\Button::create(_('Umfragen löschen'), 'delete', [
                    'data-confirm' => $_('Sollen die markierten Umfragen wirklich gelöscht werden?'),
                ]) ?>
            </td>
        </tr>
    </tfoot>
</table>
</form>
