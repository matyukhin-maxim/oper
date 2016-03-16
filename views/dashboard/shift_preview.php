<?php if (!defined('ROOT')) header("Location: ../"); ?>


<div class="row">
    <div class="col-md-12">
        <h2 class="page-header">
            <?= $jname; ?>
            <small>:: Просмотр архивной смены</small>
        </h2>
    </div>
</div>

<div class="row">
    <div class="col-sm-7 col-md-8">
        <?php $this->drawMessages(); ?>
        <?php //echo $this->renderPartial('equipment'); ?>
    </div>
    <div class="col-sm-5 col-md-4">
        <?php $this->drawInfo(); ?>
        <?php $this->drawSignatures(); ?>
    </div>
</div>

