<?php
class Carriers extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(
            'park'=>array(self::HAS_MANY, 'Parks', 'carriers_id'),
            'users'=>array(self::BELONGS_TO, 'User', 'user_id'),
            'advertisement'=>array(self::HAS_MANY, 'Advertisement', 'carriers_id'),
            'route'=>array(self::HAS_MANY, 'Route', 'carriers_id'),
            'graph'=>array(self::HAS_MANY, 'Graphs', 'carriers_id'),
            'report_percentage_routes_graphs'=>array(self::HAS_MANY, 'ReportPercentageRoutesGraphs', 'carriers_id'),

        );
    }

    public function tableName()
    {
        return 'carriers';
    }
}
?>