<?php

class VehicleType extends CActiveRecord
{
	public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function relations(){
    	return array(
    		'routes' => array(self::HAS_MANY, 'Route', 'transport_types_id'),
            'translation' => array(self::BELONGS_TO, 'Translation', 'translations_id')
	    );
    }

    public function getChildren(){
    	$res = array();       
        $carrier_id = Yii::app()->user->carrier->id;
        if ($carrier_id == 0){            
            if  (Yii::app()->user->isGuest){
                $routes = Route::model()->findAll(array(
                    'condition' => 'transport_types_id = :ttId and status=:st',
                    'params' => array(
                        ':ttId' => $this->id,
                        ':st'=>'yes'
                    ),
                ));                
            }
            else{
                $routes = $this->routes;
            }
        } else {

            $routes = Route::model()->with(array(
                'graphs'=>array(
                    'select'=>false,
                    'condition'=>'graphs.carriers_id=:carrier',
                    'params'=>array(
                        ':carrier'=>$carrier_id
                    ),
                )
            ))->findAll(array(
                'condition' => 'transport_types_id = :ttId',
                'params' => array(
                    ':ttId' => $this->id,
                ),
            ));
        }
    	foreach($routes as $item){
    		$resn[$item->id] = $item->name;
    	}
        asort($resn, SORT_NUMERIC);
        while (list($key, $value) = each($resn)) {
            $res[] = array(
                  'id'      => $key,
                  'name'    => $value,
            );
            
        }
    	return $res;
    }

	public function tableName(){
        return 'transport_types';
    }
} 