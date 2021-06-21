<?php
class Advertisement extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
   public function relations()
    {
        return array(
            'advertisement_scenario' => array(self::HAS_ONE, 'AdvertisementScenario', 'advertisement_id'),
            'carrier' => array(self::BELONGS_TO, 'Carriers', 'carriers_id'),
        );
    }

    public function tableName()
    {
        return 'advertisement';
    }
}
?>