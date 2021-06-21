<?php
class Notice extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(
            //'mark'=>array(self::BELONGS_TO, 'Marks', 'marks_id'),
            'notice_type' => array(self::BELONGS_TO, 'NoticeType', 'notifications_types_id'),
            'notice_response'=>array(self::HAS_ONE, 'NoticeResponse', 'notifications_id'),
        );
    }

    public function tableName()
    {
        return 'notifications';
    }
}
?>