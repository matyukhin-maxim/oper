
<div class="row">
    <form action="" id="comp-form">
    <div class="col-xs-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <i class="glyphicon glyphicon-bell">&nbsp;</i>
                Общий состав смены станции
            </div>
            <div class="panel-body panel-message">
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
                            foreach ($cdata as $posid => $rules) {
                                $first = get_param($rules,0);
                                $pname = get_param($first, 'pname');
                                $class = ++$cnt%2 ? 'active' : '';
                                foreach ($rules as $user) {
                                    //$pname = get_param($user,'pname');
                                    $options  = get_param($user,'select');
                                    $standins = get_param($user,'standin');
                                    $trainees = get_param($user,'trainee');
                                    $ruleid = get_param($user, 'ruleid');
                                    
                                    echo <<< PLINE
                                    <tr class="$class" ruleid="$ruleid">
                                        <td><strong>$pname</strong></td>
                                        <td class="text-top">$options</td>
                                        <td class="text-top">$standins</td>
                                        <td class="text-top">$trainees</td>
                                    </tr>
PLINE;
                                    $pname = '';
                                }
                            }?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </form>
</div>

<div class="row">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="pull-right">
                <?= get_param($btnback);?>
                <?= get_param($btnsave);?>
            </div>
        </div>
    </div>
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