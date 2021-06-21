<?php
class Stops extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(
            'stopsscenario'=>array(self::HAS_ONE, 'StopsScenario', 'stops_id'),
        );
    }

    public function tableName()
    {
        return 'stops';
    }
}
?>