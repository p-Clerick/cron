<?php
class NoticeAllHistory extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(
            'bort'=>array(self::BELONGS_TO, 'Borts', 'borts_id'),
        );
    }

    public function tableName()
    {
        return 'notice_history';
    }
    public function getBortNoticeExist($borts_id){
        $bortNotice = $this->find(array(
            'condition'=>'borts_id=:brtid',
			'params'=>array(':brtid' => $borts_id)
        ));
        return $bortNotice;
    }
}
?>