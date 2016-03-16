<div class="col-md-6">
<div class="panel panel-info">
    <div class="panel-heading">
        <?= get_param($position, 'name'); ?>
        <?php //get_param($position, 'description'); ?>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ФИО сотрудника</th>
                        <th>Стажер</th>
                        <th>Дублер</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $cnt = 0;
                    $keys = array('title' => 'fio',);
                    foreach ($select as $current) {
                        $cnt++;
                        $combo = CHtml::drawCombo(get_param($users, get_param($position,'position_id')),
                                                  get_param($current,'id'), 
                                                  get_param($current,'user_id'), $keys, true);
                        echo <<< TMPL
                        <tr>
                            <td>$cnt</td>
                            <td>$combo</td>
                            <td>-</td>
                            <td>-</td>    
                        </tr>
TMPL;
                    }?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>