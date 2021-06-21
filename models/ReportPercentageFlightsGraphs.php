<?php
class ReportPercentageFlightsGraphs extends CActiveRecord {

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	public function relations()
    {
        return array(
            'route' => array(self::BELONGS_TO, 'Route', 'routes_id'),
            'graph' => array(self::BELONGS_TO, 'Graphs', 'graphs_id'),
            'bort' => array(self::BELONGS_TO, 'Borts', 'borts_id'),
        );
    }
	public function tableName()
	{
		return 'report_percentage_of_flights_to_graphs';
	}
		
}