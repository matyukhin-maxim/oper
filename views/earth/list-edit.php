<div class="row">
	<div class="panel panel-default">
		<div class="panel-heading strong">
			<i class="glyphicon glyphicon-flag"></i> &nbsp;
			Учет установленных заземлений
		</div>
		<div class="panel-heading clearfix text-center">
			<div class="col-xs-4">Оборудование</div>
			<div class="col-xs-1">Место</div>
			<div class="col-xs-1">Тип</div>
			<div class="col-xs-1">#</div>
			<div class="col-xs-1">Установлено</div>
			<div class="col-xs-2">Установил</div>
			<div class="col-xs-2"></div>
		</div>
		<div class="panel-body panel-message">
			<table class="table compact table-bordered">
				<tbody>
				<colgroup>
					<col class="col-xs-4">
					<col class="col-xs-1">
					<col class="col-xs-1">
					<col class="col-xs-1">
					<col class="col-xs-1">
					<col class="col-xs-2">
					<col class="col-xs-2">
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
			<form action="/earth/append/" method="post">
				<div class="row compact">
					<div class="col-xs-4">
						<input type="text" class="form-control input-sm" name="e_equip" required
						       placeholder="Оборудование" autocomplete="off">
					</div>
					<div class="col-xs-1"><?= $placeList; ?></div>
					<div class="col-xs-1"><?= $typesList; ?></div>
					<div class="col-xs-1">
						<input type="text" class="form-control input-sm" name="e_number" placeholder="Номер ПЗ">
					</div>
					<div class="col-xs-1">
						<input type="text" class="form-control input-sm datepicker mtime" name="e_date" required
						       readonly value="<?= date('d.m.Y'); ?>">
					</div>
					<div class="col-xs-2"><?= $demList; ?></div>
					<div class="col-xs-2 text-right">
						<input type="hidden" name="journal" value="<?= $journal_id; ?>">
						<button type="submit" class="wrap-button btn btn-primary btn-sm btn-block strong"
						        title="Установить заземление">
							<i class="glyphicon glyphicon-share-alt"></i>
							<span class="hidden-xs">Добавить</span>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

