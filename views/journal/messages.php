
<div class="row">

    <div class="panel panel-primary">
        <div class="panel-heading">
            <i class="glyphicon glyphicon-comment">&nbsp;</i>
            Содержание записей в течении смены
        </div>
        <div class="panel-body panel-message">
            <div class="table table-responsive">
                <table class="table" id="message-table">
                    <thead>
                        <tr>
                            <th class="col-xs-2">Время</th>
                            <th class="col-xs-10">Сообщение</th>
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
                            $mtext = (get_param($item, 'comment'));
                            $mdate = get_param($item, 'mdate');
                            
                            $m_date = get_param($item,'md');
                            $m_time = get_param($item,'mt');
                            
                            $special = (int)get_param($item, 'special') === 1 ? 'special' : '';
                            $mid = get_param($item, 'id');
                            
                            $dbtn = <<< REMOVEMSG
                                    <div class="controls">
                                        <button href="/journal/deletemessage/id/$mid"
                                            class="btn btn-primary btn-sm msg-action-delete" title="Удалить">
                                            <i class="glyphicon glyphicon-remove"></i>
                                        </button>
                                    </div>
REMOVEMSG;
                            // если права на удаление сообщения нет, но кнопку даже не рисуем
                            if (get_param($candel) === false)  $dbtn = '';
                            echo <<< MLINE
                            <tr class="$special">
                                <td class="text-center mtime">
                                    <input type="text" class="form-control datetimepicker" value="$m_date" mid="$mid" readonly/>
                                    <input type="text" class="form-control" value="$m_time" id="t$mid" readonly/>
                                </td>
                                <td class="mctrl"><textarea class="message-content" mid="$mid">$mtext</textarea>
                                    $dbtn
                                    <div class="status">
                                        <button class="btn btn-primary btn-sm">
                                            <i class="glyphicon glyphicon-pencil"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
MLINE;
                        }?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="panel-footer">
            <div class="row">
                <div class="col-xs-10">
                    <textarea id="new-message-text" class="form-control"
                              placeholder="Ввод нового сообщения"></textarea>
                </div>
                <div class="col-xs-2">
                    <input type="text" class="form-control mtime" id="new-message-time" readonly value="<?= date('d.m.Y H:i'); ?>"/>
                    <button class="btn btn-block btn-primary" id="btn-new-message">
                        <i class="glyphicon glyphicon-share-alt"></i>
                        Сохранить
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
