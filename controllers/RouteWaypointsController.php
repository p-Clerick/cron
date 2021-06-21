<?php

class RouteWaypointsController extends Controller
{
	public function actionCreate(){
		$result = array();
		$requestJsonData = Yii::app()->getRequest()->getParam('data');
		$requestArray = CJSON::decode($requestJsonData);
		$insertArray = 0;
		$countPoints = RouteWaypoints::model()->getNumberOfWaypoints($requestArray['routes_id']);
		for ($i = 0, $max = count($requestArray['points']); $i < $max; $i++){
			$countPoints++;
			$insertArray = array(
				'latitude' 		=> $requestArray['points'][$i]['lat'],
				'longitude' 	=> $requestArray['points'][$i]['lng'],
				'stations_id' 	=> $requestArray['stations_id'],
				'routes_id'		=> $requestArray['routes_id'],
				'count'			=> $countPoints
			);
			RouteWaypoints::model()->createRouteWaypoint($insertArray);
		}
		echo CJSON::encode($result);
	}

	public function actionRead(){
		$requestJsonData = Yii::app()->getRequest()->getParam('data');
		$requestArray = CJSON::decode($requestJsonData);

		$queryArray = array(
			'routes_id'				=> (isset($requestArray['routes_id'])) ? $requestArray['routes_id'] : 0,
			'stations_id_from'		=> (isset($requestArray['stations_id_from'])) ? $requestArray['stations_id_from'] : NULL,
			'stations_id_to'		=> (isset($requestArray['stations_id_to'])) ? $requestArray['stations_id_to'] : NULL
		);

		try {
			$resultWaypoints = RouteWaypoints::model()->getRouteWaypoints($queryArray);
			foreach ($resultWaypoints as $object) {
				$result['data'][] = array(
					'latitude' 	=> $object->latitude,
					'longitude'	=> $object->longitude,
					'number'	=> $object->number
				);
			}			
		}
		catch (CException $e){
			$result = array(
				'message'	=> $e->getMessage(),
				'success'	=> false,
				'data'		=> ''

			);			 
		}


		$result['routes_id'] = $requestArray['routes_id'];
		echo CJSON::encode($result);
	}

	public function actionInsertNewPoint(){
		$number = Yii::app()->getRequest()->getParam('number');
		$routes_id = Yii::app()->getRequest()->getParam('routes_id');
		
		$countPoints = RouteWaypoints::model()->getNumberOfWaypoints($routes_id);

		if($number < $countPoints){
			$stations_id =  RouteWaypoints::model()->getStationsIdByNumber($number,$routes_id);

			$insertArray = array(
				'latitude' 		=> Yii::app()->getRequest()->getParam('latitude'),
				'longitude' 	=> Yii::app()->getRequest()->getParam('longitude'),
				'stations_id' 	=> $stations_id,
				'routes_id'		=> $routes_id,
				'count'			=> ($number+1)
			);

			RouteWaypoints::model()->updateWaypointsNumberFromSpecified(($number+1),$routes_id);
			RouteWaypoints::model()->createRouteWaypoint($insertArray);			
		}
		else if ($number === $countPoints){
			//TODO
			echo "Add new point to the end\n";
		}
	}

	public function actionUpdatePoint(){
		$number = Yii::app()->getRequest()->getParam('number');
		$routes_id = Yii::app()->getRequest()->getParam('routes_id');
		
		$countPoints = RouteWaypoints::model()->getNumberOfWaypoints($routes_id);

		if($number < $countPoints){
			$stations_id =  RouteWaypoints::model()->getStationsIdByNumber($number,$routes_id);

			$updateArray = array(
				'latitude' 		=> Yii::app()->getRequest()->getParam('latitude'),
				'longitude' 	=> Yii::app()->getRequest()->getParam('longitude'),
				'stations_id' 	=> $stations_id,
				'routes_id'		=> $routes_id,
				'count'			=> ($number+1)
			);

			RouteWaypoints::model()->updateRouteWaypoint($updateArray);			
		}
		else if ($number === $countPoints){
			//TODO
			echo "Add new point to the end\n";
		}
	}
	public function actionRemovePoint(){
		$routes_id = Yii::app()->getRequest()->getParam('routes_id');
		$requestJsonData = Yii::app()->getRequest()->getParam('indexes');
		$requestArray = CJSON::decode($requestJsonData);
		for ($i=0; $i < count($requestArray); $i++){
			$requestArray[$i]++;
		}
		$requestString = implode(',',$requestArray);
		$minElement = min($requestArray);
		echo $requestString.' - '.$minElement;
		RouteWaypoints::model()->removeRouteWaypointFrom($requestString,$routes_id,$minElement);
		RouteWaypoints::model()->updateWaypointsNumberFromSpecifiedAfter($minElement,$routes_id);

	}

	public function actionDeleteRouteSchema(){
	    $result = array(
	        'success' => true,
            'data' => ''
        );
        $routes_id = Yii::app()->getRequest()->getParam('nodeid');
        $level = Yii::app()->getRequest()->getParam('level');
        if ($level == 2){
            RouteWaypoints::model()->removeRouteWaypoints($routes_id);
            echo CJSON::encode($result);
        }
        else{
            $result['success'] = false;
            echo CJSON::encode($result);
        }

    }
}