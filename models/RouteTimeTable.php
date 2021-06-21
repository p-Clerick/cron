<?php
class RouteTimeTable extends CActiveRecord {

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	public function relations()
    {
        return array(
            'stationScenario' => array(self::BELONGS_TO, 'StationsScenario', 'stations_scenario_id'),
            'stationName' => array(self::BELONGS_TO, 'Stations', 'stations_id'),
            'routeHistoryAll' => array(self::BELONGS_TO, 'RouteHistoryIdAll', 'routes_history_id')
        );
    }
	public function tableName()
	{
		return 'route_calc_schedules';
	}
		
}