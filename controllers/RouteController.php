<?php

/** 
 * RouteController.php
 *
 * Контоллер для роботи з маршрутами.
 *
 * Copyright(c) 2014 Підприємство "Візор"
 * 
 * http://mak.lutsk.ua/
 *
 */

class RouteController extends Controller {
	public function actionCreate() {
		$result = array();
		if( Yii::app()->user->checkAccess('createRoute') ){
			$data = json_decode(stripslashes(file_get_contents('php://input')), true);
			if( !Route::model()->exists( 'name = :name AND transport_types_id = :id',
					array(
						 ':name' => $data['title'], 
						 ':id' => $data['typetzid'],
					) ) ){
				$route = new Route;
				$route->name = $data['title'];
				$vehicle = VehicleType::model()->findByPk($data['typetzid']);
				$route->transport_types_id = $vehicle->id;
				if($route->save()){
					$result = array('success' => true);
				} else {
					$result = array('success' => false);
				}
			} else {
				$result = array(
					'success' => false,
					'msg' => Yii::app()->session['RouteTextFull'].' '.$data['title'].' '.Yii::app()->session['AlreadyExistsPleaseSelectAnotherName'],
				);
			}
		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}	
		echo CJSON::encode($result);
	}

	public function actionDelete($id) {
		$result = array();
		if( Yii::app()->user->checkAccess('deleteRoute') ){			
			$route = Route::model()->findByPk($id);

			foreach($route->schedules as $item){
				$item->delete();
			}

			if($route->delete()){	
				$result = array(
					'success' => true,
				);
			} else {
				$result = array(
					'success' => false, 
					'msg' => Yii::app()->session['RemovingFailed'] 
				);
			}
		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}	
		echo CJSON::encode($result);
	}

	/**
     * Повертає масив із даними про назви маршрутів.
     *
     * @return {array} $arr Масив із даними про назви маршрутів.
     */
	public function actionRoutesName() {
    	/**
    	 * Результат
    	 */
    	$result = array();

    	/**
         * Масив всіх маршрутів міста
         */
    	$routes = Route::model()->getAllRoutesOrderByName();

    	/**
    	 * Запис в масиві $result
    	 */
    	$resultRecord = array();

    	foreach ($routes as $route) {
    		$resultRecord['id'] = $route['id'];
    		$resultRecord['name'] = $route['name'];

    		$result[] = $resultRecord;
    	}

        // Кодуємо результат у формат JSON
        echo CJSON::encode(array(
        	'success' => true,
        	'data' => $result
        ));
	}
}