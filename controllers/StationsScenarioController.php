<?php
class StationsScenarioController extends Controller
{
	public function actionStationsName(){
        if (isset($_POST)){
	        if($_POST['level'] === '2'){
            	$sql = Stations::model()->findAll(array('select'=>'id,name','order' => 'name'));
            	$result = array(
        			'success'=>array(),
					'data'=>array()
	    		);
				foreach($sql as $st){
					$res[$st->id] = $st->name;
		        }
		        asort($res,SORT_LOCALE_STRING); 	    		
				foreach($res as $key => $val){
					$result['data'][] = array(
						'stations_id'	=> $key,						
						'st_name'	 	=> $val						
					);
		        }
		        $result['success'] = "true";
			    echo json_encode($result);             	
	      	}
		}
	}

	public function actionStationsScenarioName(){
	    if (isset($_POST)){
			if($_POST['level'] === '2'){
                $sql = Yii::app()->db->createCommand()
				    ->select('id , name')
				    ->from('points_control pc')
				    ->order('id')
				    ->queryAll();
                $res =  CJSON::encode(array(
                'success'=>'true',
                'data'=>$sql
                ));
                echo $res;
			}
	    }
	}

	public function actionDelete($id){
		if( Yii::app()->user->checkAccess('deleteStationsScenario') ){				
			$stationsc = StationsScenario::model()->with('stations')->findAll(array(
       			'condition'=>'t.id=:delid',
       			'params'=>array(':delid'=>$id)));
       		$stationssc_num = $stationsc[0]['number'];
       		$routes_id  = $stationsc[0]['routes_id'];						
			$del=StationsScenario::model()->deleteByPk($id);	      				
			$stationssc = StationsScenario::model()->with(array(
				'route'=>array(
					'select'=>false,
					'condition'=>'t.routes_id=:routeid',
					'params'=>array(':routeid'=>$routes_id),
				),
			))->findAll(array('select'=>'id,number','order' => 'number'));						
			$i = 1;
            foreach ($stationssc as $stsc){
            	$put=StationsScenario::model()->findByPk($stsc['id']);
				$put->number = $i;
				$put->save();                                	
            	$i++;
            }
            $res =  CJSON::encode(array(
                'success'=>'true'
            ));
            echo $res;
        }
        else{
        	echo "{success: false, msg: 'Not permitted!}";
        }	                        		                                                        
	}

    public function actionRead(){
       if($_GET['level'] === '2'){                               
            $stop = StationsScenario::model()->with('stations','route_directions','poe')->findAll(array(
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
					'stations_id'			=> $stations->stations->id,										
					'route_directions_id'	=> $stations->route_directions->id,
					'poe_id'				=> $stations->poe->id,
					'pc_status'				=> $stations->pc_status
				);
            }
            echo json_encode($result);
	   }
	}

	public function actionUpdate(){
		if( Yii::app()->user->checkAccess('updateStationsScenario') ){
			$data = json_decode(Yii::app()->request->getPut('data'),true);
			$put=StationsScenario::model()->findByPk($data['id']);
			if (isset($data['number'])){
				$put->number = $data['number'];
			}
			if (isset($data['stations_id'])){
				$put->stations_id 	= $data['stations_id'];
			}
			if (isset($data['route_directions_id'])){
				$put->route_directions_id 	= $data['route_directions_id'];
			}
			if (isset($data['poe_id'])){
				$put->points_of_events_id 	= $data['poe_id'];
			}
			if (isset($data['pc_status'])){
				$put->pc_status 	= $data['pc_status'];
			}			
			$put->save();
	        $res =  CJSON::encode(array(
	        'success'=>'true',
	        'data'=>$data
	        ));
	        echo $res;
	    }
	    else{
	    	echo "{success: false, msg: 'Not permitted!}";
	    }
	}

	public function actionCreate(){
	 	if( Yii::app()->user->checkAccess('createStationsScenario') ){	
			$data = json_decode(Yii::app()->request->getPost('data'),true);
			$count = StationsScenario::model()->with('stations')->count(array(
				'condition'=>'t.routes_id=:routeid',
				'params'=>array(':routeid'=>$_POST['nodeid'])));
			$count++;
			$post = new StationsScenario;
			$post->number 				= $count;
			$post->routes_id 			= $_POST['nodeid'];           					   
			$post->stations_id 			= $data['stations_id'];
			$post->route_directions_id 	= $data['route_directions_id'];
			$post->points_of_events_id 	= $data['poe_id'];
			$post->pc_status 			= $data['pc_status'];
			$post->save();
            $res =  CJSON::encode(array(
            	'success'=>'true',
            	//'data'=>$post
            ));
            echo $res;
        }
        else{
        	echo "{success: false, msg: 'Not permitted!}";
        }
	}
	public function actionContentChanges(){
		$contentchanges = ContentChanges::model()->with('route')->findAll(array(
            'select'=>'Id',
            'condition'=>'t.types_of_content_changes_id=:toccid and t.routes_id=:routid',                   					
			'params'=>array(':toccid'=>ContentChanges::SS,':routid'=>$_POST['nodeid']),
        ));
        if ($contentchanges){        	
        	$put=ContentChanges::model()->findByPk($contentchanges[0]['Id']);
			$put->users_id = Yii::app()->user->getId();
			$put->created = date ("Y-m-d H:i:s");
			$put->save();
			$r['type'] = 'update';
			$r['route'] = $contentchanges[0]->route->name;
        }
        else{
			$post = new ContentChanges;
			$post->users_id = Yii::app()->user->getId();
			$post->types_of_content_changes_id = ContentChanges::SS;
			$post->routes_id = $_POST['nodeid'];
			$post->created = date ("Y-m-d H:i:s");
			$post->save();
			$r['type'] = 'insert';
			$route = Route::model()->findByPk($_POST['nodeid']);
			$r['route'] = $route->name;
        }
        $r['user'] = Yii::app()->user->username;
        $res =  CJSON::encode(array(
 	       'success'=>true,
        	'data'=> $r
        ));     
		echo $res;      
	}
	public function actionRoutesName(){
		$pcs = StationsScenario::model()->with('route')->findAll(array(
            'condition'=>'t.stations_id=:ssid',                   					
			'params'=>array(':ssid'=>$_GET['ss_id']),
			'order'=>'route.name'
        ));
        $route_str = '';
        foreach($pcs as $p){
        	$route_str .= $p->route->name.",";
        }
        $r['routes'] = substr($route_str,0,-1);
        $res =  CJSON::encode(array(
 	       'success'=>true,
        	'data'=> $r
        ));     
		echo $res;     
	}
	public function actionContentChangesData(){
		$contentchanges = ContentChanges::model()->with('route')->findAll(array(
            'select'=>'Id,created',
            'condition'=>'t.types_of_content_changes_id=:toccid and t.routes_id=:routid',                   					
			'params'=>array(':toccid'=>ContentChanges::SS,':routid'=>$_GET['nodeid']),
        ));
		if ($contentchanges){
			$r['date'] = $contentchanges[0]->created;
	        $res =  CJSON::encode(array(
	 	        'success'=>true,
	        	'data'=> $r
	        ));     
		}
		else{
        	$res =  CJSON::encode(array(
	 	        'success'=>false,
	        	'data'=> 'no date'
	        ));    
		}
		echo $res;   
	}
	public function actionSTSCUpdate(){
		if( Yii::app()->user->checkAccess('updateStationsScenario') ){
			$data = json_decode(Yii::app()->request->getPost('data'),true);
			$stsc=StationsScenario::model()->findAll(array(
            	'condition'=>'stations_id=:stid and routes_id=:rtid',                   					
				'params'=>array(':stid'=>$data['stations_id'],':rtid'=>$_POST['nodeid']),
        	));			
			$put=StationsScenario::model()->findByPk($stsc[0]->id);
			if (isset($data['number'])){
				$put->number = $data['number'];
			}
			if (isset($data['stations_id'])){
				$put->stations_id 	= $data['stations_id'];
			}
			if (isset($data['route_directions_id'])){
				$put->route_directions_id 	= $data['route_directions_id'];
			}
			if (isset($data['poe_id'])){
				$put->points_of_events_id 	= $data['poe_id'];
			}
			if (isset($data['pc_status'])){
				$put->pc_status 	= $data['pc_status'];
			}			
			$put->save();
	        $res =  CJSON::encode(array(
	        'success'=>'true',
	        'data'=>$data
	        ));
	        echo $res;
	    }
	    else{
	    	echo "{success: false, msg: 'Not permitted!}";
	    }
	}	
}
?>