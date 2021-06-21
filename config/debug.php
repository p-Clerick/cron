<?php

return CMap::mergeArray(
	require(dirname(__FILE__).'/main.php'),
	array(
		'modules'=>array(
			'report' => array(
				'connection' => array(
					'host' => '127.0.0.1',
					'port' => '5984',
					'user' => 'test',
					'password' => 'test',
				),
			),
			'gii'=>array(
				'class'=>'system.gii.GiiModule',
				'password'=>'Enter Your Password Here',
				// If removed, Gii defaults to localhost only. Edit carefully to taste.
				'ipFilters'=>array('127.0.0.1','::1'),
			),
		),

		'components'=>array(
			/*
			'fixture'=>array(
				'class'=>'system.test.CDbFixtureManager',
			),
			*/
			/* uncomment the following to provide test database connection
			'db'=>array(
				'connectionString'=>'DSN for test database',
			),
			*/
			'db'=>array(
				'connectionString' => 'mysql:host=193.109.144.51;dbname=MakLutsk',
				'emulatePrepare' => true,
				'username' => 'vantes',
				'password' => 'hjphj,ybr',
				//'charset' => 'utf8',
			),

			'log'=>array(
				'class'=>'CLogRouter',
				'routes'=>array(
					array(
						'class'=>'CProfileLogRoute',
						'levels'=>'profile',
						'enabled'=>true
					),
					array(
						'class' => 'CWebLogRoute',
						'categories' => 'application',
						'showInFireBug' => true
					),
					array(
						'class' => 'CWebLogRoute',
						'categories' => 'application',
						'levels'=>'error, warning, trace, profile, info',
					),
				),
			),
		),
	),
);
