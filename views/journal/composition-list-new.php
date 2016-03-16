
<div class="row">
    <form action="" id="comp-form">
    <div class="col-xs-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <i class="glyphicon glyphicon-bell">&nbsp;</i>
                Общий состав смены станции
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table comp">
                        <thead>
                            <tr>
                                <th class="col-xs-2">Должность</th>
                                <th class="col-xs-4">ФИО сотрудника</th>
                                <th class="col-xs-3">Стажер</th>
                                <th class="col-xs-3">Дублер</th>
                            </tr>
                        </thead>
                        <tbody>
                           <?php
                                $cnt = 0;
                                $readonly = get_param($viewonly, null, true);
                                foreach ($cdata as $posid => $arules) {
                                    $pold = '';
                                    $cnt++;
                                    foreach ($arules as $rule) {
                                        $class = $cnt % 2 ? 'active ' : ''; // чередование цвета должностей
                                        $rid = get_param($rule,'ruleid');
                                        $select = get_param($plist,$posid);
                                        
                                        $pname = get_param($rule, 'pname');
                                        if ($pname === $pold) $pname = '';
                                        $ctd = empty($pname) ? 'empty' : ''; // pretty view cell
                                        
                                        // если правило не редактируемое для текущего журнала
                                        // добавим к классу строки таблицы пометку, по которой потом
                                        // js удалит все комбобоксы из этих строк
                                        $class .= (get_param($rule,'jown') === '0' || $readonly) ? ' disabled' : '';
                                        
                                        $journals = get_param($authdata, 'journals');
                                        $addinfo = '';                                        
                                        if (get_param($rule, 'jown') === '0' && !$readonly) {
                                            $jowner = get_param($journals, get_param($rule,'jid'));
                                            $ownername = get_param($jowner,'name','');
                                            $ownername = empty($ownername) ? 'Заполняется из другого журнала' : "Заполняется через журнал '$ownername'";
                                            $addinfo = <<< AINFO
                                            <div class="pull-right">
                                                <abbr title="$ownername">?</abbr>
                                            </div>
AINFO;
                                        }
                                        
                                        echo <<< TR
                                        <tr class="$class" ruleid="$rid">
                                            <td class="$ctd"><strong>$pname</strong>$addinfo</td>
                                            <td class="mainuser">$select</td>
                                            <td class="selector standin">$select</td>
                                            <td class="selector trainee">$select</td>
                                        </tr>
TR;
                                        $pold = get_param($rule, 'pname');
                                    }
                                }
                           ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </form>
</div>

<!-- template for standin and trainee (don't remove)-->
<div id="template" class="form-group input-group nopad hidden">
    <input type="text" class="form-control" readonly/>
    <input type="hidden"/>
    <span class="input-group-btn">
        <button class="btn btn-default xremove" type="button">
            <i class="glyphicon glyphicon-remove"></i>
        </button>
    </span>
</div>

<div id="template-view" class="form-group nopad hidden">
    <input type="text" class="form-control" readonly/>
</div>

