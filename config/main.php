<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return CMap::mergeArray(
	require(dirname(__FILE__).'/userfiles.php'),
	array(
		'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
		'name'=>'My Web Application',

		// preloading 'log' component
		'preload'=>array('log'),

		'defaultController'=>'home',

		// autoloading model and component classes
		'import'=>array(
			'application.models.*',
			'application.components.*',
		),

		'modules'=>array(
			'report' => array(
				'connection' => array(
					'host' => '193.109.144.51',
					'port' => '5984',
					'user' => 'report',
					'password' => 'eENdwUaEKa',
				),
				'mapDb' => 'map_lutsk',
				'scheduleDb' => 'schedule_lutsk',
				'speedmode' => array(
					'intervals' => array(0, 1, 5, 15, 30, 60, 200),
				),
			),
		),

		// application components
		'components'=>array(
			'user'=>array(
				// enable cookie-based authentication
				'allowAutoLogin'=>true,
				'class' => 'WebUser',
				'loginUrl'=>'/auth',
				'authTimeout'=>60*60
			),
			// uncomment the following to enable URLs in path-format

			'urlManager'=>array(
				'urlFormat'=>'path',
				'showScriptName'=>false,
				'rules'=>array(
					array('graph/setCarrier', 'pattern'=>'graph/<id:\d+>/<carrier:\d+>', 'verb'=>'PUT'),
					array('home/guest', 'pattern'=>'guest', 'verb'=>'GET'),
					array('calculateGrafik/update', 'pattern'=>'schedule/<id:\d+>', 'verb'=>'PUT'),
					array('<_c>/create', 'pattern'=>'<_c:\w+>', 'verb'=>'POST'),
					array('<_c>/read', 'pattern'=>'<_c:\w+>/?<id:\d+>?', 'verb'=>'GET'),
					array('<_c>/update', 'pattern'=>'<_c:\w+>/<id:\d+>', 'verb'=>'PUT'),
					array('<_c>/delete', 'pattern'=>'<_c:\w+>/<id:\d+>', 'verb'=>"DELETE"),
					array('tree/read', 'pattern'=>'tree/<level:\d+>?/<id:\d+>?', 'verb'=>'GET'),
					array('calculateGrafik/create', 'pattern'=>'grafik/<id:\d+>/calculate', 'verb'=>'POST'),
					array('calculateGrafik/read', 'pattern'=>'grafik/<id:\d+>/calculate', 'verb'=>'GET'),
					array('calculateGrafik/delete', 'pattern'=>'grafik/<id:\d+>/deleteSchedule/<type:\d+>', 'verb'=>'DELETE'),
					array('report/<_c>/update', 'pattern'=>'report/<_c:\w+>/<id:\d+>', 'verb'=>'PUT'),
					array('report/<_c>/read', 'pattern'=>'report/<_c:\w+>/<level:\d+>/<node:\d+>/<from:(\d{4}-\d{2}-\d{2})>:<to:(\d{4}-\d{2}-\d{2})>', 'verb'=>'GET'),
					array('report/chart/<_c>Chart/create', 'pattern'=>'report/chart/<_c:\w+>/<chartType:.+>', 'verb'=>'POST'),

					array('RouteCalculationStations/read', 'pattern'=>'Route/<id:\d+>/CalculationStations', 'verb'=>'GET'),
					array('RouteHistoryReviewId/create', 'pattern'=>'RouteHistoryReviewId/<id:\d+>/controlpoint', 'verb'=>'POST'),
					array('RouteReviewTime/create', 'pattern'=>'RouteReviewTime/<id:\d+>/editing', 'verb'=>'POST'),
					array('RouteActive/read', 'pattern'=>'RouteActive/<id:\d+>/comment', 'verb'=>'GET'),
					array('RouteActive/create', 'pattern'=>'RouteActive/<id:\d+>/active', 'verb'=>'POST'),
					array('RouteGraphOrder/read', 'pattern'=>'RouteGraphOrder/<id:\d+>/', 'verb'=>'GET'),
					array('RouteGraphOrder/create', 'pattern'=>'RouteGraphOrder/<id:\d+>/editing', 'verb'=>'POST'),
					array('Dayinterval/create', 'pattern'=>'Dayinterval/<id:\d+>/editing', 'verb'=>'POST'),
					array('RouteTypeView/read', 'pattern'=>'RouteTypeView/<id:\d+>/', 'verb'=>'GET'),
					array('ReysOrder/create', 'pattern'=>'ReysOrder/<id:\d+>/head', 'verb'=>'POST'),
					array('ReysOrder/read', 'pattern'=>'ReysOrder/<id:\d+>', 'verb'=>'GET'),
					array('PointDistanse/create', 'pattern'=>'PointDistanse/<id:\d+>/editing', 'verb'=>'POST'),
					array('DayIntervalRoute/read', 'pattern'=>'DayIntervalRoute/<id:\d+>', 'verb'=>'GET'),
					array('DayIntervalRoute/create', 'pattern'=>'DayIntervalRoute/<id:\d+>/editing', 'verb'=>'POST'),
					array('EngineViewTable/read', 'pattern'=>'EngineViewTable/<id:\d+>/', 'verb'=>'GET'),
					array('EngineViewTable/create', 'pattern'=>'EngineViewTable/<id:\d+>/heads', 'verb'=>'POST'),
					array('Engine/create', 'pattern'=>'Engine/<id:\d+>/editing', 'verb'=>'POST'),
					array('Engine/read', 'pattern'=>'Engine/<id:\d+>/extract', 'verb'=>'GET'),
					array('GrafikTime/create', 'pattern'=>'GrafikTime/<id:\d+>/controlpoint', 'verb'=>'POST'),
				),
			),
			'db'=>array(
				'connectionString' => 'mysql:host=localhost;dbname=MakLutsk',
				'emulatePrepare' => true,
				'username' => 'maklutsk',
				'password' => 'ShCheRazMinayu49',
				'schemaCachingDuration' => 60 * 60 * 24,
                                'charset' => 'utf8',
			),

			'db2'=>array(
				'connectionString' => 'mysql:host=193.109.144.51;dbname=mak_krym',
				'emulatePrepare' => true,
				'username' => 'maksim',
				'password' => '[hbpfyntvf23',
			),

			'db3'=>array(
				'connectionString' => 'mysql:host=193.109.144.51;dbname=',
				'emulatePrepare' => true,
				'username' => 'ss_mak',
				'password' => '',
			),


			/*'db'=>array(
				'connectionString' => 'mysql:host=193.109.144.25;dbname=MakLutsk',
				'emulatePrepare' => true,
				'username' => 'makl',
				'password' => 'maklremote33',
			),*/
//для хайді 


		/*	'db'=>array(
				'connectionString' => 'mysql:host=localhost;dbname=stops',
				'emulatePrepare' => true,
				'username' => 'root',
				'password' => '',
			),*/

	/*		'db'=>array(
				'connectionString' => 'mysql:host=193.109.144.25;dbname=MakLutsk',
				'emulatePrepare' => true,
				'username' => 'makl',
				'password' => 'maklremote33',
				'schemaCachingDuration' => 60 * 60 * 24,
			),

			'db2'=>array(
				'connectionString' => 'mysql:host=193.109.144.51;dbname=mak_krym',
				'emulatePrepare' => true,
				'username' => 'maksim',
				'password' => '[hbpfyntvf23',
			),

			'db3'=>array(
				'connectionString' => 'mysql:host=193.109.144.51;dbname=',
				'emulatePrepare' => true,
				'username' => 'ss_mak',
				'password' => '',
			),*/

			'errorHandler'=>array(
				'errorAction'=>'site/error',
			),

			'log'=>array(
				'class'=>'CLogRouter',
				'routes'=>array(
					array(
						'class'=>'CFileLogRoute',
						'levels'=>'error',
						'logFile'=>'error.log',
					),
					array(
						'class'=>'CFileLogRoute',
						'levels'=>'warning',
						'logFile'=>'warning.log',
					),
					array(
						'class'=>'CFileLogRoute',
						'levels'=>'info',
						'logFile'=>'info.log',
					),
					array(
						'class'=>'CFileLogRoute',
						'levels'=>'profile',
						'logFile'=>'profile.log',
					),
					array(
						'class'=>'CFileLogRoute',
						'levels'=>'trace',
						'logFile'=>'trace.log',
					),
					array(
						'class'=>'CFileLogRoute',
						'levels'=>'info',
						'logFile'=>'auth.log',
						'categories'=>'application.controllers.Auth',
					),
				),
			),

			'authManager' => array(
				'class' => 'PhpAuthManager',
				'defaultRoles' => array('guest'),
			),
					
			'session' => array(
				'timeout' => 60 * 60 * 24
			),
			
			'cache' => array(
				'class' => 'CMemCache',
				'servers'=>array(
					array('host'=>'localhost', 'port'=>11211, 'weight'=>60),
				),
			),
		),
	)
);
