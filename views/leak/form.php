<br><br><br>

<div class="row">
	<div class="col-md-4">
		<div class="panel panel-default">
			<div class="panel-heading strong">Расчет утечек</div>
			<div class="panel-body">
				<?= $controls; ?>
			</div>
			<div class="panel-footer">
				<input type="text" class="hidden" name="res">
				<button id="btn-calc" class="btn btn-block btn-primary">Расчитать</button>
				<button id="btn-save" class="btn btn-block btn-default strong">Сохранить</button>
			</div>
		</div>
		<div class="alert alert-info">
			<div class="row text-center h4 strong">Показания манометра</div>
			<hr>
			<div class="row">
				<div class="col-xs-9 italic text-right control-label">Предел измерений кгс/см<sup>2</sup></div>
				<div class="col-xs-3">
					<input type="text" class="form-control text-right" id="pmax" value="10">
				</div>
			</div>
			<div class="row">
				<div class="col-xs-9 italic text-right control-label">Число делений единицы</div>
				<div class="col-xs-3">
					<input type="text" class="form-control text-right" id="plimit" value="255">
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-8">
		<div class="panel panel-default">
			<div class="panel-heading">История утечек</div>
			<div class="panel-heading">
				<div class="btn-group btn-group-justified">
					<a href="#" class="btn btn-default view" data-block="1">Блок №1</a>
					<a href="#" class="btn btn-default view" data-block="2">Блок №2</a>
					<a href="#" class="btn btn-default view" data-block="3">Блок №3</a>
				</div>
			</div>
			<div class="panel-body panel-message">
				<table class="table table-bordered">
					<tbody id="archive"></tbody>
				</table>
			</div>
		</div>
	</div>
</div>