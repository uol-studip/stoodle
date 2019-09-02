<h2 class="topic">
    <?= $_('Umfrage') ?>:
    <?= htmlReady($stoodle->title) ?>
</h2>
<? if (!empty($stoodle->description)): ?>
<blockquote><?= formatReady($stoodle->description) ?></blockquote><br>
<? endif; ?>

<? if ($stoodle->max_answers): ?>
<?= MessageBox::info(
    $stoodle->max_answers == 1
        ? $_('Bitte beachten Sie, dass Sie nur eine Antwort auswählen können')
        : sprintf(
            $_('Bitte beachten Sie, dass Sie nur maximal %u Antworten auswählen können'),
            $stoodle->max_answers
            )
)->hideClose() ?>
<? endif; ?>

<form action="<?= $controller->participate($stoodle) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default stoodle-list">
        <thead>
            <tr>
                <td colspan="2">&nbsp;</td>
            <? foreach ($stoodle->options as $id => $option): ?>
                <th><?= htmlReady($stoodle->formatOption($id), true, true) ?></th>
            <? endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?= $this->render_partial('stoodle-participants', ['self' => 'hide']) ?>
        <? if (count($stoodle->answers) >= 10): ?>
            <tr>
                <td colspan="2">&nbsp;</td>
            <? foreach ($stoodle->options as $id => $option): ?>
                <th style="text-align: center;">
                    <?= htmlReady($stoodle->formatOption($id), true, true) ?>
                </th>
            <? endforeach; ?>
            </tr>
        <? endif; ?>
            <tr class="self" <? if ($stoodle->max_answers && ($stoodle->allow_maybe || $stoodle->max_answers > 1)) printf('data-max-answers="%u"', $stoodle->max_answers) ?>>
                <td>
                    <?= Avatar::getAvatar($GLOBALS['user']->id)->getImageTag(Avatar::SMALL) ?>
                </td>
                <td>
                    <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $GLOBALS['user']->username, ['cid' => null]) ?>">
                        <?= htmlReady($GLOBALS['user']->getFullName()) ?>
                    </a>
                </td>
            <? $answers = $stoodle->answers;
               $answer = $answers[$GLOBALS['user']->id] ?: false;
                foreach (array_keys($stoodle->options) as $id): ?>
                <td>
                <? if ($stoodle->allow_maybe): ?>
                    <label>
                        <input type="radio" name="selection[<?= $id ?>]" value="1"
                               <? if ($answer && in_array($id, $answer['selection'])) echo 'checked'; ?>>
                        <?= Icon::create('accept', Icon::ROLE_STATUS_GREEN) ?>
                    </label>
                    <label>
                        <input type="radio" name="selection[<?= $id ?>]" value="maybe"
                               <? if (!$answer || !in_array($id, $answer['selection']) || in_array($id, $answer['maybes'])) echo 'checked'; ?>>
                        <?= Icon::create('question') ?>
                    </label>
                    <label>
                        <input type="radio" name="selection[<?= $id ?>]" value="0"
                               <? if ($answer && !(in_array($id, $answer['selection']) || in_array($id, $answer['maybes']))) echo 'checked'; ?>>
                        <?= Icon::create('decline', Icon::ROLE_STATUS_RED) ?>
                    </label>
                <? elseif ($stoodle->max_answers == 1): ?>
                    <input type="radio" name="selection" value="<?= $id ?>"
                           <? if (isset($answers[$GLOBALS['user']->id]) && in_array($id, $answers[$GLOBALS['user']->id]['selection'])) echo 'checked'; ?>>
                <? else: ?>
                    <input type="hidden" name="selection[<?= $id ?>]" value="0">
                    <input type="checkbox" name="selection[<?= $id ?>]" value="1"
                           <? if (isset($answers[$GLOBALS['user']->id]) && in_array($id, $answers[$GLOBALS['user']->id]['selection'])) echo 'checked'; ?>>
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
                    <?= Studip\Button::create($_('Auswahl speichern'), 'participate') ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>

<? if ($stoodle->allow_comments): ?>
    <?= $this->render_partial('stoodle/comments') ?>
<? endif; ?>
