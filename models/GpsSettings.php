<?php
class GpsSettings extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(
            'routesettings' => array(self::HAS_MANY, 'RouteSettings', 'gps_settings_id'),
        );
    }

    public function tableName()
    {
        return 'gps_settings';
    }
}
?>