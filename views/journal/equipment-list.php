
<div class="row">
    
    <div class="panel panel-primary">
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
                $tabclass = 'active';
                foreach ($equip as $item) {
                    $link = '#eq-' . get_param($item, 'id');
                    $title = get_param($item, 'name');
                    echo <<< EQUIPTAB
                    <li class="$tabclass">
                        <a href="$link" data-toggle="tab" class="tab-title">$title</a>
                    </li>
EQUIPTAB;
                    $tabclass = '';
                }?>
            </ul>
            <div class="equip">
                <div class="tab-content">
                    <?php
                    $tabclass = 'in active';
                    foreach ($equip as $item) {
                        $id = get_param($item, 'id');
                        $content = get_param($item, 'message');
                        echo <<< EQUIPCONTENT
                        <div class="tab-pane fade $tabclass" id="eq-$id">
                            <textarea name="equip[$id]" class="equip-content">$content</textarea>
                        </div>
EQUIPCONTENT;
                        $tabclass = '';
                    }?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="pull-right">
                <?= get_param($btnback);?>
                <a href="" id="btn-equip" class="btn btn-primary">
                    <i class="glyphicon glyphicon-pencil"></i>
                    Сохранить
                </a>
            </div>
        </div>
    </div>
    
</div>

