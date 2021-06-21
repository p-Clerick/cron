<?php

class MoveOnSchedule extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(
            'bort'=>array(self::BELONGS_TO, 'Borts', 'borts_id'),
            'schedule'=>array(self::BELONGS_TO, 'Schedules', 'schedules_id'),
            'points_control_scenario'=>array(self::BELONGS_TO, 'PointsControlScenario', 'points_control_scenario_id'),
            'stations'=>array(self::BELONGS_TO, 'Stations', 'stations_id'),
            'route'=>array(self::BELONGS_TO, 'Route', 'routes_id'),
            'graph'=>array(self::BELONGS_TO, 'Graphs', 'graphs_id'),
        );
    }

    public function tableName()
    {
        return 'move_on_schedule';
    }
}

?>