<?php
class GraphSettings extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(
            'routesettings' => array(self::HAS_MANY, 'RouteSetings', 'graph_settings_id'),
        );
    }

    public function tableName()
    {
        return 'graph_settings';
    }
}
?>