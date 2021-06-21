<?php

class ControlpointController extends CController
{
	public function actionRead($id){
		$route = Route::model()->with('controlPointScenarios')->findByPk($id);
		$ctrlPointScenarios = $route->controlPointScenarios;
		if($ctrlPointScenarios){
			$result = array(
				'success' => true,
				'rows' => array(),
			);
			foreach($ctrlPointScenarios as $item){
				$points[$item->number] = array(
					'id' => $item->id,
					'number' => $item->number,
					'title' => $item->point->name
				);
			}
			ksort($points);
			foreach($points as $item){
				$result['rows'][] = $item;
			}
		} else {
			$result = array(
				'success' => false,
				'msg' => Yii::app()->session['RouteHasNoControlPoints'],
			);
		}
		
		echo CJSON::encode($result);
	}
}