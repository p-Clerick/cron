<?php
	class PointsControl extends CActiveRecord
	{
	    public static function model($className=__CLASS__)
	    {
	        return parent::model($className);
	    }
	    public function relations()
	    {
	        return array(
	            'points_control_scenario'=>array(self::HAS_ONE, 'PointsControlScenario', 'points_control_id'),
	        );
	    }

	    public function tableName()
	    {
	        return 'points_control';
	    }
	}
?>