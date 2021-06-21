<?php

class Dinner extends CActiveRecord
{
	public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function relations(){
    	return array(
    		'timetable' => array(self::BELONGS_TO, 'TimeTable', 'schedules_id'),
            'scenario' => array(self::BELONGS_TO, 'ControlPointScenario', 'points_control_scenario_id'),
			'points_control_scenario'=>array(self::BELONGS_TO, 'PointsControlScenario', 'points_control_scenario_id'),
            'workshift' => array(self::BELONGS_TO, 'Workshift', 'workshift_id'),  
        );
    }

    public function copyFrom ($sourceDinner, $targetSchedule) {
        $this->number = $sourceDinner->number;
        $this->schedules_id = $targetSchedule->id;
        $this->flight_number = $sourceDinner->flight_number;
        $this->flight_number = $sourceDinner->flight_number;
        $this->points_control_scenario_id = $sourceDinner->points_control_scenario_id;
        $this->start_time = $sourceDinner->start_time;
        $this->end_time = $sourceDinner->end_time;
        $this->duration = $sourceDinner->duration;
        $this->elapsed_worktime = $sourceDinner->elapsed_worktime;
        $targetWorkshift = Workshift::model()->find(array(
            'condition' => 'schedule_id = :sId AND number = :n',
            'params' => array(
                ':sId' => $targetSchedule->id,
                ':n' => $sourceDinner->workshift->number 
            ),
        ));
        if (!empty($targetWorkshift)) {
            $this->workshift_id = $targetWorkshift->id;            
        } else {
            throw CException('Не знайшлася робоча зміна для обіду');
        }
    }

    public function tableName(){
        return 'dinners';
    }
}