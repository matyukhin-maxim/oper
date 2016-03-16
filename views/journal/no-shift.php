
<div class="row">
    <div class="col-xs-8 col-xs-offset-2">
        <div class="panel panel-default text-center">
            <div class="panel-body">
            <h4>Оперативная смена не принята.</h4>
            <?php if (get_param($gsc)): ?>
                
                Нажмите
                <?=
                CHtml::drawLink('<strong>сюда</strong>', array(
                    //'class' => 'alert-link',
                    'href' => '/journal/newshift/',
                    'data-toggle' => 'modal',
                    'data-target' => '#universal',
                ));
                ?>, чтобы принять смену.
                
                <br />
                <br />
                <div class="row">
                    <div class="col-xs-12">
                        <div class="alert alert-info text-center">
                            <i class="glyphicon glyphicon-info-sign"></i>
                            <span>
                                текущий пользователь будет указн в качестве <strong>принявшего</strong> предыдущую смену
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>
