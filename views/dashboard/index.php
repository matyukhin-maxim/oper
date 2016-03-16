<?php if (!defined('ROOT')) header("Location: ../"); ?>


<div class="row">
    <div class="col-md-12">
        <h2 class="page-header">
            <?= $jname; ?>
            <small>:: Текущая смена</small>
        </h2>
    </div>
</div>
<div class="row">
    <?php if ($shift): ?>
        <div class="col-sm-7 col-md-8 col-lg-8">
            <?php $this->drawMessages(); ?>
            <?php $this->drawEquip(); ?>
        </div>
        <div class="col-sm-5 col-md-4 col-lg-4">
            <?php $this->drawInfo(); ?>
            <?php $this->drawCompositionsPanel(); ?>
        </div>
    <?php else: ?>
        <div class="col-md-8 col-md-offset-2">
            <div class="alert alert-success text-center">
                Оперативная смена не открыта.
                <?php if (get_param($this->data, 'btn_open')): ?>
                    Нажмите
                    <a class="alert-link" href="newshift/" data-toggle="modal" data-target="#new-shift-dialog">сюда</a>,
                    для создания новой смены.
                <?php endif; ?>
            </div>
            <div id="new-shift-dialog" class="modal fade" tabindex="-1" 
                 aria-hidden="true" role="dialog" data-backdrop="static">
                <div class="modal-dialog modal-dialog-center">
                    <div class="modal-content">
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<div class="row">
    
</div>
