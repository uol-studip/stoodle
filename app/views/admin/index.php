<?= $this->render_partial('admin/stoodle-list', [
    'title'    => _('Aktuelle Umfragen'),
    'stoodles' => $stoodles,
]) ?>

<? if (count($evaluated)): ?>
    <?= $this->render_partial('admin/stoodle-list', [
        'title'    => _('Ausgewertete Umfragen'),
        'stoodles' => $evaluated,
    ]) ?>
<? endif; ?>
