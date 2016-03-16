
<div class="row">

    <div class="col-xs-12 col-xs-offset-0 col-md-8 col-md-offset-2">
        <?php if (count($journals)) : ?>

            <div class="panel panel-default login-panel">
                <div class="panel-heading">Выбор оперативного журнала</div>
                <div class="panel-body">
                    <div class="list-group">
                        <?php
                        foreach ($journals as $item) {
                            $link = '/auth/change/id/' . get_param($item, 'id');
                            $name = get_param($item, 'description', '???');
                            $class = get_param($item, 'em', 0) > 0 ? 'text-primary' : '';

                            echo <<< JLINK
                                <a href="$link" class="list-group-item text-center">
                                    <h4 class="$class">$name</h4>
                                </a>
JLINK;
                        }?>
                    </div>
                </div>
            </div>


        <?php else: ?>

            <div class="alert alert-info">
                Для Вашей учетной записи не доступен ни один оперативный журнал.
                <br/>
                Обратитесь в <strong><abbr title="51-30, 50-98, 55-88">отдел ОИТ</abbr></strong>, 
                и сообщите, доступ к каким журналам вам необходим, и для каких целей.
            </div>

        <?php endif; ?>
    </div>

</div>
