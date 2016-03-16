<?php if (!defined('ROOT')) header("Location: ../"); ?>

<div class="panel panel-yellow">
    <div class="panel-heading">
        <span class="glyphicon glyphicon-tags">&nbsp;</span>
        Сообщения
        
        <?php if (get_param($this->data, 'btn_new')): ?>
        <div class="btn-group pull-right">
            <button class="btn btn-default btn-outline btn-xs" data-toggle="modal" 
                    data-target="#new-message-dialog" title="Новое сообщение" href="newmessage/">
                <span class="glyphicon glyphicon-plus btn-xs"></span>
            </button>
        </div>
        <?php endif;?>
    </div>
    <div class="panel-body panel-message">

        <div id="new-message-dialog" class="modal fade" tabindex="-1" 
             aria-hidden="true" role="dialog" data-backdrop="static">
            <div class="modal-dialog modal-dialog-center">
                <div class="modal-content">
                </div>
            </div>
        </div>
        <div id="msg-body">
            <?= $this->drawMessagesList()?>
        </div>
    </div>
</div>

