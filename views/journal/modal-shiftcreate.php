
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" title="Закрыть">&times;</button>
    <h4 class="modal-title">Открытие новой смены</h4>
</div>
<form id="shift-form" action="/journal/newshift/" method="post">
    <div class="modal-body">

        <!--
        <div class="row form-group">
            <div class="col-xs-6 text-right">
                <strong>Смену принял :</strong>
            </div>
            <div class="col-xs-6">
                <input type="text" class="form-control" readonly value="<?= get_param($useropen, null); ?>" />
            </div>
        </div>
        -->
        
        <div class="row">
            <div class="col-sm-6 text-right">
                <label class="control-label" for="shift-date">Дата новой смены :</label>
            </div>
            <div class="col-sm-6">
                <div class=" input-group">
                    <input id="shift-date" class="form-control text-center" type="text" name="sdate" 
                           placeholder="Выберите дату.." required autocomplete="off"
                           value="<?= $cdate; ?>">
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6 text-right">
                <label class="control-label">Вид смены :</label>
            </div>
            <div class="col-sm-6">
                <?= $pint;?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6 text-right">
                <label class="control-label">Вахта :</label>
            </div>
            <div class="col-sm-6">
                <?= $pwatch;?>
            </div>
        </div>

    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button name="newshift" id="shift-submit" type="submit" class="btn btn-primary">Создать</button>
    </div>
</form>