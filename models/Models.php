<?php
class Models extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(
            'mark'=>array(self::BELONGS_TO, 'Marks', 'marks_id'),
            'vehicletype' => array(self::BELONGS_TO, 'VehicleType', 'transport_types_id'),
            'bort'=>array(self::HAS_ONE, 'Borts', 'models_id'),
        );
    }

    public function tableName()
    {
        return 'models';
    }
}
?>