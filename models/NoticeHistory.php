<?php
class NoticeHistory extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(
            //'mark'=>array(self::BELONGS_TO, 'Marks', 'marks_id'),
            'notice_response' => array(self::BELONGS_TO, 'NoticeResponse', 'notifications_responses_id'),
            'notice' => array(self::BELONGS_TO, 'Notice', 'notifications_id'),
            'bort'=>array(self::BELONGS_TO, 'Borts', 'borts_id'),
        );
    }

    public function tableName()
    {
        return 'notifications_history';
    }
}
?>