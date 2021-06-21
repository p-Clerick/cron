<?php

return array(
	'guest' => array(
		'type' => CAuthItem::TYPE_ROLE,
		'description' => 'Guest',
		'bizRule' => null,
		'data' => null
	),
	'disp' => array(
		'type' => CAuthItem::TYPE_ROLE,
		'description' => 'Dispatcher',
		'children' => array(
			'user',
		),
		'bizRule' => null,
		'data' => null
	),
	'cw' => array(
		'type' => CAuthItem::TYPE_ROLE,
		'description' => 'Content Writer',
		'children' => array(
			'user',
		),
		'bizRule' => null,
		'data' => null
	),
	'fm' => array(
		'type' => CAuthItem::TYPE_ROLE,
		'description' => 'Ferryman',
		'children' => array(
		  'admin',
		),
		'bizRule' => null,
		'data' => null,
	),
	'gov' => array(
		'type' => CAuthItem::TYPE_ROLE,
		'description' => 'Goverment',
		'children' => array(
		  'user',

			'createStations',
			'updateStations',
			'deleteStations',

		),
		'bizRule' => null,
		'data' => null,
	),
	'state_agencies' => array(
		'type' => CAuthItem::TYPE_ROLE,
		'description' => 'State_agencies',
		'children' => array(
		  'user',
		),
		'bizRule' => null,
		'data' => null,
	),
	'admin' => array(
		'type' => CAuthItem::TYPE_ROLE,
		'description' => 'Administrator',
		'children' => array(
			'disp',
			'cw',

			'createUser',
			'deleteUser',

			/*'createBorts',
			'updateBorts',
			'deleteBorts',*/

			'createPointsControlScenario',
			'updatePointsControlScenario',
			'deletePointsControlScenario',
			'loadPointsControlScenario',

			'loadStopsScenario',
			'createStopsScenario',
			'updateStopsScenario',
			'deleteStopsScenario',

			'deleteCurrentSchedule',

			'createAdvertisement',
			'updateAdvertisement',
			'deleteAdvertisement',

			'getVidhylReport',
			'getNoconnectionReport',
			'getSpeedmodeReport',
			'getMileageReport',
			'getSpeedReport',
			'getWorktimeReport',
			'exportReport',

			'createStops',
			'updateStops',
			'deleteStops',
		),
		'bizRule' => null,
		'data' => null
	),
	'superadmin' => array(
		'type' => CAuthItem::TYPE_ROLE,
		'description' => 'Administrator',
		'children' => array(
			'disp',
			'cw',

			'createUser',
			'deleteUser',

			'createSchedule',
			'deleteSchedule',
			'changeSchedule',
			'calculateGrafik',
			'getInitialParamsForCalculation',
			'deleteCurrentSchedule',

			'createRoute',
			'deleteRoute',

			'createDayInterval',
			'deleteDayInterval',
			'updateDayInterval',

			'createBorts',
			'updateBorts',
			'deleteBorts',

			'createPointsControlScenario',
			'updatePointsControlScenario',
			'deletePointsControlScenario',
			'loadPointsControlScenario',

			'createPointsControl',
			'updatePointsControl',
			'deletePointsControl',

			'createPointsOfEvents',
			'updatePointsOfEvents',
			'deletePointsOfEvents',

			'loadStopsScenario',
			'createStopsScenario',
			'updateStopsScenario',
			'deleteStopsScenario',

			'loadStationsScenario',
			'createStationsScenario',
			'updateStationsScenario',
			'deleteStationsScenario',

			'createAdvertisement',
			'updateAdvertisement',
			'deleteAdvertisement',

			'createDirections',
			'updateDirections',
			'deleteDirections',

			'createRouteConfig',
			'updateRouteConfig',
			'deleteRouteConfig',
			'readCarrier',
			'readGraph',
			'setCarrierForGraph',

			'getVidhylReport',
			'getNoconnectionReport',
			'getSpeedmodeReport',
			'getMileageReport',
			'getSpeedReport',
			'getWorktimeReport',
			'exportReport',

			'createSettings',
			'createStops',
			'updateStops',
			'deleteStops',

			'createNotifications',
			'updateNotifications',
			'deleteNotifications',

			'createNotificationsResponses',
			'updateNotificationsResponses',
			'deleteNotificationsResponses',

			'createStationsPlayback',
			'updateStationsPlayback',

			'createStations',
			'updateStations',
			'deleteStations',

		),
		'bizRule' => null,
		'data' => null
	),
	'superuser' => array(
		'type' => CAuthItem::TYPE_ROLE,
		'description' => 'Superuser',
		'children' => array(

			'superadmin',

			'createStops',
			'updateStops',
			'deleteStops',

			'createStations',
			'updateStations',
			'deleteStations',

			'createNotifications',
			'updateNotifications',
			'deleteNotifications',

			'createNotificationsResponses',
			'updateNotificationsResponses',
			'deleteNotificationsResponses',

            'GetCurrentReport',
		),
		'bizRule' => null,
		'data' => null
	),

	'createUser' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'create user',
		'bizRule' => null,
		'data' => null,
	),
	'deleteUser' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'delete user',
		'bizRule' => null,
		'data' => null,
	),
	'createRoute' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'create route',
		'bizRule' => null,
		'data' => null,
	),
	'deleteRoute' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'delete route',
		'bizRule' => null,
		'data' => null,
	),
	'createSchedule' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'create schedule',
		'bizRule' => null,
		'data' => null,
	),
	'deleteSchedule' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'delete schedule',
		'bizRule' => null,
		'data' => null,
	),
	'createDayInterval' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'create day interval',
		'bizRule' => null,
		'data' => null,
	),
	'deleteDayInterval' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'delete day interval',
		'bizRule' => null,
		'data' => null,
	),
	'updateDayInterval' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'delete day interval',
		'bizRule' => null,
		'data' => null,
	),
	'calculateGrafik' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'calculate grafik times',
		'bizRule' => null,
		'data' => null,
	),
	'createPointsControl' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'create points control',
		'bizRule' => null,
		'data' => null,
	),
	'updatePointsControl' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'update points control',
		'bizRule' => null,
		'data' => null,
	),
	'deletePointsControl' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'delete points control',
		'bizRule' => null,
		'data' => null,
	),
	'createPointsOfEvents' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'create points OfEvents',
		'bizRule' => null,
		'data' => null,
	),
	'updatePointsOfEvents' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'update points OfEvents',
		'bizRule' => null,
		'data' => null,
	),
	'deletePointsOfEvents' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'delete points OfEvents',
		'bizRule' => null,
		'data' => null,
	),
	'createDirections' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'create directions for route',
		'bizRule' => null,
		'data' => null,
	),
	'updateDirections' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'update directions for route',
		'bizRule' => null,
		'data' => null,
	),
	'deleteDirections' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'delete directions for route',
		'bizRule' => null,
		'data' => null,
	),
	'createRouteConfig' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'create routeConfig',
		'bizRule' => null,
		'data' => null,
	),
	'updateRouteConfig' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'update RouteConfig',
		'bizRule' => null,
		'data' => null,
	),
	'deleteRouteConfig' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'delete RouteConfig',
		'bizRule' => null,
		'data' => null,
	),
	'createStops' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'create stops',
		'bizRule' => null,
		'data' => null,
	),
	'updateStops' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'update stops',
		'bizRule' => null,
		'data' => null,
	),
	'deleteStops' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'delete stops',
		'bizRule' => null,
		'data' => null,
	),
	'createStationsPlayback' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'create stops',
		'bizRule' => null,
		'data' => null,
	),
	'updateStationsPlayback' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'update stops',
		'bizRule' => null,
		'data' => null,
	),
	'createStopsScenario' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'create stopsscenario',
		'bizRule' => null,
		'data' => null,
	),
	'updateStopsScenario' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'update stopsscenario',
		'bizRule' => null,
		'data' => null,
	),
	'deleteStopsScenario' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'delete stopsscenario',
		'bizRule' => null,
		'data' => null,
	),
	'loadStopsScenario' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'load stopsscenario',
		'bizRule' => null,
		'data' => null,
	),
	'createStations' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'create stations',
		'bizRule' => null,
		'data' => null,
	),
	'updateStations' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'update stations',
		'bizRule' => null,
		'data' => null,
	),
	'deleteStations' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'delete stations',
		'bizRule' => null,
		'data' => null,
	),
	'createStationsScenario' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'create stationsscenario',
		'bizRule' => null,
		'data' => null,
	),
	'updateStationsScenario' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'update stationsscenario',
		'bizRule' => null,
		'data' => null,
	),
	'deleteStationsScenario' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'delete stationsscenario',
		'bizRule' => null,
		'data' => null,
	),
	'createNotifications' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'create notice',
		'bizRule' => null,
		'data' => null,
	),
	'updateNotifications' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'update notice',
		'bizRule' => null,
		'data' => null,
	),
	'deleteNotifications' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'delete notice',
		'bizRule' => null,
		'data' => null,
	),
	'createNotificationsResponses' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'create notice',
		'bizRule' => null,
		'data' => null,
	),
	'updateNotificationsResponses' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'update notice',
		'bizRule' => null,
		'data' => null,
	),
	'deleteNotificationsResponses' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'delete notice',
		'bizRule' => null,
		'data' => null,
	),
	'createBorts' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'create borts',
		'bizRule' => null,
		'data' => null,
	),
	'updateBorts' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'update borts',
		'bizRule' => null,
		'data' => null,
	),
	'deleteBorts' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'delete borts',
		'bizRule' => null,
		'data' => null,
	),
	'createAdvertisement' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'create borts',
		'bizRule' => null,
		'data' => null,
	),
	'updateAdvertisement' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'update borts',
		'bizRule' => null,
		'data' => null,
	),
	'deleteAdvertisement' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'delete borts',
		'bizRule' => null,
		'data' => null,
	),
	'createPointsControlScenario' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'create points control scenario',
		'bizRule' => null,
		'data' => null,
	),
	'updatePointsControlScenario' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'update points control scenario',
		'bizRule' => null,
		'data' => null,
	),
	'deletePointsControlScenario' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'delete points control scenario',
		'bizRule' => null,
		'data' => null,
	),
	'loadPointsControlScenario' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'load points control scenario',
		'bizRule' => null,
		'data' => null,
	),
	'loadStationsScenario' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'load stations scenario',
		'bizRule' => null,
		'data' => null,
	),
	'deleteCurrentSchedule' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'delete current schedule',
		'bizRule' => null,
		'data' => null,
	),
	'changeSchedule' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'update grapfik times',
		'bizRule' => null,
		'data' => null,
	),
	'readCarrier' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'read list of carriers',
		'bizRule' => null,
		'data' => null,
	),
	'readGraph' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'read information about graph',
		'bizRule' => null,
		'data' => null,
	),
	'setCarrierForGraph' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'set carrier for graph',
		'bizRule' => null,
		'data' => null,
	),
	'getInitialParamsForCalculation' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'Завантаження початкових даних для форми генерації графіка',
		'bizRule' => null,
		'data' => null,
	),
	'getVidhylReport' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'get report vidhyl',
		'bizRule' => null,
		'data' => null,
	),
	'getNoconnectionReport' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'get report vidhyl',
		'bizRule' => null,
		'data' => null,
	),
	'getSpeedmodeReport' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'get report speedmode',
		'bizRule' => null,
		'data' => null,
	),
	'getMileageReport' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'get report mileage',
		'bizRule' => null,
		'data' => null,
	),
	'getSpeedReport' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'get report speed',
		'bizRule' => null,
		'data' => null,
	),
	'getWorktimeReport' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'get report worktime',
		'bizRule' => null,
		'data' => null,
	),
	'exportReport' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'can export xls report',
		'bizRule' => null,
		'data' => null,
	),
	'createSettings' => array(
		'type' => CAuthItem::TYPE_TASK,
		'description' => 'CRU operation with route settings',
		'bizRule' => null,
		'data' => null,
	),
    'GetCurrentReport' => array(
        'type' => CAuthItem::TYPE_TASK,
        'description' => 'create order report',
        'bizRule' => null,
        'data' => null,
    ),
);
