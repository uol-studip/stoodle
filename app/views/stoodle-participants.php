<? if (!$stoodle->is_public): ?>
    <tr class="no-highlight">
        <td class="blank">&nbsp;</td>
        <td class="blank">&nbsp;</td>
        <td colspan="<?= count($stoodle->options) ?>">
            <?= MessageBox::info(_('Die Antworten der Teilnehmer sind nicht öffentlich einsehbar.')) ?>
        </td>
    </tr>
<? endif; ?>
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
        <? if ($stoodle->allow_maybe && in_array($id, $options['maybes'])): ?>
            <?= Assets::img('icons/16/blue/question') ?>
        <? elseif (in_array($id, $options['selection'])): ?>
            <?= Assets::img('icons/16/green/accept') ?>
        <? else: ?>
            <?= Assets::img('icons/16/red/decline') ?>
        <? endif; ?>
        </td>
    <? endforeach; ?>
<? else: ?>
    <? foreach (array_keys($stoodle->options) as $id): ?>
        <td>
            <?= Assets::img('icons/16/gray/question') ?>
        </td>
    <? endforeach; ?>
<? endif; ?>
    </tr>
<? endforeach; ?>
<? if (@$show_mail): ?>
    <tr>
        <th colspan="<?= 2 + count($stoodle->options) ?>" style="text-align: center;">
            <?= _('Nachricht verschicken an alle Teilnehmer dieser Umfrage, die die folgende(n) Option(en) gewählt haben:') ?>
        </th>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <label class="plain">
                <input type="checkbox" name="mail_to[]" value="all">
                <?= _('Alle Teilnehmer') ?>
            </label>
        </td>
    <? foreach (array_keys($stoodle->options) as $id): ?>
        <td style="text-align: center;">
            <input type="checkbox" name="mail_to[]" value="<?= $id ?>"
                   <? if (isset($options_count) && !$options_count[$id]) echo 'disabled'; ?>>
        </td>
    <? endforeach; ?>
<? endif; ?>
