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
            'graph'=>array(self::BELONGS_TO, 'Graphs', 'graphs_id'),
            'advertisement_scenario' => array(self::BELONGS_TO, 'AdvertisementScenario', 'advertisement_scenario_id'),
        );
    }

    public function tableName()
    {
        return 'advertisement_scenario';
    }
}
?>