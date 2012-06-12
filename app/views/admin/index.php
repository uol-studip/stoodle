<?= $this->render_partial('admin/stoodle-list', array(
        'title'    => _('Aktuelle Umfragen'),
        'stoodles' => $stoodles,
)) ?>

<? if (count($evaluated)): ?>
    <?= $this->render_partial('admin/stoodle-list', array(
            'title'    => _('Ausgewertete Umfragen'),
            'stoodles' => $evaluated,
    )) ?>
<? endif; ?>
