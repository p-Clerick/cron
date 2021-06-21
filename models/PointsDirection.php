<?php
	class PointsDirection extends CActiveRecord
	{
	    public static function model($className=__CLASS__)
	    {
	        return parent::model($className);
	    }
	    public function relations()
	    {
	        return array(
	            'route'=>array(self::BELONGS_TO, 'Route', 'routes_id'),
	        );
	    }

	    public function tableName()
	    {
	        return 'waypoints';
	    }
	}
?>