<?php

/** 
 * StationsController.php
 *
 * Контоллер для роботи з зупинками.
 *
 * Copyright(c) 2014 Підприємство "Візор"
 * 
 * http://mak.lutsk.ua/
 *
 */

class StationsController extends Controller
{
	public function actionStations(){
      	if (isset($_POST)){
		    if($_POST['level'] === '1'){
                $sql = Stations::model()->findAll(array('order' => 'id'));
                $res =  CJSON::encode(array(
                'success'=>'true',
                'data'=>$sql
                ));
                echo $res;
		    }
      	}
	}
	public function actionDelete($id){
		if( Yii::app()->user->checkAccess('deleteStations') ){
			$sql=Stations::model()->deleteByPk($id);
			$res =  CJSON::encode(array(
                'success'=>'true'
            ));
            echo $res;
		}else {
			echo "{success: false, msg: 'Not permitted!}";
		}
	}
    public function actionRead(){
    	$carrier = Yii::app()->user->checkUser(Yii::app()->user);
        //if($_GET['level'] === '1'){
    	if ($carrier){
			$sql = Stations::model()->with('stations_scenario.route')->findAll(array(
		        'condition'=>'route.carriers_id=:carrid and t.id = stations_scenario.stations_id',                   					
				'params'=>array(':carrid'=>$carrier['carrier_id']),        			
    			'order' => 't.id'
        	));
    	}
    	else{
            $sql = Stations::model()->findAll(array('order' => 'id'));
        }
            $res =  CJSON::encode(array(
            	'success'=>'true',
            	'data'=>$sql
            ));
            echo $res;
	    //}
	}
    public function actionUpdate(){
		if( Yii::app()->user->checkAccess('updateStations') ){
			$data = json_decode(Yii::app()->request->getPut('data'),true);
	        $sql=Stations::model()->findByPk($data['id']);
        	while (list($key, $value) = each($data)) {
				$sql->$key=$value;
			}
			$sql->save();
	        $res =  CJSON::encode(array(
		        'success'=>'true',
		        'data'=>$data
	        ));
	        echo $res;
        }else {
			echo "{success: false, msg: 'Not permitted!}";
		}
	}
	public function actionCreate(){
		if( Yii::app()->user->checkAccess('createStations') ){
		   $data = json_decode(Yii::app()->request->getPost('data'),true);
		   $sql = new Stations;
			while (list($key, $value) = each($data)) {
				$sql->$key=$value;
			}
			$sql->save();
        	$res =  CJSON::encode(array(
        		'success'=>'true',
        		'data'=>$data
        	));
        	echo $res;
		} else {
			echo "{success: false, msg: 'Not permitted!}";
		}
	}
	public function actionStationsPoints()
    {
        $count = 0;
        $result = array(
			'points'=>array(),
			'count'=>array()
	    );
	  	if($_POST['level'] === '1'){
	  		$stations = Stations::model()->findAll(array(
       			'order' => 't.id'));
		}
	  	if($_POST['level'] === '2' or $_POST['level'] === '3'){
	  		$stations = Stations::model()->with('stopsscenario.points_control_scenario.route')->findAll(array(
				'condition'=>'route.id=:routid',
				'params'=>array(':routid'=>$_POST['nodeid']),
				'order' => 't.id'));
	  	}
        foreach($stations as $stop){
			$result['points'][] = array(
				'id'			=> $stop->id,
				'name'			=> $stop->name,
				'latitude'	 	=> (double) (floor($stop->latitude/100)*100+(($stop->latitude   - floor($stop->latitude/100)*100)*100/60))/100,
				'longitude' 	=> (double) (floor($stop->longitude/100)*100+(($stop->longitude - floor($stop->longitude/100)*100)*100/60))/100,
			);
            $count++;
        }
        $result['count'] = $count;
        echo json_encode($result);
	}
	public function actionStationstoMap()
    {
        $count = 0;
        $result = array(
			'points'=>array(),
			'count'=>array()
	    );
		$stsc = StationsScenario::model()->with('stations')->findAll(array(
			'condition'=>'t.routes_id=:routeid',
			'params'=>array(':routeid'=>$_GET['nodeid']),
			'order' => 't.number')
   		);
   		$result = array(
			'success'=>true,
			'data'=>array(),
		);
		$route_point = 'no';
	  	$stations = Stations::model()->findAll(array(
       		'order' => 't.id'));
        foreach($stations as $stop){
			foreach($stsc as $stscs){
				if($stscs->stations_id ===  $stop->id){
					$route_point = 'yes';					
				}			
            }   	
			$result['points'][] = array(
				'id'			=> $stop->id,
				'name'			=> $stop->name,
				'latitude'	 	=> (double) (floor($stop->latitude/100)*100+(($stop->latitude   - floor($stop->latitude/100)*100)*100/60))/100,
				'longitude' 	=> (double) (floor($stop->longitude/100)*100+(($stop->longitude - floor($stop->longitude/100)*100)*100/60))/100,
				'route_point'	=> $route_point,
			);
			$route_point = 'no';        	
            $count++;
        }
        $result['count'] = $count;
        echo json_encode($result);
	}


	/**
     * Повертає масив із даними про назви зупинок.
     *
     * @return {array} $arr Масив із даними про назви зупинок.
     */
	public function actionStationsName() {
    	/**
    	 * Результат
    	 */
    	$result = array();

    	/**
         * Масив всіх зупинок міста
         */
    	$stations = Stations::model()->getAllStationsOrderByName();

    	/**
    	 * Запис в масиві $result
    	 */
    	$resultRecord = array();

    	foreach ($stations as $stations) {
    		$resultRecord['id'] = $stations['id'];
    		$resultRecord['name'] = $stations['name'];

    		$result[] = $resultRecord;
    	}

        // Кодуємо результат у формат JSON
        echo CJSON::encode(array(
        	'success' => true,
        	'data' => $result
        ));
	}
}
?>