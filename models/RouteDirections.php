<?php
	class RouteDirections extends CActiveRecord
	{
	    public static function model($className=__CLASS__)
	    {
	        return parent::model($className);
	    }
	    public function relations()
	    {
	        return array(
	            'route'=>array(self::BELONGS_TO, 'Route', 'routes_id'),
	            'station_from'=>array(self::BELONGS_TO, 'Stations', 'stations_id_from'),
	            'station_to'=>array(self::BELONGS_TO, 'Stations', 'stations_id_to'),	            
	            'stations_scenario' => array(self::HAS_MANY, 'StationScenario', 'route_directions_id'),


	        );
	    }

	    public function tableName()
	    {
	        return 'route_directions';
	    }
	}
?>