<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 06.04.2016
 * Time: 14:05
 */

/** @property LeakModel $model */
class Leak extends Controller {

	public function actionIndex() {

		$controls = [
			'date' => 'Дата',
			'block' => 'Номер блока',
			'calctime' => 'Время замера',
			'b-pressure' => 'Начальное давление',
			'e-pressure' => 'Конечное давление',
			'b-gas' => 'Холодный газ начальный',
			'e-gas' => 'Холодный газ конечный',
			'result' => 'Величина утечки',
		];

		foreach ($controls as $id => $title) {

			$this->data['controls'] .= CHtml::createTag('div', ['class' => 'row'], [
				CHtml::createTag('div', ['class' => 'col-xs-8 control-label text-right'], "$title :"),
				CHtml::createTag('div', ['class' => 'col-xs-4'], [
					CHtml::createTag('input', [
						'class' => 'form-control text-right ' . ( $id === 'date' ? 'mtime datepicker' : ''),
						'type' => 'text',
						'id' => $id,
						'name' => $id,
					]),
				]),
			]);
		}

		$this->js[] = 'leaks';
		$this->render('form');
	}

	public function actionData() {

		$block = filter_var(get_param($this->arguments, 'id', 1), FILTER_VALIDATE_INT) ?: 1;
		if (!$this->isAjax()) $this->redirect(['back' => 1]);

		$res = $this->model->getList($block);
		foreach ($res as $info) echo CHtml::createTag('tr', null, [
			CHtml::createTag('td', ['class' => 'col-xs-4 text-center'], date2format(get_param($info, 'date'))),
			CHtml::createTag('td', ['class' => 'col-xs-8'], sprintf("%.2f%%",100 * get_param($info, 'value'))),
		]);
	}

	public function actionSave() {

		if (!$this->isAjax()) $this->redirect(['back' => 1]);

		$params = filter_input_array(INPUT_POST, [
			'date' => FILTER_SANITIZE_STRING,
			'block' => FILTER_VALIDATE_INT,
			//'calctime' => FILTER_VALIDATE_FLOAT,
			//'b-pressure' => FILTER_VALIDATE_FLOAT,
			//'e-pressure' => FILTER_VALIDATE_FLOAT,
			//'b-gas' => FILTER_VALIDATE_FLOAT,
			//'e-gas' => FILTER_VALIDATE_FLOAT,
			'res' => FILTER_VALIDATE_FLOAT,
		]);

		$params['date'] = date2mysql($params['date']);
		echo $this->model->saveLeak($params) ? 'ok' : '';
	}
}