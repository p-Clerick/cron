<?php

class Tree
{
	const LEVEL_VEHICLE = 1;
	const LEVEL_ROUTE = 2;
	const LEVEL_SCHEDULE = 3;

	/**
	 * Повертає список дочірніх елементів для маршрута або типу тз
	 * @param integer $level Рівень дерева
	 * @param integer $id Ідентифікатор елемента руху (маршрута, графіка, типу тз)
	 * @return array Дочірні елементи
	 */
	public function getChildNodes($level, $id){
		if($level == Tree::LEVEL_VEHICLE){
			$node = VehicleType::model()->with('translation')->findbyPk($id);    		                		            
		} else if($level == Tree::LEVEL_ROUTE){
			$node = Route::model()->findByPk($id);			
		} else {
			echo "Невірний  тип елемента дерева";
			return ;
		}
			return $node->getChildren();
	}

	/**
	 * Повертає список елементів для кореневого вузла дерева
	 * @return array Дочірні елементи
	 */
	public function getRootNode(){
		// search routes with carrier
		$carrier_id = Yii::app()->user->carrier->id;

		if ($carrier_id == 0){
			$types = VehicleType::model()->with('translation')->findAll();
			if (Yii::app()->session['lang']=='ua') {
				foreach($types as $item){
		    		$res[] = array(
		    			'id'=> $item->id,
		    			'name' => $item->translation->word_ua,
			    	);
		    	}
		    }	
		    if (Yii::app()->session['lang']=='ru') {
				foreach($types as $item){
		    		$res[] = array(
		    			'id'=> $item->id,
		    			'name' => $item->translation->word_ru,
			    	);
		    	}
		    }
		} else {
	 		$routes = Route::model()->with(array(
				'graphs'=>array(
					'select'=>false,
					'condition'=>'graphs.carriers_id=:carrier',
					'params'=>array(
						':carrier'=>$carrier_id
					),
				),
			))->findAll();
			foreach ($routes as $item) {
				$types[$item->vehicletype->id] = $item->vehicletype;
			}
			$res = array();
	    	foreach($types as $item){
	    		$res[] = array(
	    			'id'=> $item->id,
	    			'name' => $item->name,
		    	);
	    	}
		}


		
    	return $res;
	}
}