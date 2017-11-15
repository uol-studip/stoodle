<? $limit = $comments ? count($stoodle->comments) : 5; ?>

<div id="comments">
    <h3>
    <? if (empty($stoodle->comments)): ?>
        <?= $_('Kommentare') ?>
    <? elseif (count($stoodle->comments) > 1): ?>
        <?= sprintf($_('%u Kommentare'), count($stoodle->comments)) ?>
    <? else: ?>
        <?= $_('1 Kommentar') ?>
    <? endif; ?>
    </h3>
<? if (!$stoodle->end_date || $stoodle->end_date > time()): ?>
    <form action="<?= $controller->url_for('stoodle/comment', $stoodle->stoodle_id) ?>" method="post">
        <?= CSRFProtection::tokenTag() ?>
        <fieldset>
            <legend><?= $_('Kommentar hinzufügen') ?></legend>

            <div class="type-text">
                <textarea class="add_toolbar" name="comment"></textarea>
            </div>

            <?= Studip\Button::createAccept($_('Kommentar speichern'), 'store') ?>
        </fieldset>
    </form>
<? endif; ?>
<? if (!empty($stoodle->comments)): ?>
    <table class="default">
        <colgroup>
            <col width="<?= reset(Avatar::getDimension(Avatar::SMALL)) ?>">
            <col>
        </colgroup>
        <tbody>
        <? foreach (array_slice($stoodle->comments, 0, $limit) as $comment):
            $user = User::find($comment->user_id);
        ?>
            <tr>
                <td>
                    <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $user->username, ['cid' => null]) ?>">
                        <?= Avatar::getAvatar($comment->user_id)->getImageTag(Avatar::SMALL) ?>
                    </a>
                </td>
                <td>
                    <?= formatReady($comment->comment) ?>

                    <ul class="details">
                        <li><?= date('d.m.y H:i', $comment->mkdate) ?></li>
                        <li>
                            <a href="<?= URLHelper::getURL('dispatch.php/profile?username=' . $user->username, ['cid' => null]) ?>">
                                <?= htmlReady($user->getFullName()) ?>
                            </a>
                        </li>
                    <? if ($comment->user_id == $GLOBALS['user']->id
                        || $GLOBALS['perm']->have_perm('root')
                        || $GLOBALS['perm']->have_studip_perm('tutor', $range_id)):

                    ?>
                        <li>
                            <a href="<?= $controller->url_for('stoodle/delete_comment', $comment->comment_id) ?>" data-confirm="<?= $_('Soll der Kommentar wirklich gelöscht werden?') ?>">
                                <?= Icon::create('trash')->asImg(['class' => 'text-top'] + tooltip2($_('Kommentar löschen'))) ?>
                            </a>
                        </li>
                    <? endif; ?>
                    </ul>
                </td>
            </tr>
        <? endforeach; ?>
        <? if (($spillover = count($stoodle->comments) - $limit) > 0): ?>
            <tr class="more-comments">
                <td colspan="2" class="topic">
                    <a href="<?= $controller->url_for('stoodle', $stoodle->stoodle_id, 'all') ?>#comments">
                        <?= Icon::create('arr_1down', Icon::ROLE_INFO_ALT) ?>
                        <? if ($spillover == 1): ?>
                            <?= $_('1 weiterer Kommentar') ?>
                        <? else: ?>
                            <?= sprintf($_('%u weitere Kommentare...'), $spillover) ?>
                        <? endif; ?>
                    </a>
                </td>
            </tr>
        <? endif; ?>
        </tbody>
    </table>
<? endif; ?>
</div>
