<h2 class="topic" style="padding: .5em;">
    <?= _('Umfrage') ?>:
    <?= htmlReady($stoodle->title) ?>
</h2>
<? if (!empty($stoodle->description)): ?>
<p>
    <?= formatReady($stoodle->description) ?>
</p>
<? endif; ?>

<form action="<?= $controller->url_for('stoodle/participate', $stoodle->stoodle_id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default zebra-hover stoodle-list">
        <thead>
            <tr>
                <td colspan="2">&nbsp;</td>
            <? foreach ($stoodle->options as $id => $option): ?>
                <th><?= $stoodle->formatOption($id) ?></th>
            <? endforeach; ?>
            </tr>
        </thead>
        <tbody>
        <?  $count = 1;
            foreach ($answers = $stoodle->getAnswers() as $user_id => $options):
               if ($user_id == $GLOBALS['user']->id) continue;
        ?>
            <tr>
                <td>
                <? if ($stoodle->is_anonymous): ?>
                    <?= Avatar::getAvatar('nobody')->getImageTag(Avatar::SMALL) ?>
                <? else: ?>
                    <a href="<?= URLHelper::getLink('about.php?username=' . $users[$user_id]->username, array('cid' => null)) ?>">
                        <?= Avatar::getAvatar($user_id)->getImageTag(Avatar::SMALL) ?>
                    </a>
                <? endif; ?>
                </td>
                <td>
                <? if ($stoodle->is_anonymous): ?>
                    <?= sprintf(_('Teilnehmer #%u'), $count++) ?>
                <? else: ?>
                    <a href="<?= URLHelper::getLink('about.php?username=' . $users[$user_id]->username, array('cid' => null)) ?>">
                        <?= htmlReady($users[$user_id]->getFullName()) ?>
                    </a>
                <? endif; ?>
                </td>
        <? if ($stoodle->is_public): ?>
            <? foreach (array_keys($stoodle->options) as $id): ?>
                <td>
                <? if ($stoodle->allow_maybe): ?>
                    <? if (in_array($id, $options['maybes'])): ?>
                        <?= Assets::img('icons/16/blue/question') ?>
                    <? elseif (in_array($id, $options['selection'])): ?>
                        <?= Assets::img('icons/16/green/accept') ?>
                    <? else: ?>
                        <?= Assets::img('icons/16/red/decline') ?>
                    <? endif; ?>
                <? else: ?>
                    <input type="checkbox" disabled <? if (in_array($id, $options['selection'])) echo 'checked'; ?>>
                <? endif; ?>
                </td>
            <? endforeach; ?>
        <? else: ?>
                <td colspan="<?= count($this->options) ?>" class="private">
                    <?= _('Die Antworten der Teilnehmer sind nicht öffentlich einsehbar.') ?>
                </td>
        <? endif; ?>
            </tr>
        <? endforeach; ?>
            <tr class="steel">
                <td>
                    <?= Avatar::getAvatar($GLOBALS['user']->id)->getImageTag(Avatar::SMALL) ?>
                </td>
                <td>
                    <?= _('Ihre Auswahl') ?>
                </td>
            <?  $answer = $answers[$GLOBALS['user']->id] ?: false;
                foreach (array_keys($stoodle->options) as $id): ?>
                <td>
                <? if ($stoodle->allow_maybe): ?>
                    <label>
                        <input type="radio" name="selection[<?= $id ?>]" value="1" <? if ($answer && in_array($id, $answer['selection'])) echo 'checked'; ?>>
                        <?= Assets::img('icons/16/green/accept') ?>
                    </label>
                    <label>
                        <input type="radio" name="selection[<?= $id ?>]" value="maybe" <? if ($answer && in_array($id, $answer['maybes'])) echo 'checked'; ?>>
                        <?= Assets::img('icons/16/blue/question') ?>
                    </label>
                    <label>
                        <input type="radio" name="selection[<?= $id ?>]" value="0" <? if (!$answer || !(in_array($id, $answer['selection']) || in_array($id, $answer['maybes']))) echo 'checked'; ?>>
                        <?= Assets::img('icons/16/red/decline') ?>
                    </label>
                <? else: ?>
                    <input type="hidden" name="selection[<?= $id ?>]" value="0">
                    <input type="checkbox" name="selection[<?= $id ?>]" value="1" <? if (isset($answers[$GLOBALS['user']->id]) && in_array($id, $answers[$GLOBALS['user']->id]['selection'])) echo 'checked'; ?>>
                <? endif; ?>
                </td>
            <? endforeach; ?>
            </tr>
        </tbody>
        <tfoot>
        <? if ($stoodle->is_public): ?>
            <tr class="sum">
                <td colspan="2">&nbsp;</td>
            <?  $maybes = $stoodle->allow_maybe ? $stoodle->getOptionsCount(true) : false;
                foreach ($stoodle->getOptionsCount() as $option_id => $count): ?>
                <td>
                    <?= $count ?>
                <? if ($stoodle->allow_maybe && !empty($maybes[$option_id])): ?>
                    <span class="maybe-count">
                        + <?= $maybes[$option_id] ?> ?
                    </span>
                <? endif; ?>
                </td>
            <? endforeach; ?>
            </tr>
        <? endif; ?>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td colspan="<?= count($stoodle->options) ?>">
                    <?= Studip\Button::create(_('Auswahl speichern'), 'participate') ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>

<? if ($stoodle->allow_comments): ?>
<?= $this->render_partial('stoodle/comments') ?>
<? endif; ?>
