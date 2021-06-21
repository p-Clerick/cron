<?php
class NoticeResponse extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(
            //'mark'=>array(self::BELONGS_TO, 'Marks', 'marks_id'),
            //'vehicletype' => array(self::BELONGS_TO, 'VehicleType', 'transport_types_id'),
            'notice'=>array(self::BELONGS_TO, 'Notice', 'notifications_id'),
            //'notice_type'=>array(self::BELONGS_TO, 'NoticeType', 'notifications_types_id'),
            'notice_response'=>array(self::HAS_MANY, 'NoticeHistory', 'notifications_responses_id'),
        );
    }

    public function tableName()
    {
        return 'notifications_responses';
    }
}
?>