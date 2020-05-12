<?php
$is_visible = function ($user_id) use ($range) {
    if ($range instanceof Course) {
        $visibility = $range->members->findOneBy('user_id', $user_id)->visible ?: 'unknown';

        if ($visibility !== 'unknown') {
            return $visibility === 'yes';
        }
    }
    if ($range instanceof Institute) {
        return (bool) $range->members->findOneBy('user_id', $user_id)->visible;
    }

    return get_visibility_by_id($user_id);
}
?>

<? if (!$stoodle->is_public): ?>
    <tr class="no-highlight">
        <td class="blank">&nbsp;</td>
        <td class="blank">&nbsp;</td>
        <td colspan="<?= count($stoodle->options) ?>">
            <?= MessageBox::info($_('Die Antworten der Teilnehmer sind nicht öffentlich einsehbar.')) ?>
        </td>
    </tr>
<? endif; ?>
<?  $count = 1;
    foreach ($stoodle->answers as $user_id => $options):
       if ($user_id === $GLOBALS['user']->id && @$self === 'hide') continue;
       $user = User::find($user_id);
       $visible = $user->id === $GLOBALS['user']->id || $is_visible($user->id);
?>
    <tr>
        <td>
        <? if ($stoodle->is_anonymous || (!$visible && !$admin)): ?>
            <?= Avatar::getAvatar('nobody')->getImageTag(Avatar::SMALL) ?>
        <? else: ?>
            <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $user->username], true) ?>">
                <?= Avatar::getAvatar($user_id)->getImageTag(Avatar::SMALL) ?>
            </a>
        <? endif; ?>
        </td>
        <td>
        <? if ($stoodle->is_anonymous || (!$visible && !$admin)): ?>
            <?= sprintf($_('Teilnehmer #%u'), $count++) ?>
        <? else: ?>
            <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $user->username], true) ?>">
                <?= htmlReady($user->getFullName()) ?>
            </a>
            <? if (!$visible): ?>(<?= $_('unsichtbar') ?>)<? endif; ?>
        <? endif; ?>
        </td>
<? if ($stoodle->is_public || $admin): ?>
    <? foreach (array_keys($stoodle->options) as $id): ?>
        <td>
        <? if ($stoodle->allow_maybe && in_array($id, $options['maybes'])): ?>
            <?= Icon::create('question') ?>
        <? elseif (in_array($id, $options['selection'])): ?>
            <?= Icon::create('accept', Icon::ROLE_STATUS_GREEN) ?>
        <? else: ?>
            <?= Icon::create('decline', Icon::ROLE_STATUS_RED) ?>
        <? endif; ?>
        </td>
    <? endforeach; ?>
<? else: ?>
    <? foreach (array_keys($stoodle->options) as $id): ?>
        <td>
            <?= Icon::create('question', Icon::ROLE_INACTIVE) ?>
        </td>
    <? endforeach; ?>
<? endif; ?>
    </tr>
<? endforeach; ?>
<? if (@$show_mail): ?>
    <tr>
        <th colspan="<?= 2 + count($stoodle->options) ?>" style="text-align: center;">
            <?= $_('Nachricht verschicken an alle Teilnehmer dieser Umfrage, die die folgende(n) Option(en) gewählt haben:') ?>
        </th>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <label class="plain">
                <input type="checkbox" name="mail_to[]" value="all">
                <?= $_('Alle Teilnehmer') ?>
            </label>
        </td>
    <? foreach (array_keys($stoodle->options) as $id): ?>
        <td style="text-align: center;">
            <input type="checkbox" name="mail_to[]" value="<?= $id ?>"
                   <? if (isset($options_count) && !$options_count[$id]) echo 'disabled'; ?>>
        </td>
    <? endforeach; ?>
<? endif; ?>
