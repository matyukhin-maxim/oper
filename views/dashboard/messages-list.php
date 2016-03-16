
<?php if (!$messages) : ?>
    <div class="alert alert-warning">
        Сообщений нет.
    </div>
<?php endif; ?>

<?php
foreach ($messages as $msg) {
    $mtext = nl2br(get_param($msg, 'comment'));
    $mdate = get_param($msg, 'date_msg');
    $muser = get_param($msg, 'fio');
    $special = get_param($msg, 'special', '0') === '0' ? '' : 'special';
    echo <<<SMSG
            <blockquote class="$special">
            <h5>
                <span class="glyphicon glyphicon-time"></span>
                <strong>$mdate</strong>
                <div class="pull-right">
                    <span class="glyphicon glyphicon-user"></span>
                    $muser
                </div>
                <br /><br />$mtext
            </h5>
        </blockquote>
SMSG;
}