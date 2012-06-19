<?  $count = 1;
    foreach ($answers = $stoodle->getAnswers() as $user_id => $options):
       if ($user_id == $GLOBALS['user']->id && @$self === 'hide') continue;
       $user = User::find($user_id);
?>
    <tr>
        <td>
        <? if ($stoodle->is_anonymous): ?>
            <?= Avatar::getAvatar('nobody')->getImageTag(Avatar::SMALL) ?>
        <? else: ?>
            <a href="<?= URLHelper::getLink('about.php?username=' . $user->username, array('cid' => null)) ?>">
                <?= Avatar::getAvatar($user_id)->getImageTag(Avatar::SMALL) ?>
            </a>
        <? endif; ?>
        </td>
        <td>
        <? if ($stoodle->is_anonymous): ?>
            <?= sprintf(_('Teilnehmer #%u'), $count++) ?>
        <? else: ?>
            <a href="<?= URLHelper::getLink('about.php?username=' . $user->username, array('cid' => null)) ?>">
                <?= htmlReady($user->getFullName()) ?>
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
        <td colspan="<?= count($stoodle->options) ?>" class="private">
            <?= _('Die Antworten der Teilnehmer sind nicht öffentlich einsehbar.') ?>
        </td>
<? endif; ?>
    </tr>
<? endforeach; ?>
