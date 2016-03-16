
    <div class="panel panel-info">
        <div class="panel-heading">
            <span class="glyphicon glyphicon-wrench">&nbsp;</span>
            Состояние оборудования
            <?php if (get_param($this->data, 'btn_equip')): ?>
                <div class="btn-group pull-right">
                    <button class="btn btn-default btn-outline btn-xs" id="btn-equip" title="Сохранить">
                        <span class="glyphicon glyphicon-floppy-disk btn-xs"></span>
                    </button>
                </div>
            <?php endif;?>
        </div>
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <?php 
                $cnt = 0;
                foreach ($equip as $item) {
                    printf('<li class="%s"><a href="#eq-%d" data-toggle="tab">%s</a></li>' . PHP_EOL,
                            (++$cnt === 1) ? 'active' : '',
                            get_param($item,'id'),
                            get_param($item, 'name'));
                }?>
            </ul>
            <div class="col-sm-12 equip">
                <div class="tab-content">
                    <?php
                    $cnt = 0;
                    foreach ($equip as $item) {
                        printf('<div class="tab-pane fade %s" id="eq-%d">
                                <textarea name="equip[%d]" class="flot-chart-content">%s</textarea></div>' . PHP_EOL,
                                (++$cnt === 1) ? 'in active' : '',
                                get_param($item,'id'),
                                get_param($item,'id'),
                                get_param($item,'message'));
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

