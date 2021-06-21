<?php
class NoticeType extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(           
            'notice_type'=>array(self::HAS_MANY, 'NoticeResponse', 'notifications_types_id'),
            'notice_type'=>array(self::HAS_MANY, 'Notice', 'notifications_types_id'),
        );
    }

    public function tableName()
    {
        return 'notifications_types';
    }
}
?>