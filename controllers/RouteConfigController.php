<?php
class RouteConfigController extends Controller
{
	public function actionCreate(){
		if ( Yii::app()->user->checkAccess('createRouteConfig') ){
		    $data = json_decode(Yii::app()->request->getPost('data'),true);   
			$route = new Route;
			$route->id 					= $data['route_number'];
			$route->name 				= $data['route_name'];
			$route->transport_types_id 	= $_POST['nodeid'];					
			$route->route_types_id 		= $data['route_types_id'];										
			$route->move_methods_id 	= $data['move_methods_id'];
			$route->carriers_id 		= $data['carriers_id'];
			$route->status				= $data['route_status'];
			$route->save();
            $res =  CJSON::encode(array(
            	'success' => true,
            	'data' => $data,
            	'message' => Yii::app()->session['RecordAdded']
            	
            ));
		} else {
        	$res =  CJSON::encode(array(
	            'success' => false,
	            'message' => Yii::app()->session['AccessDenied']
	        ));
		}
		echo $res;
	}    
    public function actionRead(){
    	$carrier = Yii::app()->user->checkUser(Yii::app()->user);
        if ($_GET['level'] === '1'){
			if ($carrier){
				$route = Route::model()->findAll(array(
	   				'condition'=>'t.transport_types_id=:trtid and t.carriers_id=:carrid',
	   				'params'=>array(':trtid'=>$_GET['nodeid'],':carrid'=>$carrier['carrier_id']),
	   				'order' => 't.name')
	   			);
			}
			else{
	            $route = Route::model()->findAll(array(
	   				'condition'=>'t.transport_types_id=:trtid',
	   				'params'=>array(':trtid'=>$_GET['nodeid']),
	   				'order' => 't.name')
	   			);
	   		}	
			$res =  array(
	        	'success' => true,
	        	'data' => array()
	        );
            foreach($route as $routes){
				$res['data'][] = array(
					'id'					=> $routes->id,
					'route_number'			=> $routes->id,
					'route_name'			=> $routes->name,					
					'route_types_id'		=> $routes->route_types_id,										
					'move_methods_id'		=> $routes->move_methods_id,
					'carriers_id'			=> $routes->carriers_id,
					'route_status'			=> $routes->status										

				);
            }
            echo CJSON::encode($res);
	   	}
	}

	public function actionUpdate(){
		if( Yii::app()->user->checkAccess('updateDirections') ){
			$data = json_decode(Yii::app()->request->getPut('data'),true);
			$put=Route::model()->findByPk($data['id']);
			if (isset($data['route_number'])){
				$put->id 				= $data['route_number'];
			}
			if (isset($data['route_name'])){
				$put->name 				= $data['route_name'];
			}
			if (isset($data['route_types_id'])){
				$put->route_types_id 	= $data['route_types_id'];
			}
			if (isset($data['move_methods_id'])){
				$put->move_methods_id 	= $data['move_methods_id'];
			}
			if (isset($data['carriers_id'])){
				$put->carriers_id 		= $data['carriers_id'];
			}
			if (isset($data['route_status'])){
				$put->route_status 		= $data['route_status'];
			}			
			$put->save();
	        $res =  CJSON::encode(array(
	        	'success' => true,
	        	'data' => $data,
	        	'message' => Yii::app()->session['RecordUpdated']
	        	
	        ));

	    }
	    else{
	        $res =  CJSON::encode(array(
	        	'success' => false,
	        	'message' => Yii::app()->session['AccessDenied']
	        ));	    	

	    }
	    echo $res;
	}
	public function actionDelete($id){
		if( Yii::app()->user->checkAccess('deleteDirections') ){				
			$route = Route::model()->findAll(array(
       			'condition'=>'t.id=:delid',
       			'params'=>array(':delid'=>$id)));
			$graph = Graphs::model()->findAll(array(
            	'select'=>'id, routes_id',
		        'condition'=>'t.routes_id=:routid',                   					
				'params'=>array(':routid'=>$route[0]->id)            						
			));
			if(isset($graph[0]->routes_id)){
	       		$res =  CJSON::encode(array(
	                'success' => true,
	                'message' => Yii::app()->session['AccessDenied']
	            ));	            	
			}
			else{
				Route::model()->deleteByPk($id);
	       		$res =  CJSON::encode(array(
	                'success' => true,
	                'message' => Yii::app()->session['RecordDeleted']
	            ));
			}
        }
        else{
        	$res =  CJSON::encode(array(
	            'success' => false,
	            'message' => Yii::app()->session['AccessDenied']
	        ));
        }
        echo $res;	                        		                                                        
	}			
	
}
?>