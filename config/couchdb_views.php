<?php

return array(
	'params' => array(
		'dbs' => array(
			array(
				'db_name' => 'schedule_lutsk',
				'views' => array(
					array('connection', 'all'),
					array('worktime', 'by_date_bort_schedule'),
				),
			),

			array(
				'db_name' => 'map_lutsk',
				'views' => array(
					array('mileage', 'all'),
					array('bortschedule', 'all'),
					array('speedmode', 'count'),
				),
			),
		),
	)
);
