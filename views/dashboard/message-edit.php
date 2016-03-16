
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" title="Закрыть">&times;</button>
    <h4 class="modal-title">Ввод нового сообщения</h4>
</div>

<form id="msg-form" action="postprocess/" method="post">
    <div class="modal-body">
        
        <div class="row">
            <div class="col-xs-6">
                <label for="tpicker">Дата сообщения</label>
                <div class="form-group input-group">
                    <span class="input-group-addon">
                        <i class="glyphicon glyphicon-calendar"></i>
                    </span>
                    <input id="datepicker" class="form-control datepicker" type="text" name="msg-date" 
                           required value="<?= $cdate; ?>"/>
                </div>
            </div>
            <div class="col-xs-6">
                <label for="tpicker">Время сообщения</label>
                <div class="form-group input-group">
                    <span class="input-group-addon">
                        <i class="glyphicon glyphicon-time"></i>
                    </span>
                    <input id="tpicker" name="msg-time" class="form-control" type="text"  required value="<?= date('H:i'); ?>"/>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <strong>Особая отметка</strong>
                <input type="checkbox" name="special" />
            </div>
        </div>

        <textarea name="message" class="form-control flot-chart-content" 
                  placeholder="Новое сообщение" 
                  autocomplete="off" 
                  required></textarea>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button name="newmsg" id="msg-submit" type="submit" class="btn btn-primary">Сохранить</button>
    </div>
</form>
