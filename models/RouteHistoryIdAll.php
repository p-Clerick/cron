<?php
class RouteHistoryIdAll extends CActiveRecord {

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	public function tableName()
	{
		return 'route_calc_init_data';
	}
		
}