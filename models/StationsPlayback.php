<?php
class StationsPlayback extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(
            'stations_scenario'=>array(self::BELONGS_TO, 'StationsScenario', 'stations_scenario_id')
        );
    }

    public function tableName()
    {
        return 'stations_playback';
    }
}
?>