<h2 class="topic">
    <?= _('Umfrage') ?>:
    <?= htmlReady($stoodle->title) ?>
</h2>
<? if (!empty($stoodle->description)): ?>
<blockquote><?= formatReady($stoodle->description) ?></blockquote><br>
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
            <?= $this->render_partial('stoodle-participants', array('self' => 'hide')) ?>
            <tr class="steel self">
                <td>
                    <?= Avatar::getAvatar($GLOBALS['user']->id)->getImageTag(Avatar::SMALL) ?>
                </td>
                <td>
                    <a href="<?= URLHelper::getLink('about.php?username=' . $GLOBALS['user']->username, array('cid' => null)) ?>">
                        <?= $GLOBALS['user']->getFullName() ?>
                    </a>
                </td>
            <? $answers = $stoodle->getAnswers();
               $answer = $answers[$GLOBALS['user']->id] ?: false;
                foreach (array_keys($stoodle->options) as $id): ?>
                <td>
                <? if ($stoodle->allow_maybe): ?>
                    <label>
                        <input type="radio" name="selection[<?= $id ?>]" value="1" <? if ($answer && in_array($id, $answer['selection'])) echo 'checked'; ?>>
                        <?= Assets::img('icons/16/green/accept') ?>
                    </label>
                    <label>
                        <input type="radio" name="selection[<?= $id ?>]" value="maybe" <? if (!$answer || !(in_array($id, $answer['selection']) || in_array($id, $answer['maybes']))) echo 'checked'; ?>>
                        <?= Assets::img('icons/16/blue/question') ?>
                    </label>
                    <label>
                        <input type="radio" name="selection[<?= $id ?>]" value="0" <? if ($answer && !(in_array($id, $answer['selection']) || in_array($id, $answer['maybes']))) echo 'checked'; ?>>
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
