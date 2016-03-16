
<div class="row">
    <div class="well well-sm">
        <div class="row semi-semi-huge">
            <div class="col-xs-4 text-center">
                <strong>Дата : </strong>
                <?= date2human(get_param($shift, 'dopen', 'n/a')); ?>
            </div>
            <div class="col-xs-4 text-center">
                <strong>Смена : </strong>
                <?= get_param($shift, 'period', 'n/a'); ?>
            </div>
            <div class="col-xs-4 text-center">
                <strong>Вахта : </strong>
                <?= get_param($shift, 'abbr', 'n/a'); ?>
            </div>
        </div>
    </div>
</div>
