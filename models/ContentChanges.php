<?php
class ContentChanges extends CActiveRecord
{
    const PCS = 1;
    const SS  = 2;
    const ADS = 3;
    const STS = 4;    
    
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(
            'route' => array(self::BELONGS_TO, 'Route', 'routes_id'),
//            'advertisement_scenario' => array(self::HAS_ONE, 'Advertisement', 'points_control_scenario_id'),
        );
    }

    public function tableName()
    {
        return 'content_changes';
    }
}
?>