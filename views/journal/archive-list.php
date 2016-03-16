
<div class="row">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <i class="glyphicon glyphicon-book">&nbsp;</i>
                Архив журнала 
                <table class="table">
                    <thead>
                        <tr>
                            <th class="col-xs-1">Дата</th>
                            <th class="col-xs-2">Смена</th>
                            <th class="col-xs-1">Вахта</th>
                            <th class="col-xs-3">ФИО НС</th>
                            <th class="col-xs-5"></th> 
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="panel-body panel-archive">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="col-xs-1"></th>
                                <th class="col-xs-2"></th>
                                <th class="col-xs-1"></th>
                                <th class="col-xs-3"></th>
                                <th class="col-xs-5"></th> 
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                if (!$archive) echo <<< NOMSG
                                    <tr class="warning">
                                        <td colspan="5">
                                            Архив пуст.
                                        </td>
                                    </tr>
NOMSG;
                                
                                foreach ($archive as $shift) {
                                    //var_dump($shift);                                    continue;
                                    $sid    = get_param($shift, 'id');
                                    $dopen  = get_param($shift, 'dopen');
                                    $uname  = get_param($shift, 'ou');
                                    $period = get_param($shift, 'period');
                                    $watch  = get_param($shift, 'abbr');
                                    
                                    $signed = (int)get_param($shift, 'sign');
                                    $lname  = $signed ? 'Подписано' : 'Подписать';
                                    $lclass = $signed ? 'btn btn-primary disabled' : 'btn btn-default';
                                    
                                    $btn_sign = CHtml::drawLink($lname, array(
                                        'href' => '',
                                        'class' => "$lclass sign",
                                        'sid' => $sid,
                                        'title' => 'Подписать смену',
                                    ));
                                    
                                    $btn_preview = CHtml::drawLink('Просмотр', array(
                                        'href' => "/journal/preview/id/$sid/",
                                        'class' => "btn btn-default",
                                        'title' => 'Просмотр смены',
                                    ));
                                    
                                    // если права на подписть нет, то кнопку подписи вообще не рисуем
                                    if (!get_param($sign)) $btn_sign = '';
                                    
                                    $bt4rep = CHtml::drawLink('4 смены', array(
                                        'href' => "/journal/report/id/$sid/cnt/4/",
                                        'class' => "btn btn-default",
                                        'title' => 'Отчет за 4 смены',
                                        'target' => '_blank',
                                    ));
                                    
                                    $bt6rep = CHtml::drawLink('6 смен', array(
                                        'href' => "/journal/report/id/$sid/cnt/6/",
                                        'class' => "btn btn-default",
                                        'title' => 'Отчет за 6 смен',
                                        'target' => '_blank',
                                    ));
                                    
                                    echo <<< SHIFT
                                    <tr>
                                        <td>$dopen</td>
                                        <td>$period</td>
                                        <td>$watch</td>
                                        <td>$uname</td>
                                        <td>
                                            <div class="btn-group btn-group-justified btn-group-sm">
                                                $btn_sign
                                                $btn_preview
                                                $bt4rep
                                                $bt6rep
                                            </div>
                                        </td>
                                    </tr>
SHIFT;
                            }?>
                            
                        </tbody>
                    </table>
                </div> 
            </div>
    </div>
</div>