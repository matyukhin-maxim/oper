<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 29.03.2016
 * Time: 9:53
 */

// Идентификаторы журналов, в которых есть раздел с заземлением
define('JRN_ORU',   25);    // ДЭМ ОРУ
define('JRN_MGK',   27);    // МПГК
define('JRN_CHU',   40);    // ДЭМ ЧТЭЦ

require_once 'models/JournalModel.php';

/** @property EarthModel $model */
class Earth extends Controller {

	private $journal_id;
	private $helper;
	private $editable;
	private $position;

	public function __construct() {
		parent::__construct();

		$this->helper = new JournalModel();

		$this->journal_id = filter_var(Session::get('jid'), FILTER_VALIDATE_INT);
		if (!$this->authdata) $this->redirect('auth/');
		if (!$this->journal_id) $this->redirect('auth/select/');

		if (!$this->isRoleGranted('ACE_EARTH_CONTROL')) {
			$this->appendDebug('Нет доступа к этому разделу!');
			$this->redirect(['back' => 1]);
		}

		$this->editable = $this->isRoleGranted('ACE_EARTH_CONTROL', $this->journal_id);

		// У каждого журнала свой список пользователей ДЭМов (кто устанавливает/снимает заземленя)
		// Явно пропишем id'шники этих должностей для каждого журнала
		$positions = [
			JRN_ORU => [197, 198, 199],  // ДЭМ ОРУ
			JRN_MGK => [197, 198, 199],  // МПГК
			JRN_CHU => [200],            // ДЭМ ЧТЭЦ
		];

		// Если id текущего журнала нет в вышестоящем списке, то редактировать заземления нельзя даже если есть право..
		//if (!in_array($this->journal_id, array_keys($positions))) $this->editable = false;
		$this->position = get_param($positions, $this->journal_id, []);
		if (!$this->position) $this->editable = false;

	}

	public function actionIndex() {

		$this->data['jname'] = $this->helper->getJournalFullName($this->journal_id);
		$this->data['subtitle'] = ' :: Установленные заземления';
		$this->render('index', false);


		$list = $this->model->getEarthList($this->journal_id);
		$types = $this->model->getEarthTypes();
		$place = $this->model->getEarthPlaces();
		$dems = $this->helper->getAllUsersByPositions($this->position, true);

		$affix = $this->editable ? '-edit' : '-view';

		$this->data['typesList'] = CHtml::drawCombo($types, null, [
			'default' => '-',
			'htmlOptions' => [
				'class' => 'form-control input-sm',
				'name' => 'e_type',
				'required' => true,
			],
		]);

		$this->data['placeList'] = CHtml::drawCombo($place, null, [
			'default' => '-',
			'htmlOptions' => [
				'class' => 'form-control input-sm',
				'name' => 'e_place',
			] + ($this->journal_id == JRN_MGK ? ['required' => true] : []), // Обязательно к заполнению для МПГК
		]);

		$options = CHtml::createTag('option', ['value' => ''], '-');
		foreach ($dems as $id => $user) $options .= CHtml::createTag('option', ['value' => $id], $user);
		$this->data['demList'] = CHtml::createTag('select', [
			'class' => 'form-control input-sm',
			'required' => true,
			'name' => 'e_dem',
		], $options);

		$this->data['earthlist'] = '';
		$this->listRender($list);

		$this->data['journal_id'] = $this->journal_id;

		// НСС и НСЭ - особый случай
		if ($this->journal_id < 3) {

			// во-первых, они только смотрят
			$this->editable = false;
			$affix = '-view';

			// но смотрят все разом
			$this->data['earthlist'] = '';

			// ОРУ
			$list = $this->model->getEarthList(JRN_ORU);
			$this->data['earthlist'] .= CHtml::createTag('tr', ['class' => 'info strong'], [
				CHtml::createTag('td', ['colspan' => 6], 'ОРУ ')
			]);
			$this->listRender($list);

			// МПГК
			$list = $this->model->getEarthList(JRN_MGK);
			$this->data['earthlist'] .= CHtml::createTag('tr', ['class' => 'info strong'], [
				CHtml::createTag('td', ['colspan' => 6], 'Главный корпус ')
			]);
			$this->listRender($list);
		}

		$this->render("list$affix", false);

		echo CHtml::createTag('div', ['class' => 'row'], [
			$this->drawSummary('ОРУ', JRN_ORU),
			$this->drawSummary('Главный корпус', JRN_MGK),
			//$this->drawSummary('ЧТЭЦ', JRN_CHU),
		]);

		$this->render('');
	}

	public function actionAppend() {

		// параметры нового заземления
		$params = filter_input_array(INPUT_POST, [
			'e_equip' => FILTER_SANITIZE_STRING,
			'e_type' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'min_range' => 1,
					'max_range' => 4,
					'default' => 1,
				],
			],
			'e_number' => FILTER_SANITIZE_STRING,
			'e_place' => FILTER_VALIDATE_INT,
			'e_date' => FILTER_SANITIZE_STRING,
			'e_dem' => FILTER_VALIDATE_INT,
			'journal' => FILTER_VALIDATE_INT,
		]);

		$params['e_date'] = date2mysql($params['e_date']);

		var_dump($params);

		$this->model->setupEarth($params);
		$this->redirect(['back' => 1]);
	}

	public function actionDelete() {

		$eid = filter_var(get_param($this->arguments, 'id'), FILTER_VALIDATE_INT);
		$jid = get_param($this->arguments, 'jid', $this->journal_id);

		if ($this->isPOST()) {
			// POSTом если сюда попали - значит снимаем заземление

			$param = filter_input_array(INPUT_POST, [
				'e_date' => FILTER_SANITIZE_STRING,
				'e_dem' => FILTER_VALIDATE_INT,
				'eid' => FILTER_VALIDATE_INT,
				'jid' => FILTER_VALIDATE_INT,
			]);

			$param['e_date'] = date2mysql($param['e_date']);
			$res = $this->model->takeoffEarth($param);
			if ($res) self::appendDebug('Информация сохранена', 1);

			$this->redirect(['back' => 1]);
			die;
		}

		$info = $this->model->getEarthInfo($eid, $jid);

		if (!$info) {

			// Если по id ничего не нашли, то скажем об этом пользователю (точнее этому жулику)
			$this->data['emessage'] = "Информация по запрошенному заземлению не найдена";
			echo $this->renderPartial('../error_modal');
			return;
		}

		$dems = $this->helper->getAllUsersByPositions($this->position, true); //  Список ДЕМ
		$options = CHtml::createTag('option', ['value' => ''], '-');
		foreach ($dems as $id => $user) $options .= CHtml::createTag('option', ['value' => $id], $user);
		$this->data['demList'] = CHtml::createTag('select', [
			'class' => 'form-control input-sm',
			'required' => true,
			'name' => 'e_dem',
		], $options);

		$this->data['earthDate'] = CHtml::createTag('input', [
			'class' => 'form-control input-sm datepicker mtime',
			'name' => 'e_date',
			'value' => date('d.m.Y'),
			'readonly' => true,
		]);

		$this->data['e_equip'] = get_param($info, 'equipment');
		$this->data['eid'] = $eid;
		$this->data['jid'] = $jid;

		echo $this->renderPartial('modal-delete');
	}

	public function drawSummary($title, $place) {

		// панелька с итогами
		$this->data['department'] = $title;
		$this->data['earthTotal'] = '';
		$total = $this->model->getEarthTotal($place);
		foreach ($total as $row) {
			$this->data['earthTotal'] .= CHtml::createTag('li', ['class' => 'list-group-item'], [
				CHtml::createTag('div', ['class' => 'pull-right strong'], get_param($row, 'res')),
				get_param($row, 'title'),
			]);
		}

		return $this->renderPartial('panel-summary');
	}

	private function listRender($list) {

		if (count($list) === 0) {
			$this->data['earthlist'] .= CHtml::createTag('tr', ['class' => 'warning strong text-center'], [
				CHtml::createTag('td', ['colspan' => 7], 'Нет данных')
			]);
		}

		foreach ($list as $item) {
			$eid = get_param($item, 'id', -1);

			$buttonOff = CHtml::createTag('button', [
				'class' => 'btn btn-default btn-block btn-sm',
				'data-toggle' => 'modal',
				'data-target' => '#universal',
				'data-keyboard' => false,
				'data-backdrop' => 'static',
				'href' => $this->generateURI( $this->selfurl . 'delete', ['id' => $eid, 'jid' => $this->journal_id]),
			], 'Снять');

			$this->data['earthlist'] .= CHtml::createTag('tr', ['class'=>'text-center'], [
				CHtml::createTag('td', ['class' => 'text-left'], get_param($item, 'equipment')),
				CHtml::createTag('td', null, get_param($item, 'place')),
				CHtml::createTag('td', null, get_param($item, 'shortname')),
				CHtml::createTag('td', null, get_param($item, 'num')),
				CHtml::createTag('td', null, get_param($item, 'odate')),
				CHtml::createTag('td', null, get_param($item, 'ou')),
				$this->editable ? CHtml::createTag('td', null, $buttonOff) : '',
			]);

		}
	}
}