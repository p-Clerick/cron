<?php
class ReportStopsAverageRoutes extends CActiveRecord {

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	public function relations()
    {
        return array(
            'route' => array(self::BELONGS_TO, 'Route', 'routes_id'),
            'station'=>array(self::BELONGS_TO, 'Stations', 'stations_id'),
        );
    }
	public function tableName()
	{
		return 'report_average_deviation_to_stops_routes';
	}
		
}