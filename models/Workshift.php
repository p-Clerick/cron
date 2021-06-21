<?php

class Workshift extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function relations(){
		return array(
			'schedule' => array(self::BELONGS_TO, 'Schedules', 'schedule_id'),
			'dinners' => array(self::HAS_MANY, 'Dinner', 'workshift_id'),
		);
	}

	public function tableName(){
		return 'workshift';
	}
}