<?php
class AdvertisementScenario extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
   public function relations()
    {
        return array(
            'points_control_scenario'=>array(self::BELONGS_TO, 'PointsControlScenario', 'points_control_scenario_id'),
            'advertisement' => array(self::BELONGS_TO, 'Advertisement', 'advertisement_id'),
            'advertisement_scenario_graphs' => array(self::HAS_ONE, 'AdvertisementScenarioGraphs', 'advertisement_scenario_id'),
            'route' => array(self::BELONGS_TO, 'Route', 'routes_id'),
        );
    }

    public function tableName()
    {
        return 'advertisement_scenario';
    }
}
?>