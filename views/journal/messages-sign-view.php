
<!--<div class="row">
    <div class="col-xs-12">
        <?= get_param($link_close);?>
    </div>
</div>
<div class="row">&nbsp;</div>-->

<div class="row">
    <div class="col-xs-9">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <i class="glyphicon glyphicon-comment">&nbsp;</i>
            Содержание записей в течении смены
        </div>
        <div class="panel-body panel-message-view">
            <div class="table table-responsive">
                <table class="table" id="message-table">
                    <thead>
                        <tr>
                            <th class="col-xs-1">Время</th>
                            <th class="col-xs-11">Сообщение</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?= $messageList; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
    
    <div class="col-xs-3">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <i class="glyphicon glyphicon-flag">&nbsp;</i>
                Подписи
                <div class="pull-right">
                    <span class="badge"><?= get_param($sign_info);?></span>
                </div>
            </div>
            <div class="panel-body panel-signature">
                <div class="list-group">
                    <?php
                        if (!$signatures)
                            echo <<< NOSIGN
                            <div class="list-group-item text-center bg-warning">
                                Нет подписей
                            </div>
NOSIGN;
                        foreach ($signatures as $sline) { 
                            $user = get_param($sline,'uname');
                            $time = get_param($sline, 'stamp');
                            echo <<< SIGNLINE
                            <div class="list-group-item">
                                $user
                                <div class="text-muted small">
                                    <em>$time</em>
                                </div>
                            </div>
SIGNLINE;
                    }?>
                </div>
            </div>
            <?php if (get_param($signbuton)) {
                echo <<< SFOOTER
                <div class="panel-footer">
                    $signbuton
                </div>
SFOOTER;
            }?>    
        </div>
    </div>
</div>



