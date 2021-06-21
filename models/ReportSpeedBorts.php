<?php
class ReportSpeedBorts extends CActiveRecord {

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	public function relations()
    {
        return array(
        	'carrier' => array(self::BELONGS_TO, 'Carriers', 'carriers_id'),
            'route' => array(self::BELONGS_TO, 'Route', 'routes_id'),
            'graph' => array(self::BELONGS_TO, 'Graphs', 'graphs_id'),
            'bort' => array(self::BELONGS_TO, 'Borts', 'borts_id')
        );
    }
	public function tableName()
	{
		return 'report_speed_borts';
	}
		
}