<?php
class LocationsFlight extends CActiveRecord {

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	public function relations()
    {
        return array(
            'route' => array(self::BELONGS_TO, 'Route', 'routes_id'),
            'stations' => array(self::BELONGS_TO, 'Stations', 'stations_id'),
            'graph' => array(self::BELONGS_TO, 'Graphs', 'graphs_id'),
            'bort' => array(self::BELONGS_TO, 'Borts', 'borts_id'),
        );
    }
	public function tableName()
	{
		return 'locations_in_flights';
	}
		
}