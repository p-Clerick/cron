<?php
class ReportStopsAverageBuses extends CActiveRecord {

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	public function relations()
    {
        return array(
            'station'=>array(self::BELONGS_TO, 'Stations', 'stations_id'),
        );
    }
	public function tableName()
	{
		return 'report_average_deviation_to_stops_buses';
	}
		
}