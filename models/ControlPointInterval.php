<?php

class ControlPointInterval extends CActiveRecord
{
	public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function relations(){
    	return array(
    		'point_scenario' => array(self::BELONGS_TO, 'ControlPointScenario', 'point_scenario_id'),
    		'dayinterval' => array(self::BELONGS_TO, 'DayInterval', 'dayintervalid'),
	    );
    }

    public function tableName(){
        return 'points_interval';
    }
}