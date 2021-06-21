<?php
class PointsOfEventsController extends Controller
{
	public function actionDelete($id){
		if( Yii::app()->user->checkAccess('deletePointsOfEvents') ){
			$sql=PointsOfEvents::model()->deleteByPk($id);
			$res =  CJSON::encode(array(
                'success'=>true,
                'msg'=> Yii::app()->session['RecordDeleted'],
            ));
            echo $res;
        }else {
			echo "{success: false, messsage: 'Not permitted!}";
		}
	}
    public function actionRead(){
		$carrier = Yii::app()->user->checkUser(Yii::app()->user);                  	
        if($_GET['level'] === '1'){
        	if ($carrier){
        		$sql = PointsOfEvents::model()->with('stations_scenario.route')->findAll(array(
			        'condition'=>'route.carriers_id=:carrid and t.id = stations_scenario.points_of_events_id and stations_scenario.pc_status = "yes"',                   					
					'params'=>array(':carrid'=>$carrier['carrier_id']),        			
        			'order' => 't.id'
        		));
/*
        		$st_l = StationsScenario::model()->with('route')->findAll(array(
			        'condition'=>'route.carriers_id=:carrid',                   					
					'params'=>array(':carrid'=>$carrier['carrier_id']),        			
        			'order' => 't.id'
        		));
        		foreach ($st_l as $key) {
        			$st_id[] =  $key->points_of_events_id;
        		}
        		$stsc_list = '('.implode(",", $st_id).')';        		

        		$sql = PointsOfEvents::model()->findAll(array(
			        'condition'=>'t.id in '.$stsc_list,                  										     			
        			'order' => 't.id'
        		));
*/        		        		
        	}
        	else{
            	$sql = PointsOfEvents::model()->findAll(array('order' => 'id'));
            }
            $res =  CJSON::encode(array(
            	'success'=>true,
            	'data'=>$sql
            ));
            echo $res;
	    }
	}
    public function actionUpdate(){
		if( Yii::app()->user->checkAccess('updatePointsOfEvents') ){
			$data = json_decode(Yii::app()->request->getPut('data'),true);

	        $sql=PointsOfEvents::model()->findByPk($data['id']);
	        while (list($key, $value) = each($data)) {
				$sql->$key=$value;
			}
			$sql->save();
			$stsc = StationsScenario::model()->with('route')->findAll(array(
	            'condition'=>'t.points_of_events_id=:poeid',                   					
				'params'=>array(':poeid'=>$data['id']),
				'order'=>'route.id'
	        ));	        
	        foreach($stsc as $p){
	        	if ($p->pc_status == 'yes'){
	        		$type_of_content_change = ContentChanges::PCS;
					$contentchanges = ContentChanges::model()->with('route')->findAll(array(
			            	'select'=>'Id',
			                'condition'=>'t.types_of_content_changes_id=:toccid and t.routes_id=:routid',                   					
							'params'=>array(':toccid'=>ContentChanges::SS,':routid'=>$p->route->id),
			        ));			        
					$put=ContentChanges::model()->findByPk($contentchanges[0]['Id']);

					if(isset($put->Id)){					
						$put->users_id = Yii::app()->user->getId();				
						$put->routes_id = $p->route->id;
						$put->created = date ("Y-m-d H:i:s");
						$put->save();
					}	        		
					
			    }
			    elseif ($p->pc_status == 'no') {
			        $type_of_content_change = ContentChanges::SS;
			    }
				$contentchanges = ContentChanges::model()->with('route')->findAll(array(
		            	'select'=>'Id',
		                'condition'=>'t.types_of_content_changes_id=:toccid and t.routes_id=:routid',                   					
						'params'=>array(':toccid'=>$type_of_content_change,':routid'=>$p->route->id),
		        ));			        
				$put=ContentChanges::model()->findByPk($contentchanges[0]['Id']);				
				if(isset($put->Id)){
					$put->users_id = Yii::app()->user->getId();				
					$put->routes_id = $p->route->id;
					$put->created = date ("Y-m-d H:i:s");
					$put->save();	        	
				}	
	        }      	        
	        $res =  CJSON::encode(array(
		        'success'=>true,
		        'msg'=> Yii::app()->session['RecordUpdated'],
		        'data'=>$data
	        ));
	        echo $res;
    	}else {
			echo '{"success": "false", "msg": "Not permitted!"}';
		}
	}
	public function actionCreate(){
		if( Yii::app()->user->checkAccess('createPointsOfEvents') ){
			    $data = json_decode(Yii::app()->request->getPost('data'),true);
			    $sql = new PointsOfEvents;
				while (list($key, $value) = each($data)) {
				$sql->$key=$value;
			}
			$sql->save();
            $res =  CJSON::encode(array(
            'success'=>'true'/*,
            'data'=>$data*/
            ));
            echo $res;
		} else {
				echo "{success: false, msg: 'Not permitted!}";
		}
	}
	public function actionPOEName(){
        if (isset($_POST)){
 	        if($_POST['level'] === '2'){
            	$sql = PointsOfEvents::model()->findAll(array('select'=>'id,name, name as poe_id','order' => 'name'));
            	$result = array(
        			'success'=>array(),
					'data'=>array()
	    		);
				foreach($sql as $poe){
					$res[$poe->id] = $poe->name;
		        }
		        asort($res,SORT_LOCALE_STRING);   		
				foreach($res as $key => $val){
					$result['data'][] = array(
						'poe_id'		=> $key,						
						'poe_name'	 	=> $val						
					);
		        }
		        $result['success'] = "true";	        
			    echo json_encode($result);            	
            /*	$res =  CJSON::encode(array(
            		'success'=>'true',
            		'data'=>$sql
            	));
            	echo $res;*/
	      	}
		}
	}
	public function actionPOEtoMap()
    {
        $distance_limit = 300;
        $count = 0;
        $result = array(
			'points'=>array(),
			'count'=>array()
	    );
		$stsc = StationsScenario::model()->with('poe')->findAll(array(
			'condition'=>'t.routes_id=:routeid',
			'params'=>array(':routeid'=>$_GET['nodeid']),
			'order' => 't.number')
		);
		$result = array(
			'success'=>true,
			'data'=>array(),
		);
	  	$poes = PointsOfEvents::model()->findAll(array(
       		'order' => 't.id'));
	  	$route_point = 'no';
        foreach($poes as $poe){
			foreach($stsc as $stscs){
				if($stscs->points_of_events_id ===  $poe->id){
					$route_point = 'yes';					
				}		
            }
            if (PointsOfEvents::model()->dist_calc($poe->latitude,$poe->longitude,$_GET['lat'],$_GET['lng']) < $distance_limit){   	
				$result['points'][] = array(
					'id'			=> $poe->id,
					'name'			=> $poe->name,
					'latitude'	 	=> (double) (floor($poe->latitude/100)*100+(($poe->latitude   - floor($poe->latitude/100)*100)*100/60))/100,
					'longitude' 	=> (double) (floor($poe->longitude/100)*100+(($poe->longitude - floor($poe->longitude/100)*100)*100/60))/100,
					'route_point'	=> $route_point
				);
				$count++;
			}	
			$route_point = 'no';
        	
            
        }
        $result['count'] = $count;
        echo json_encode($result);
	}
	public function actionPoints()
    {
        $count = 0;
        $result = array(
			'points'=>array(),
			'count'=>array()
	    );
	  	$poes = PointsOfEvents::model()->findAll(array(
       		'order' => 't.id'));
        foreach($poes as $poe){
			$result['points'][] = array(
				'id'			=> $poe->id,
				'name'			=> $poe->name,
				'latitude'	 	=> (double) (floor($poe->latitude/100)*100+(($poe->latitude   - floor($poe->latitude/100)*100)*100/60))/100,
				'longitude' 	=> (double) (floor($poe->longitude/100)*100+(($poe->longitude - floor($poe->longitude/100)*100)*100/60))/100,
			);
            $count++;
        }
        $result['count'] = $count;
        echo json_encode($result);
	}
	public function actionRoutesName(){
		$stsc = StationsScenario::model()->with('route')->findAll(array(
            'condition'=>'t.points_of_events_id=:poeid',                   					
			'params'=>array(':poeid'=>$_GET['poe_id']),
			'order'=>'route.name'
        ));
        $route_str = '';
        foreach($stsc as $p){
        	$route_str .= $p->route->name.",";
        }
        $r['routes'] = substr($route_str,0,-1);
        $res =  CJSON::encode(array(
 	       'success'=>true,
        	'data'=> $r
        ));     
		echo $res;     
	}
	public function actionContentChanges(){
		$contentchanges = ContentChanges::model()->with('route')->findAll(array(
            	'select'=>'Id',
                'condition'=>'t.types_of_content_changes_id=:toccid and t.routes_id=:routid',                   					
				'params'=>array(':toccid'=>ContentChanges::PCS,':routid'=>$_POST['nodeid']),
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
			$post->types_of_content_changes_id = ContentChanges::PCS;
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
	
}
?>