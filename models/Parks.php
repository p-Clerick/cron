<?php
class Parks extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(
            'carrier'=>array(self::BELONGS_TO, 'Carriers', 'carriers_id'),
            'bort'=>array(self::HAS_MANY, 'Borts', 'parks_id'),
        );
    }

    public function tableName()
    {
        return 'parks';
    }
}
?>