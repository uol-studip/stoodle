<ul class="stoodle-info">
    <li><?= _('Start') ?>: <?= $stoodle->start_date ? date('d.m.Y', $stoodle->start_date) : 'offen' ?></li>
    <li><?= _('Ende') ?>: <?= $stoodle->end_date ? date('d.m.Y', $stoodle->end_date) : 'offen' ?></li>
    <li>
    <? if ($stoodle->is_public): ?>
        <?= _('Die Ergebnisse der Umfrage sind öffentlich einsehbar.') ?>
    <? else: ?>
        <?= _('Die Ergebnisse der Umfrage sind nicht öffentlich einsehbar.') ?>
    <? endif; ?>
    </li>
<? if ($stoodle->is_anonymous): ?>
    <li><?= _('Die Umfrage ist anonym.')?></li>
<? endif; ?>
</ul>
