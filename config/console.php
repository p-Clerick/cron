<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Console Commands',
	// application components
	'components'=>array(
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=MakLutsk',
			'emulatePrepare' => true,
			'username' => 'maklutsk',
			'password' => 'ShCheRazMinayu49',
		),

		'RediskaConnection'=>array(
			'class'=>'application.components.RediskaConnection',
			'options'=>array(
				'servers' => array(
					'server1' => array(
						'host'=>'193.109.144.51',
						'port'=>'6379',
						'timeout'=>'3', // in seconds
						'readTimeout'=>'3', // in seconds
					),
				),
				'serializerAdapter'=>'json',
			),
		),

		'CouchConnection'=>array(
			'class'=>'application.components.CouchConnection',
			'options'=>array(
				'host' => '193.109.144.51',
				'port' => '5984',
				'user' => 'report',
				'password' => 'eENdwUaEKa',
			),
		),

		'export' => array(
			'class'=>'Export',
			'options' => array(
				'map' => 'lutsk_map',
				'schedule' => 'lutsk_schedule',
			),
		),

		'cache' => array(
			'class' => 'CMemCache',
			'servers'=>array(
				array('host'=>'localhost', 'port'=>11211, 'weight'=>60),
			),
		),
	),

	'params'=>array(
		// РљР°РЅР°Р» РґР°РЅРёС… redis, РЅР° СЏРєРёР№ РїСЂРёС…РѕРґСЏС‚СЊ РґР°РЅС– СЂСѓС…Сѓ РїРѕ РјР°РїС–
		'map_channel'=>'lutsk_map', 
		// РљР°РЅР°Р» РґР°РЅРёС… redis, РЅР° СЏРєРёР№ РїСЂРёС…РѕРґСЏС‚СЊ РґР°РЅС– СЂСѓС…Сѓ РјР°СЂС€СЂСѓС‚Сѓ, 
		// СЏРєС– РїРѕС‚СЂР°РїРёР»Рё РІ РіСЂР°С„С–Рє
		'schedule_channel'=>'lutsk_schedule',
		// Р†РЅС‚РµСЂРІР°Р» РјС–Р¶ Р·Р°РїРёСЃР°РјРё РґР°РЅРёС… РІ CouchDB (РґР»СЏ РґР°РЅРёС… Р· РєР°СЂС‚Рё)
		'flushtime' => 60,

		'map_db'=>'map_lutsk',
		'schedule_db'=>'schedule_lutsk',
	),
);

/*



// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return CMap::mergeArray(
	require(dirname(__FILE__).'/couchdb_views.php'),
	array(
		'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
		'name'=>'Console Commands',
		// application components
		'components'=>array(
			'db'=>array(
				'connectionString' => 'mysql:host=193.109.144.51;dbname=MakLutsk',
				'emulatePrepare' => true,
				'username' => 'maklutsk',
				'password' => '%tM*q~$:p83B',
			),
//	'db'=>array(
//				'connectionString' => 'mysql:host=193.109.144.25;dbname=MakLutsk',
//				'emulatePrepare' => true,
//				'username' => 'makl',
//				'password' => 'maklremote33',
//			),

			'RediskaConnection'=>array(
				'class'=>'application.components.RediskaConnection',
				'options'=>array(
					'servers' => array(
						'server1' => array(
							'host'=>'193.109.144.51',
							'port'=>'6379',
							'timeout'=>'3', // in seconds
							'readTimeout'=>'3', // in seconds
						),
					),
					'serializerAdapter'=>'json',
				),
			),

			'CouchConnection'=>array(
				'class'=>'application.components.CouchConnection',
				'options'=>array(
					'host' => '193.109.144.51',
					'port' => '5984',
					'user' => 'report',
					'password' => 'eENdwUaEKa',
				),
			),

			'export' => array(
				'class'=>'Export',
				'options' => array(
					'map' => 'lutsk_map',
					'schedule' => 'lutsk_schedule',
				),
			),

			'cache' => array(
				'class' => 'CMemCache',
				'servers'=>array(
					array('host'=>'localhost', 'port'=>11211, 'weight'=>60),
				),
			),
		),

		'params'=>array(
			// РљР°РЅР°Р» РґР°РЅРёС… redis, РЅР° СЏРєРёР№ РїСЂРёС…РѕРґСЏС‚СЊ РґР°РЅС– СЂСѓС…Сѓ РїРѕ РјР°РїС–
			'map_channel'=>'lutsk_map',
			// РљР°РЅР°Р» РґР°РЅРёС… redis, РЅР° СЏРєРёР№ РїСЂРёС…РѕРґСЏС‚СЊ РґР°РЅС– СЂСѓС…Сѓ РјР°СЂС€СЂСѓС‚Сѓ,
			// СЏРєС– РїРѕС‚СЂР°РїРёР»Рё РІ РіСЂР°С„С–Рє
			'schedule_channel'=>'lutsk_schedule',
			// Р†РЅС‚РµСЂРІР°Р» РјС–Р¶ Р·Р°РїРёСЃР°РјРё РґР°РЅРёС… РІ CouchDB (РґР»СЏ РґР°РЅРёС… Р· РєР°СЂС‚Рё)
			'flushtime' => 60,

			'map_db'=>'map_lutsk',
			'schedule_db'=>'schedule_lutsk',
		),
	)
);*/
