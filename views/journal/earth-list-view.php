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
			<div class="col-xs-3">Установил</div>
		</div>
		<div class="panel-body panel-message">
			<table class="table compact table-bordered">
				<tbody>
				<colgroup>
					<col class="col-xs-5">
					<col class="col-xs-1">
					<col class="col-xs-1">
					<col class="col-xs-2">
					<col class="col-xs-3">
				</colgroup>
				<?= $earthlist; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

