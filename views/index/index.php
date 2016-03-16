<?php if (!defined('ROOT')) header("Location: ../"); ?>

    <!--
    <div class="row">
        <div class="col-md-12 col-lg-12 col-sm-12">
            <h2 class="page-header">
                <?php //print_r($_SESSION);?>
            </h2>
        </div>
    </div>
    -->
    

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <?php if (count($authdata['grants']) > 0): ?>

                <div class="panel panel-info">
                    <div class="panel-heading">Выбор оперативного журнала</div>
                    <div class="panel-body">
                        <div class="list-group">
                            <?php foreach ($authdata['grants'] as $key => $value) {
                            printf('<h4><a href="%s" class="list-group-item">%s</a></h4>',
                                    '/auth/change/id/' . $key, $value);}
                            ?>
                        </div>
                    </div>
                </div>

            <?php else: ?>

                <div class="alert alert-info">
                    Для Вашей учетной записи не доступен ни один оперативный журнал.
                    <br/>
                    Обратитесь в <strong><abbr title="51-30, 50-98, 55-88">отдел ОИТ</abbr></strong>, 
                    если считаете это чудовищной несправедливостью.
                </div>

            <?php endif; ?>
        </div>
    </div>

