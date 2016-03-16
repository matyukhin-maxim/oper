
<div class="row">
    <div class="col-xs-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                Состав смены
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-hover">
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
                            foreach ($plist as $key => $item) {
                                echo <<< POS_LINE
                                <tr>
                                    <td class="alert-warning" colspan="4"><strong>$item</strong></td>
                                </tr>
POS_LINE;
                                $persons = get_param($rlist, $key, array());
                                $keys = array('title' => 'fio',);
                                foreach ($persons as $cnt => $ritem) {
                                    
                                    $uinfo = get_param($ulist, get_param($ritem, 'user_id'));
                                    $tinfo = get_param($ulist, get_param($ritem, 'trainee_id'));
                                    $sinfo = get_param($ulist, get_param($ritem, 'standin_id'));
                                    
                                    
                                    if(!empty($editmode)) {
                                        $cb_user    = CHtml::drawCombo(get_param($positions, $key), 'uuu', get_param($uinfo, 'id'), $keys, true);
                                        $cb_trainee = CHtml::drawCombo(get_param($positions, $key), 'uuu', get_param($tinfo, 'id'), $keys, true);
                                        $cb_standin = CHtml::drawCombo(get_param($positions, $key), 'uuu', get_param($sinfo, 'id'), $keys, true);
                                    } else {
                                        $cb_user    = get_param($uinfo, 'fio', '-');
                                        $cb_trainee = get_param($tinfo, 'fio', '-');
                                        $cb_standin = get_param($sinfo, 'fio', '-');
                                    }
                                    
                                    // условие для НСС (стажера и дублера у него нет) [id должности = 1]
                                    if ($key === 1) {
                                        $cb_trainee = '';
                                        $cb_standin = '';
                                    }
                                   
                                    $pnum = $cnt + 1;
                                    echo <<< RULE_LINE
                                    <tr>
                                        <td>$pnum</td>
                                        <td>$cb_user</td>
                                        <td>$cb_trainee</td>
                                        <td>$cb_standin</td>
                                    </tr>
RULE_LINE;
                                }
                            }?>  
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

