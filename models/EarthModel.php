<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 29.03.2016
 * Time: 10:05
 */
class EarthModel extends Model {

	public function getEarthList($journal_id) {

		$res = $this->select('
			SELECT
				c.id, c.equipment, t.shortname, c.num, p.description place,
				get_user_short(c.user_on) ou,
				date_format(c.date_on, "%d.%m.%Y") odate
			FROM earth_control c
			LEFT JOIN earth_type t ON c.etype_id = t.id
			LEFT JOIN earth_place p on c.place_id = p.id
			WHERE c.deleted = 0
				AND c.journal_id = :jid
				AND c.date_off IS NULL
			ORDER BY t.id, c.date_on DESC ', ['jid' => $journal_id]);

		return $res;
	}

	public function getEarthTypes() {

		$res = $this->select('SELECT id, shortname title FROM earth_type');
		//return array_column($res, 'title', 'id');
		return $res;
	}

	public function setupEarth($params) {

		$res = $this->select('
			REPLACE INTO earth_control (id, equipment, etype_id, num, date_on, user_on, journal_id, place_id)
			VALUES (NULL, :e_equip, :e_type, :e_number, :e_date, :e_dem, :journal, :e_place)
			', $params);

		return $res;
	}

	public function getEarthInfo($eid, $jid) {

		$result = $this->select('
			SELECT * FROM earth_control
				WHERE id = :eid
					AND journal_id = :jid
					AND  deleted = 0', [
			'eid' => $eid,
			'jid' => $jid,
		]);

		return get_param($result, 0);
	}

	public function takeoffEarth($param) {

		$res = 0;
		$this->select('
			UPDATE earth_control
			SET date_off = :e_date, user_off = :e_dem
			WHERE journal_id = :jid
				AND id = :eid', $param, $res);

		return $res > 0;
	}

	public function getEarthTotal($jid) {

		return $this->select('
			SELECT et.description title, ifnull(o.cnt, 0) res
			FROM earth_type et
			LEFT JOIN (SELECT etype_id, count(*) cnt
			FROM earth_control ec
			WHERE ec.journal_id = :journal
				AND ec.date_off IS NULL
				AND ec.deleted = 0
			GROUP BY 1) o ON et.id = o.etype_id', ['journal' => $jid]);
	}

	public function getEarthPlaces() {

		return $this->select('SELECT id, description title FROM earth_place WHERE deleted = 0');
	}
}