<?php

class ControlPoint extends CActiveRecord
{
	public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function relations(){
    	return array(
    		'scenarios' => array(self::HAS_MANY, 'ControlPointScenario', 'points_control_id'),
	    );
    }

    public function tableName(){
        return 'points_control';
    }
}