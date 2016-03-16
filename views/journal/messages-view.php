
<div class="row">

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
                    
                        <?php
                        if (!$messages) echo <<< NOMSG
                            <tr class="warning">
                                <td colspan="2">
                                    Сообщений нет.
                                </td>
                            </tr>
NOMSG;
                        foreach ($messages as $item) {
                            $mtext = nl2br(get_param($item, 'comment'));
                            $mdate = get_param($item, 'mdate');
                            
                            $m_date = get_param($item,'md');
                            $m_time = get_param($item,'mt');
                            
                            $special = (int)get_param($item, 'special') === 1 ? 'special' : '';
                            $mid = get_param($item, 'id');
                            echo <<< MLINE
                            <tr class="$special">
                                <td class="text-center">
                                    $mdate
                                </td>
                                <td>$mtext</td>
                            </tr>
MLINE;
                        }?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
