
<div class="row">
    <div class="col-md-12">
        <h2 class="page-header">
            <?= $jname; ?>
            <small>:: Состав смены</small>
        </h2>
    </div>
</div>

<form action="/dashboard/postprocess/" method="post">
    <div class="row">
        <div class="col-md-12">
            <button type="submit" name="savecomp" class="btn btn-success btn-block">Сохранить</button>
        </div>
    </div>
    <div class="row">
        <?php
        foreach ($positions as $position) {
            $this->data['position'] = $position;
            $this->data['select'] = array();
            foreach ($compositions as $composition) {
                if ($composition['posid'] === $position['position_id']) {
                    $this->data['select'][] = $composition;
                }
            }
            echo $this->renderPartial('tmpl_position');
        }
        ?>
    </div>
    <div class="row">
        <div class="col-md-12">
            <button type="submit" name="savecomp" class="btn btn-success btn-block">Сохранить</button>
        </div>
    </div>
</form>
