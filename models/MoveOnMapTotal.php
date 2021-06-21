<?php

class MoveOnMapTotal extends CActiveRecord
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
        );
    }

    public function tableName()
    {
        return 'move_on_map_total';
    }
}

?>