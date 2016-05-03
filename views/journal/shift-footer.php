
<div class="row">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-2 text-center">
                    <strong>С разрешения главного инженера</strong>
                    <input type="text" class="form-control mtime" readonly id="tpicker" value="<?= get_param($time_agree, null); ?>"/>
                </div>
                <div class="col-xs-6">
                    <div class="row">
                        <div class="col-xs-6 text-right"><strong>Смену сдал :</strong></div>
                        <div class="col-xs-6">
                            <?= get_param($shift, 'cu', 'не указан'); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 text-right"><strong>Смену принял :</strong></div>
                        <div class="col-xs-6">
                            <?= get_param($shift, 'ou', '-'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-xs-4">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="btn-group btn-group-justified">
                                <?php

                                // Кнопки состава смены и оборудования
                                echo $compBtn;
                                echo $devsBtn;

                                    $extlist = get_param($extgroup, null, array());
                                    foreach ($extlist as $btn) {
                                        echo "$btn\n";
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <?= get_param($link_close); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
	            <div class="col-md-4">
		            <div class="btn-group">
			            <?= $toolbar;?>
		            </div>
	            </div>
	            <div class="col-md-8 pull-right">
		            <div class="btn-group btn-group-justified">
			            <?php
			            $btnlist = get_param($printgroup, null, array());
			            foreach ($btnlist as $btn) {
				            echo "$btn\n";
			            }
			            ?>
		            </div>
	            </div>
            </div>
        </div>
    </div>
</div>