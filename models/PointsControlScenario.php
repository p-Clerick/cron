<?php
class PointsControlScenario extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(
           // 'moveonschedule'=>array(self::HAS_MANY, 'MoveOnSchedule', 'points_control_scenario_id'),
            'points_control'=>array(self::BELONGS_TO, 'PointsControl', 'points_control_id'),
            'scheduletime' => array(self::HAS_MANY, 'ScheduleTimes', 'points_control_scenario_id'),
            'route' => array(self::BELONGS_TO, 'Route', 'routes_id'),
            'advertisement_scenario' => array(self::HAS_ONE, 'Advertisement', 'points_control_scenario_id'),
			'stops_scenario' => array(self::HAS_ONE, 'StopsScenario', 'points_control_scenario_id'),
            'moveonschedule' => array(self::HAS_MANY, 'MoveOnSchedule', 'points_control_scenario_id'),
        );
    }

    public function tableName()
    {
        return 'points_control_scenario';
    }
}
?>