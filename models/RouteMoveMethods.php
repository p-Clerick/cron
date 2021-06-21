<?php

class RouteMoveMethods extends CActiveRecord
{
	public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function relations(){
    	return array(
            'route' => array(self::HAS_ONE, 'Route', 'move_methods_id'),
	    );
    }

	public function tableName(){
        return 'route_move_methods';
    }
}