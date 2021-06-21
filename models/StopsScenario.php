<?php
class StopsScenario extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(
            //'points_control_scenario'=>array(self::BELONGS_TO, 'PointsControlScenario', 'points_control_scenario_id'),
            'stops'=>array(self::BELONGS_TO, 'Stops', 'stops_id'),
            'route' => array(self::BELONGS_TO, 'Route', 'routes_id'),
        );
    }

    public function tableName()
    {
        return 'stops_scenario';
    }
}
?>