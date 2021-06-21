<?php

class RouteSettings extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	public function relations(){
		return array(
            'route' => array(self::BELONGS_TO, 'Route', 'routes_id'),
			'gpssettings'=>array(self::BELONGS_TO, 'GpsSettings', 'gps_settings_id'),
			'graphsettings'=>array(self::BELONGS_TO, 'GraphSettings', 'graph_settings_id'),						
			'networks'=>array(self::BELONGS_TO, 'Network', 'networks_id'),            
		);
	}
	public function tableName(){
		return 'route_settings';
	}
}