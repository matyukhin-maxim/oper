<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true" title="Закрыть">&times;</button>
	<h4 class="modal-title">
		Снятие заземления &nbsp;
		<small class="text-danger strong"><?= $e_equip; ?></small>
	</h4>
</div>
<form action="/earth/delete/" method="post">
	<div class="modal-body">
		<div class="form-group row">
			<div class="col-xs-4 text-right">
				<label class="control-label">Дата снятия :</label>
			</div>
			<div class="col-xs-8">
				<?= $earthDate;?>
			</div>
		</div>
		<div class="form--group row">
			<div class="col-xs-4 text-right">
				<label class="control-label">Снял ДЭМ :</label>
			</div>
			<div class="col-xs-8">
				<?= $demList; ?>
			</div>
		</div>
	</div>
	<div class="modal-footer clearfix">
		<div class="btn-group pull-right">
			<input type="hidden" name="eid" value="<?= $eid; ?>">
			<input type="hidden" name="jid" value="<?= $jid; ?>">
			<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			<button type="submit" class="btn btn-primary">Сохранить</button>
		</div>
	</div>
</form>