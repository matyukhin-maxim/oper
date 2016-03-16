
<div class="panel panel-primary">
    <div class="panel-heading">
        <span class="glyphicon glyphicon-cog">&nbsp;</span>
        Состав смены
    </div>
    <div class="panel-body">
        <div class="list-group">
            <?php foreach ($positions as $pos) : ?>
                <div class="list-group-item">
                    <strong>
                        <?= get_param($pos, 'name', '?'); ?>
                    </strong>
                    <div class="badge badge-success pull-right">
                        <?= get_param($pos,'cnt'); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <a class="btn btn-default btn-block" href="compositions/">Изменить состав смены</a>
    </div>
</div>
