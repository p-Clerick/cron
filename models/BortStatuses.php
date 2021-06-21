<?php

class BortStatuses extends CActiveRecord
{
	public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
	/*public function relations()
    {
        return array(
            'order'=>array(self::HAS_MANY, 'Orders', 'bort_statuses_id'),
        );
    }*/
	public function tableName(){
        return 'bort_statuses';
    }
}