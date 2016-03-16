
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" title="Закрыть">&times;</button>
    <h4 class="modal-title">Открытие новой смены</h4>
</div>
<form id="shift-form" action="postprocess/" method="post">
    <div class="modal-body">

        <div class="row">
            <div class="col-sm-6 text-right">
                <label class="control-label" for="datepicker">Дата новой смены :</label>
            </div>
            <div class="col-sm-6">
                <div class="form-group input-group">
                    <input id="datepicker" class="form-control datepicker" type="text" name="sdate" 
                           placeholder="Выберите дату.." required autocomplete="off"
                           value="<?= $cdate; ?>">
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>

        <div class="row form-group">
            <div class="col-sm-6 text-right">
                <label class="control-label">Вид смены :</label>
            </div>
            <div class="col-sm-6">
                <?= $pint;?>
            </div>
        </div>

        <div class="row form-group">
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