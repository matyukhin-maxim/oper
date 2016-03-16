<?php if (!defined('ROOT')) header("Location: ../"); ?>

<div class="panel panel-red">
    <div class="panel-heading">
        <span class="glyphicon glyphicon-info-sign">&nbsp;</span>
        Информация о смене
        <div class="pull-right">
            <div class="btn-group">
                <button class="btn btn-default btn-outline btn-xs dropdown-toggle" data-toggle="dropdown">
                    <span class="glyphicon glyphicon-chevron-down btn-xs"></span>
                </button>
                <ul class="dropdown-menu pull-right">
                    <li class="disabled"><a href="#">Отчет по смене</a></li>
                    <?php if (get_param($this->data, 'btn_close')) : ?>
                        <li class="divider"></li>
                        <li><a href="closeshift/">Сдать смену</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>    
    <div class="panel-body">
        <div class="list-group">
            <div class="list-group-item">
                Дата смены:
                <span class="pull-right text-danger"><strong>
                        <?= get_param($shift, 'dopen', 'n/a'); ?>
                    </strong></span>
            </div>
            <div class="list-group-item">
                Смена:
                <span class="pull-right text-danger"><strong>
                        <?= get_param($shift, 'period', 'n/a'); ?>
                    </strong></span>
            </div>
            <div class="list-group-item">
                Вахта:
                <span class="pull-right text-danger"><strong>
                        <?= get_param($shift, 'abbr', 'n/a'); ?>
                    </strong></span>
            </div>
            <div class="list-group-item">
                Смену принял:
                <span class="pull-right text-danger"><strong>
                        <?= get_param($shift, 'fio', 'n/a'); ?>
                    </strong></span>
            </div>
        </div>
    </div>
</div>

