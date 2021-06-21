<?php
class ReazonsNoOuting extends CActiveRecord {

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	public function tableName()
	{
		return 'reazons_no_outing';
	}
		
}