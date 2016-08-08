<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 16.06.2016
 * Time: 8:41
 */
class DOTModel extends CSQLServer {

	public function __construct() {
		$conn = [
			'host' => 'dot.asu.ngres',
			'user' => 'dapo',
			'pass' => '514',
			'base' => ''
		];

		parent::__construct($conn);
	}

	public function getTemperature() {

		$res = $this->selectAll('
			select o.dt, l.id_server, l.val, s.adres, s.comment
			from ( select
				id_server, max([data]) dt
			from ROBOT.dbo.log
			group by id_server) o
			left join ROBOT.dbo.log l on o.id_server = l.id_server and o.dt = l.data
			left join ROBOT.dbo.servers s on l.id_server = s.id
			order by s.id
		');

		array_walk_recursive($res, 'charsetChange');
		return $res;
	}

}