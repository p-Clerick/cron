<?php
class Marks extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(
            'model'=>array(self::HAS_ONE, 'Models', 'marks_id'),
        );
    }

    public function tableName()
    {
        return 'marks';
    }
}
?>