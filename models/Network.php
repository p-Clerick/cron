<?php
class Network extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(           
            'routesettings' => array(self::HAS_MANY, 'RouteSettings', 'networks_id'),
        );
    }

    public function tableName()
    {
        return 'networks';
    }
}
?>