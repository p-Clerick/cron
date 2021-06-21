<?php

class ControlPointScenario extends CActiveRecord
{
	public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function relations(){
    	return array(
    		'intervals' => array(self::HAS_MANY, 'ControlPointInterval', 'point_scenario_id'),
            'point' => array(self::BELONGS_TO, 'ControlPoint', 'points_control_id'),
            'route' => array(self::BELONGS_TO, 'Route', 'routes_id'),
            'times' => array(self::HAS_MANY, 'ScheduleTime', 'points_control_scenario'),
	    );
    }

    public function tableName(){
        return 'points_control_scenario';
    }

    public function getControlPointInterval($dayInt){
        return ControlPointInterval::model()->find(
            'point_scenario_id = :pId AND dayintervalid = :dId',
            array(
                'pId' => $this->id,
                'dId' => $dayInt->id,
            )
        );
    }
}