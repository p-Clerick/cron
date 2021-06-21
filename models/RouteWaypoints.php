<?php

class RouteWaypoints extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(
            'route'=>array(self::BELONGS_TO, 'Route', 'routes_id'),
        );
    }
    public function tableName()
    {
        return 'route_waypoints';
    }

	/**
	* Створення точки шляху маршруту
	* @param array $pointArray :
	*		- latitude - широта
	*		- longitude - довгота
	*		- stations_id - ід зупинки
	*		- count - кількість точок шляху маршруту до вставки нової
	*		- routes_id - ід маршруту
	*/
    public function createRouteWaypoint($pointArray){
    	$routeWaypoints = new RouteWaypoints;
    	$routeWaypoints->latitude 		= $pointArray['latitude'] ;
    	$routeWaypoints->longitude		= $pointArray['longitude'];
    	$routeWaypoints->stations_id 	= $pointArray['stations_id'];
    	$routeWaypoints->number 		= $pointArray['count'];
    	$routeWaypoints->routes_id 		= $pointArray['routes_id'];
    	$routeWaypoints->save();
    }

    /**
	*	Повертає масив точок шляху маршруту. Результат залежить
	*	від наявності точок зупинок від і до.
	*	@param array $queryArray :
	*		- routes_id - ід маршруту 
	*		- stations_id_from - ід зупинки від якої необхідні дані
	*		- stations_id_to - ід зупинки до якої необхідні дані
	*	@return RouteWaypoints - Повертає точки шляху маршруту
    */
    public function getRouteWaypoints($queryArray){
    	$routeWaypoints = 0;
    	$numberWaypointsFrom = 0;
    	$numberWaypointsTo = 0;

    	if (!isset($queryArray['stations_id_from']) and !isset($queryArray['stations_id_to'])){
	    	$routeWaypoints = $this->getWaypointsForRoute($queryArray['routes_id']);
    	}

    	if (isset($queryArray['stations_id_from'])){
            try{
	    	  $numberWaypointsFrom = $this->getNumberForStation($queryArray['routes_id'], $queryArray['stations_id_from'], 'ASC');
            }
            catch(Cexception $e){
                $numberWaypointsFrom = 1;
            }
    	}
    	else{
    		$numberWaypointsFrom = 1;
    	}

		if (isset($queryArray['stations_id_to'])){
            try{
                $numberWaypointsTo = $this->getNumberForStation($queryArray['routes_id'],$queryArray['stations_id_to'], 'ASC');
            }
            catch (Cexception $e){
                $numberWaypointsTo = $this->getNumberOfWaypoints($queryArray['routes_id']);
            }
		}
		else{
            $numberWaypointsTo = $this->getNumberOfWaypoints($queryArray['routes_id']);
		}

		if (isset($queryArray['stations_id_from']) || isset($queryArray['stations_id_to'])){
			$routeWaypoints = $this->getWaypointsForRouteBetweenNumbers($queryArray['routes_id'], $numberWaypointsFrom, $numberWaypointsTo);
		}

    	return $routeWaypoints;
    }

    /**
    *	Повертає кількість точок шляху для конкретного маршруту
    *	@param integer $routesId ід маршруту
    *
    *	@return integer
    */
    public function getNumberOfWaypoints($routesId){
    	$countPoints = 0;
    	$countPoints = RouteWaypoints::model()->count(array(
    		'condition'=>'routes_id=:rtid',
			'params'=>array(':rtid' => $routesId),
    	));
        if (!$countPoints){
            $countPoints = 0;
            //throw new CException("Detection of the number of points for the route error");
        }
        else{
    	   return $countPoints;
        }   	
    }

    /**
    *	Повертає номер першої або останньої точки для конкретної зупинки
    *	@param integer $routesId ід маршруту
    *	@param integer $stations_id ід зупинки
    *	@param integer $sort_type спосіб сортування
    *
    *	@return integer 
    */
    public function getNumberForStation($routesId, $stations_id, $sort_type){
		$queryWaypoints = RouteWaypoints::model()->find(array(
    		'select' => 'id, routes_id, stations_id, number',
    		'condition' => 'routes_id=:rtid and stations_id=:stid',
    		'params' => array('rtid' => $routesId, 'stid' => $stations_id),
    		'order' => 'number '.$sort_type
		));
        if (!$queryWaypoints) {
            throw new CException("Error stop searching for a route");
        }
        else{
            return $queryWaypoints->number;
        }		
    }

    /**
    *	Повертає точки шляху маршруту для конкретного маршруту
    *	@param integer $routesId
    *
    *	@return RouteWaypoints
    */
    public function getWaypointsForRoute($routesId){
    	$routeWaypoints = RouteWaypoints::model()->findAll(array(
    		'condition' => 'routes_id=:rtid',
    		'params' => array('rtid' => $routesId),
    		'order' => 'number'
    	));
        if (!$routeWaypoints) {
            throw new CException("Error finding points to route traffic");
        }
        else{
            return $routeWaypoints;
        }	
    }

    /**
    *	Повертає точки шляху маршруту для конкретного маршруту між вказаними номерами точок
    *	@param integer $routesId ід маршруту
    *	@param integer $numberFrom номер точки маршруту від якої необхідні дані
    *	@param integer $numberTo номер точки маршруту по яку необхідні дані
    *
    *	@return RouteWaypoints
    */
    public function getWaypointsForRouteBetweenNumbers($routesId, $numberFrom, $numberTo){
		$routeWaypoints = RouteWaypoints::model()->findAll(array(
			'condition' => 'routes_id=:rtid and number >= :from and number <= :to',
			'params' => array('rtid' => $routesId, 'from' => $numberFrom, 'to' => $numberTo),
			'order' => 'number'
		));
        if (!$routeWaypoints){
            throw new Cexception("Error finding points between two stops on the route");
        }
        else{
		  return $routeWaypoints;
        }
    }

    /**
    *	Повертає точки шляху маршруту для конкретного маршруту більше вказаного номеру точки
    *	@param integer $routesId ід маршруту
    *	@param integer $numberFrom номер точки маршруту від якої необхідні дані
    *
    *	@return RouteWaypoints
    */
    public function getWaypointsForRouteFromNumbers($routesId, $numberFrom){
		$routeWaypoints = RouteWaypoints::model()->findAll(array(
			'condition' => 'routes_id=:rtid and number >= :from',
			'params' => array('rtid' => $routesId, 'from' => $numberFrom),
			'order' => 'number'
		));
		return $routeWaypoints;
    }
    
    public function getStationsIdByNumber($number,$routes_id){
    	$routeWaypoint = RouteWaypoints::model()->find(array(
    		'condition' => 'routes_id=:rtid and number=:num',
    		'params' => array(':rtid' => $routes_id, ':num' => $number)
    	));
    	return $routeWaypoint->stations_id;
    }

    public function updateWaypointsNumberFromSpecified($number,$routes_id){
    	$routeWaypoints = $this->getWaypointsForRouteFromNumbers($routes_id,$number);
    	foreach ($routeWaypoints as $routeWaypoint){
    		$number++;
    		$routeWaypoint->number = $number;
    		$routeWaypoint->save();
    	}
    }

    public function updateWaypointsNumberFromSpecifiedAfter($number,$routes_id){
        $routeWaypoints = $this->getWaypointsForRouteFromNumbers($routes_id,$number);
        foreach ($routeWaypoints as $routeWaypoint){
            $routeWaypoint->number = $number;
            $routeWaypoint->save();
            $number++;
        }
    }

    public function updateRouteWaypoint($pointArray){
    	$routeWaypoint = RouteWaypoints::model()->find(array(
    		'condition' => 'routes_id=:rtid and number=:num',
    		'params' => array(':rtid' => $pointArray['routes_id'], ':num' => $pointArray['count'])
    	));
    	$routeWaypoint->latitude 		= $pointArray['latitude'] ;
    	$routeWaypoint->longitude		= $pointArray['longitude'];
    	$routeWaypoint->save();   	
    }

    public function removeRouteWaypointFrom($pointString,$routes_id,$minElement){
        $routeWaypoints = RouteWaypoints::model()->deleteAll(array(
            'condition' => 'routes_id=:rtid AND number in ('.$pointString.')',
            'params' => array('rtid' => $routes_id),
        ));
//        $this->updateWaypointsNumberFromSpecified(($minElement-1),$routes_id);
    }

    public function removeRouteWaypoints($routes_id){
        $routeWaypoints = RouteWaypoints::model()->deleteAll(array(
            'condition' => 'routes_id=:rtid',
            'params' => array('rtid' => $routes_id),
        ));
    }
}

?>