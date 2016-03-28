<div class="row">
	<div class="panel panel-default">
		<div class="panel-heading strong">
			<i class="glyphicon glyphicon-flag"></i> &nbsp;
			Учет установленных заземлений
		</div>
		<div class="panel-heading clearfix text-center">
			<div class="col-xs-5">Оборудование</div>
			<div class="col-xs-1">Тип</div>
			<div class="col-xs-1">#</div>
			<div class="col-xs-2">Дата установки</div>
			<div class="col-xs-2">Установил</div>
			<div class="col-xs-1"></div>
		</div>
		<div class="panel-body panel-message">
			<table class="table compact table-bordered">
				<tbody>
				<colgroup>
					<col class="col-xs-5">
					<col class="col-xs-1">
					<col class="col-xs-1">
					<col class="col-xs-2">
					<col class="col-xs-2">
					<col class="col-xs-1">
				</colgroup>
				<?= $earthlist; ?>
				</tbody>
			</table>
		</div>
		<div class="panel-footer">
			<div class="row">
				<label class="col-xs-12">
					<i class="glyphicon glyphicon-cog"></i>
					Установка нового заземления
				</label>
			</div>
			<form action="/journal/earthAdd/" method="post">
				<div class="row compact">
					<div class="col-xs-5"><?= $earthEquip; ?></div>
					<div class="col-xs-1"><?= $earthTypes; ?></div>
					<div class="col-xs-1"><?= $earthNumber; ?></div>
					<div class="col-xs-2"><?= $earthDate; ?></div>
					<div class="col-xs-2"><?= $earthUsers; ?></div>
					<div class="col-xs-1 text-right">
						<input type="hidden" name="jid" value="<?= $jid;?>">
						<button type="submit" class=" wrap-button btn btn-primary btn-sm btn-block" title="Установить заземление">
							Установить
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

