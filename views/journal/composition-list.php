
<div class="row">
    <div class="col-xs-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <i class="glyphicon glyphicon-bell">&nbsp;</i>
                Общий состав смены станции
            </div>
            <div class="panel-body panel-message">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="col-xs-0">#</th>
                                <th class="col-xs-4">Должность / ФИО сотрудника</th>
                                <th class="col-xs-4">Стажер</th>
                                <th class="col-xs-4">Дублер</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach ($cdata as $posid => $rules) {
                                $first = get_param($rules, 0);
                                $pname = get_param($first,'pname');
                                
                                echo <<< PLINE
                                <tr>
                                    <td class="alert-info" colspan="4">$pname</td>
                                </tr>
PLINE;
                                foreach ($rules as $user) {
                                    $opt = $user['list'];
                                    echo <<< PLINE
                                    <tr>
                                        <td class="text-center" colspan="2">$opt</td>
                                    </tr>
PLINE;
                                }
                                
                            }?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

