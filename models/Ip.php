<?php

class Ip extends CActiveRecord
{
	public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function relations(){
    	return array(
    		'bortip' => array(self::BELONGS_TO, 'Borts', 'borts_id'),             
        );
    }   

    public function tableName(){
        return 'ip';
    }
}