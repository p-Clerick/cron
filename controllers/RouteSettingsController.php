<?php

class RouteSettingsController extends Controller
{
	public function actionCreate(){
    	if( Yii::app()->user->checkAccess('createSettings') ){		
			if($_POST['nodeid']){
				$routechanges = RouteSettings::model()->with('route','networks','gpssettings','graphsettings')->findAll(array(
		            	'select'=>'Id',
		                'condition'=>'t.routes_id=:routid',                   					
						'params'=>array(':routid'=>$_POST['nodeid']),
		        ));

		        if ($routechanges){        	
		        	$put=RouteSettings::model()->findByPk($routechanges[0]['Id']);
					$put->networks_id 			= $_POST['net'];
					$put->graph_settings_id 	= $_POST['graph'];
					$put->gps_settings_id 		= $_POST['gps'];
					$put->created 				= date ("Y-m-d H:i:s");
					$put->save();
					$r['type'] = 'update';
					$r['route'] = $routechanges[0]->route->name;
		        }
		        else{
					if($_POST['net']){        	
						$post = new RouteSettings;
						$post->networks_id 			= $_POST['net'];
						$post->graph_settings_id 	= $_POST['graph'];
						$post->gps_settings_id 		= $_POST['gps'];
						$post->routes_id 			= $_POST['nodeid'];
						$post->created 				= date ("Y-m-d H:i:s");
						$post->save();
						$r['type'] = 'insert';
						$route = Route::model()->findByPk($_POST['nodeid']);
						$r['route'] = $route->name;
					}
					else{					
						return false;				
					}
		        }
		        $r['user'] = Yii::app()->user->username;
		        $res =  CJSON::encode(array(
		 	       'success'=>true,
		        	'data'=> $r
		        ));     
				echo $res;      
			}
		}
		else{		       
		        $r['user'] = Yii::app()->user->username;
		        $r['type'] = 'noaccess';
		        $res =  CJSON::encode(array(
		 	       'success'=>false,
		        	'data'=> $r
		        ));     
				echo $res;
		}
	}
	public function actionRead(){
		$result = array();
		$routesettings = RouteSettings::model()->with('route', 'networks', 'gpssettings','graphsettings')->findAll(array(
            	//'select'=>'Id,created',
                'condition'=>'t.routes_id=:routid',                   					
				'params'=>array(':routid'=>$_GET['nodeid']),
        ));
		foreach ($routesettings as $rs){
         	$result['network'] = array(
         		'Id'		=> $rs->networks->Id,
             	'name' 		=> $rs->networks->name,
             	'apn' 		=> $rs->networks->apn,
             	'login' 	=> $rs->networks->login,
             	'pass' 		=> $rs->networks->pass,
             	'type' 		=> $rs->networks->type,
             	'port' 		=> $rs->networks->port,
             	'host'		=> $rs->networks->host,
             	'port_data' => $rs->networks->port_data,
             	'port_ex' 	=> $rs->networks->port_ex,
             	'port_file' => $rs->networks->port_file
         	);
         	$result['graph'] = array(
         		'Id'				=> $rs->graphsettings->Id,         		
         		'name' 				=> $rs->graphsettings->name,
             	'advanceTime' 		=> $rs->graphsettings->advanceTime,
             	'latenessTime' 		=> $rs->graphsettings->latenessTime,
             	'orderqueryTime' 	=> $rs->graphsettings->orderqueryTime
         	);
         	$result['gps'] = array(
         		'Id'		=> $rs->gpssettings->Id,          		
         		'name' 		=> $rs->gpssettings->name,
             	'accpoint' 	=> $rs->gpssettings->accpoint,
             	'speed' 	=> $rs->gpssettings->speed,
             	'degree' 	=> $rs->gpssettings->degree,             	
             	'period' 	=> $rs->gpssettings->period
         	);         	         	

		}
        $res =  CJSON::encode(array(
	 	        'success'=>true,
	        	'data'=> $result
	    ));   
		echo $res;		
	}
	public function actionNetworks(){
		$networks = Network::model()->findAll();
		foreach	($networks as $nt){
			$result[] = array(
				'Id'	=> $nt->Id,
				'name'	=> $nt->name
			);
		}
        $res =  CJSON::encode(array(
	 	        'success'=>true,
	        	'data'=> $result
	    ));   
		echo $res; 
	}
	public function actionGraphs(){
		$networks = GraphSettings::model()->findAll();
		foreach	($networks as $nt){
			$result[] = array(
				'Id'	=> $nt->Id,
				'name'	=> $nt->name
			);
		}
        $res =  CJSON::encode(array(
	 	        'success'=>true,
	        	'data'=> $result
	    ));   
		echo $res; 
	}
	public function actionGps(){
		$networks = GpsSettings::model()->findAll();
		foreach	($networks as $nt){
			$result[] = array(
				'Id'	=> $nt->Id,
				'name'	=> $nt->name
			);
		}
        $res =  CJSON::encode(array(
	 	        'success'=>true,
	        	'data'=> $result
	    ));   
		echo $res; 
	}
	public function actionNetworkRead($id){
		$networks = Network::model()->findAll(array(
                'condition'=>'t.Id=:ntid',                   					
				'params'=>array(':ntid'=>$id),
        ));
        $res =  CJSON::encode(array(
	 	        'success'=>true,
	        	'data'=> $networks
	    ));   
		echo $res; 
	}
	public function actionGraphRead($id){
		$graph = GraphSettings::model()->findAll(array(
                'condition'=>'t.id=:ntid',                   					
				'params'=>array(':ntid'=>$id),
        ));
        $res =  CJSON::encode(array(
	 	        'success'=>true,
	        	'data'=> $graph
	    ));   
		echo $res; 
	}
	public function actionGpsRead($id){
		$gps = GpsSettings::model()->findAll(array(
                'condition'=>'t.id=:ntid',                   					
				'params'=>array(':ntid'=>$id),
        ));
        $res =  CJSON::encode(array(
	 	        'success'=>true,
	        	'data'=> $gps
	    ));   
		echo $res; 
	}
}