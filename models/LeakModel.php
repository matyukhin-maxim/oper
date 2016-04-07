<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 07.04.2016
 * Time: 14:50
 */
class LeakModel extends Model {

	public function getList($block = 1) {

		return $this->select('
			SELECT * FROM leaks
			WHERE block = :blockid
				AND deleted = 0
			ORDER BY date DESC ', ['blockid' => $block]);
	}

	public function saveLeak($params) {

		$cnt = 0;
		$this->select('INSERT INTO leaks (block, date, value) VALUES (:block, :date, :res)', $params, $cnt);

		return $cnt > 0;
	}
}