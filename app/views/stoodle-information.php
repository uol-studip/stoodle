<dl class="stoodle-sidebar-info">
    <dt><?= _('Laufzeit') ?></dt> <!-- icons/16/black/date -->
    <dd><?= spoken_time($this->stoodle->end_date - ($this->stoodle->start_date ?: $this->stoodle->mkdate)) ?></dd>

    <dt><?= _('Start') ?></dt>
    <dd><?= date('d.m.Y H:i', $this->stoodle->start_date ?: $this->stoodle->mkdate) ?></dd>

    <dt><?= _('Ende') ?></dt>
    <dd><?= date('d.m.Y H:i', $this->stoodle->end_date) ?></dd>

    <dt class="label-participants"><?= _('Teilnehmer') ?></dt>
    <dd>
        <?= $answers ?>
        (<?= round($participants ? 100 * $answers / $participants : 0, 2) ?>%)
    </dd>
        $this->addToInfobox(_('Informationen'),
                            _('Teilnehmer') . ': ' . $answers . ' (' . round($participants ? 100 * $answers / $participants : 0, 2) . '%)',
                            'icons/16/black/stat');

<? /*
        $this->addToInfobox(_('Informationen'),
                            sprintf(_('Die Umfrage war <em>%s</em> und <em>%s</em>.'),
                                    $this->stoodle->is_public ? _('öffentlich') : _('nicht öffentlich'),
                                    $this->stoodle->is_anonymous ? _('anonym') : _('nicht anonym')),
                            'icons/16/black/visibility-visible');
        if ($this->stoodle->allow_maybe) {
            $this->addToInfobox(_('Informationen'),
                                _('Eine Angabe von "vielleicht" war erlaubt.'),
                                'icons/16/black/question');
        }
        if ($this->stoodle->allow_comments) {
            $this->addToInfobox(_('Informationen'),
                                _('Kommentare waren erlaubt.'),
                                'icons/16/black/comment');
        }
        $this->setInfoboxImage('infobox/evaluation.jpg');
*/ ?>
</dl>

