<?php
class ReportPercentageGraphs extends CActiveRecord {

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	public function relations()
    {
        return array(
            'route' => array(self::BELONGS_TO, 'Route', 'routes_id'),
            'graph' => array(self::BELONGS_TO, 'Graphs', 'graphs_id')
        );
    }
	public function tableName()
	{
		return 'report_percentage_performance_schedules_to_graphs';
	}
		
}