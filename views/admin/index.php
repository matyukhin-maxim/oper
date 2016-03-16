
<div class="row">
    <h2 class="page-header">
        <?= get_param($subtitle, null, 'Административная панель'); ?>
    </h2>
</div>

<div class="row">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <div class="row">
                <div class="col-xs-9 h4">
                    <i class="glyphicon glyphicon-user"></i>
                    Пользователи
                </div>
                <div class="col-xs-3 pull-right">
                    <input id="ufilter" type="text" class="form-control" placeholder="Поиск..." autofocus=""/>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <table class="table">
                <thead>
                    <tr>
                        <th class="col-xs-4">Фамилия</th>
                        <th class="col-xs-3">Имя</th>
                        <th class="col-xs-3">Отчетво</th>
                        <th class="col-xs-2">Должность</th>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="panel-body usertable">
            <table class="table table-striped table-hover" id="usertable">
                <colgroup>
                    <col class="col-md-4">
                    <col class="col-md-3">
                    <col class="col-md-3">
                    <col class="col-md-2">
                </colgroup>
                <tbody>
                    
                </tbody>
            </table>
        </div>
    </div>
</div>