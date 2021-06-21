<?php
class RouteDirectionsController extends Controller
{
	public function actionCreate(){
		if( Yii::app()->user->checkAccess('createDirections') ){
		    $data = json_decode(Yii::app()->request->getPost('data'),true);
			$count = RouteDirections::model()->count(array(
				'condition'=>'t.routes_id=:routeid',
				'params'=>array(':routeid'=>$_POST['nodeid'])));
			$count++;		    
			$post = new RouteDirections;
			$post->number 				= $data['number'];
			$post->routes_id 			= $_POST['nodeid'];           					   
			$post->stations_id_from 	= $data['stations_id_from']; 
			$post->stations_id_to 		= $data['stations_id_to']; 
			$post->name 				= $data['dir_name']; 
			$post->save();
			$data['id'] = $post->id;
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
       if($_GET['level'] === '2'){                               
            $stop = RouteDirections::model()->findAll(array(
   				'condition'=>'t.routes_id=:routeid',
   				'params'=>array(':routeid'=>$_GET['nodeid']),
   				'order' => 't.number')
   			);
   			$result = array(
				'success'=>true,
				'data'=>array(),
			);
            foreach($stop as $stations){
				$result['data'][] = array(
					'id'					=> $stations->id,
					'number'				=> $stations->number,
					'stations_id_from'		=> $stations->stations_id_from,										
					'stations_id_to'		=> $stations->stations_id_to,
					'dir_name'				=> $stations->name,
				);
            }
            echo json_encode($result);
	   	}
	}	
	public function actionUpdate(){
		if( Yii::app()->user->checkAccess('updateDirections') ){
			$data = json_decode(Yii::app()->request->getPut('data'),true);
			$put=RouteDirections::model()->findByPk($data['id']);
			if (isset($data['number'])){
				$put->number = $data['number'];
			}
			if (isset($data['stations_id_from'])){
				$put->stations_id_from 	= $data['stations_id_from'];
			}
			if (isset($data['stations_id_to'])){
				$put->stations_id_to 	= $data['stations_id_to'];
			}
			if (isset($data['dir_name'])){
				$put->name 	= $data['dir_name'];
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
			$rd = RouteDirections::model()->findAll(array(
       			'condition'=>'t.id=:delid',
       			'params'=>array(':delid'=>$id)));
			$st = StationsScenario::model()->findAll(array(
            	'select'=>'id,route_directions_id',
		        'condition'=>'t.route_directions_id=:rdid and t.routes_id=:routid',                   					
				'params'=>array(':rdid'=>$id, ':routid'=>$rd[0]->routes_id)            						
			));
			/*if(isset($st[0]->route_directions_id)){
	       		$res =  CJSON::encode(array(
	                'success' => true,
	                'message' => Yii::app()->session['AccessDenied']
	            ));	            	
			}
			else{*/
				RouteDirections::model()->deleteByPk($id);
	       		$res =  CJSON::encode(array(
	                'success' => true,
	                'message' => Yii::app()->session['RecordDeleted']
	            ));
			//}
        }
        else{
        	$res =  CJSON::encode(array(
	            'success' => false,
	            'message' => Yii::app()->session['AccessDenied']
	        ));
        }
        echo $res;                      		                                                        
	}
	public function actionRDName(){
        if (isset($_POST)){
	        if($_POST['level'] === '2'){
            	$sql = RouteDirections::model()->findAll(array(
            		'select'=>'id,name',
		            'condition'=>'t.routes_id=:routid',                   					
					'params'=>array(':routid'=>$_POST['nodeid']),
            		'order' => 'name'
            	));
            	$result = array(
        			'success'=>array(),
					'data'=>array()
	    		);
				foreach($sql as $rd){
					$result['data'][] = array(
						'route_directions_id'		=> $rd->id,						
						'dir_name'	 	=> $rd->name						
					);
		        }
		        $result['success'] = "true";
			    echo json_encode($result);             	
	      	}
		}
	}	
	public function actionStationsNameFrom(){
        if (isset($_POST)){
	        if($_POST['level'] === '2'){
            	/*$sql = StationsScenario::model()->with('stations')->findAll(array('select'=>'id,stations_id',
		            'condition'=>'t.routes_id=:routid',                   					
					'params'=>array(':routid'=>$_POST['nodeid']),
            		'order' => 'number'));*/
				$sql = Stations::model()->findAll(array(
					'order' => 'name'
				));
            	$result = array(
        			'success'=>array(),
					'data'=>array()
	    		);
				foreach($sql as $st){
					$result['data'][] = array(
						'stations_id_from'	=> $st->id,
						'stations_name_from'	=> $st->name
					);
		        }
		        $result['success'] = "true";
			    echo json_encode($result);             	
	      	}
		}
	}
	public function actionStationsNameTo(){
        if (isset($_POST)){
	        if($_POST['level'] === '2'){
            	/*$sql = StationsScenario::model()->with('stations')->findAll(array('select'=>'id,stations_id',
		            'condition'=>'t.routes_id=:routid',                   					
					'params'=>array(':routid'=>$_POST['nodeid']),
            		'order' => 'number'));*/
				$sql = Stations::model()->findAll(array(
					'order' => 'name'
				));
            	$result = array(
        			'success'=>array(),
					'data'=>array()
	    		);
				foreach($sql as $st){
					$result['data'][] = array(
						'stations_id_to'		=> $st->id,
						'stations_name_to'	=> $st->name
					);
		        }
		        $result['success'] = "true";
			    echo json_encode($result);             	
	      	}
		}
	}	
}
?>