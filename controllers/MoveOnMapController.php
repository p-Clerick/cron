<?php
class MoveOnMapController extends Controller{
   public function actionRead(){
   		$this->writeLog = false;
		if($_GET['level'] === '1'){
			if(Yii::app()->user->name != "guest"){
				$carrier = Yii::app()->user->checkUser(Yii::app()->user);
			}
			if (isset($carrier)){
				$moveonmaps = MoveOnMap::model()->with(array(
					'bort'=>array(                                  
						'select'=>'number,state_number,special_needs',
						'condition'=>'connection = "yes" ',
					),
					'bort.model'=>array(                                  
						'select'=>false,
						'condition'=>'model.transport_types_id=:trtypeid ',
						'params'=>array(':trtypeid'=>$_GET['nodeid']),
					),
					'bort.park.carrier'=>array(                                  
						'select'=>'name',
					),
					'graph'=>array(                                  
						'select'=>'name',
					),
					'route'=>array(                                  
						'select'=>'name',
	                    'condition'=>'route.carriers_id=:carrid',
	                    'params'=>array(':carrid'=>$carrier['carrier_id']),						
					)))           	                   	
	           		->findAll(array(
	   					'select'=>'id, latitude, longitude, speed, direction, datatime,time_difference',
	   					'condition'=>'date(t.datatime) = CURRENT_DATE()',                   					
	   					'order' => 't.id')
	           		);
            }
            else{			
	           $moveonmaps = MoveOnMap::model()->with(array(
					'bort'=>array(                                  
						'select'=>'number,state_number,special_needs',
						'condition'=>'connection = "yes" ',
					),
					'bort.model'=>array(                                  
						'select'=>false,
						'condition'=>'model.transport_types_id=:trtypeid ',
						'params'=>array(':trtypeid'=>$_GET['nodeid']),
					),
					'bort.park.carrier'=>array(                                  
						'select'=>'name',
					),					
					'graph'=>array(                                  
						'select'=>'name',
					),'route'=>array(                                  
						'select'=>'name',
					)))           	                   	
	           		->findAll(array(
	   					'select'=>'id, latitude, longitude, speed, direction, datatime,time_difference',
	   					'condition'=>'date(t.datatime) = CURRENT_DATE()',                   					
	   					'order' => 't.id')
	           		);
	        }   		
      	}
      	if($_GET['level'] === '2'){
      		//kostylyk
      		if ($_GET['nodeid'] == 6){
      			$r_id = 0;
      		}
      		else{
      			$r_id = $_GET['nodeid'];
      		}
           	$moveonmaps = MoveOnMap::model()->with(array(
				'bort'=>array(                                  
                    'select'=>'number,state_number,special_needs',
                    'condition'=>'connection = "yes" ',
                ),
				'graph'=>array(                                  
                    'select'=>'name',						                                    
                ),
                'bort.park.carrier'=>array(                                  
					'select'=>'name',
				),
                'route'=>array(                                  
                   'select'=>'name',
                )))                   	
           		->findAll(array(
   					'select'=>'id, latitude, longitude, speed, direction, datatime,time_difference',
   					'condition'=>'date(t.datatime) = CURRENT_DATE() and t.routes_id=:rtid',
   					'params'=>array(':rtid'=>$r_id),                   					
   					'order' => 't.id')
				);
      	}
      	if($_GET['level'] === '3'){
           	$moveonmaps = MoveOnMap::model()->with(array(
				'bort'=>array(                                  
				  	'select'=>'number,state_number,special_needs',
				  	'condition'=>'connection = "yes" ',
				),
				'graph'=>array(                                  
                    'select'=>'name',						                                    
                ),
				'bort.park.carrier'=>array(                                  
					'select'=>'name',
				),
                'route'=>array(                                  
				  	'select'=>'name',
				)))           	
           		->findAll(array(
   					'select'=>'id, latitude, longitude, speed, direction, datatime,time_difference',
   					'condition'=>'date(t.datatime) = CURRENT_DATE() and t.graphs_id=:grid',
   					'params'=>array(':grid'=>$_GET['nodeid']),                   					
   					'order' => 't.id')
				);
      	}                            
		$count = 0;
		$result = array(
			'mark'=>array(),
			'count'=>array()
		);
      	foreach($moveonmaps as $moveonmap){
      		if (isset($moveonmap->route->name)){
				$routename = $moveonmap->route->name;
				$graph = $moveonmap->graph->name;
			}
			else{
				$routename = "100";
				$graph = 1;
			}
            /*if (isset($moveonmap->time_difference)){
				$timediff	= $moveonmap->time_difference;
			}
			else{
				$timediff	= 'немає в графіку';
			}*/
			$result['mark'][] = array(
				'id'		=> $moveonmap->id,
				'n'			=> $moveonmap->bort->number,
				'sn'		=> $moveonmap->bort->state_number,
				'sb'		=> $moveonmap->bort->special_needs,
				'car'		=> $moveonmap->bort->park->carrier->name,
				'lt'	 	=> round((double) (floor($moveonmap->latitude/100)*100+(($moveonmap->latitude   - floor($moveonmap->latitude/100)*100)*100/60))/100,6),
				'ln' 		=> round((double) (floor($moveonmap->longitude/100)*100+(($moveonmap->longitude - floor($moveonmap->longitude/100)*100)*100/60))/100,6),
				//'latitude_ukr'  	=> $moveonmap->latitude,
				//'longitude_ukr' 	=> $moveonmap->longitude,
				's'			=> $moveonmap->speed,
				'c'			=> $moveonmap->direction,
				'd' 		=> $moveonmap->datatime,
				'r'			=> $routename,
				//'img'		=> $routename,
				'g'     	=> (int)$graph,
				//'e'		=> 'трек',
				'td'		=> $moveonmap->time_difference
			);
            $count++;
      	}
      $result['count'] = $count;
      echo json_encode($result);
   	}
   	public function actionStops(){
        $count = 0;
        $result = array(
			'points'=>array(),
			'count'=>array()
		);
	  	if($_POST['level'] === '1'){      		  		
           	$poe = StationsScenario::model()->with('poe', 'route')->findAll(array(
				'condition'=>'route.transport_types_id=:trtypeid',
				'params'=>array(':trtypeid'=>$_POST['nodeid']),
				'order' => 't.id')
			);	
		}
		if($_POST['level'] === '2'){      		  		
           	$poe = StationsScenario::model()->with('poe', 'route')->findAll(array(
				'condition'=>'t.routes_id=:routeid',
				'params'=>array(':routeid'=>$_POST['nodeid']),
				'order' => 't.number')
			);
		}
		if($_POST['level'] === '3'){      		  		
           	$poe = StationsScenario::model()->with('poe', 'route.graphs')->findAll(array(
				'condition'=>'graphs.id=:grid',
				'params'=>array(':grid'=>$_POST['nodeid']),
				'order' => 't.number')
           	);
		} 
  		foreach($poe as $stop){
      		if(!isset($stop->poe->id)){
				echo $stop->id; echo ",";                     			
      		}
			$result['points'][] = array(
				'id'	=> $stop->poe->id,
				'name'	=> $stop->poe->name,
				'lt'	=> (double) (floor($stop->poe->latitude/100)*100+(($stop->poe->latitude   - floor($stop->poe->latitude/100)*100)*100/60))/100,
				'ln' 	=> (double) (floor($stop->poe->longitude/100)*100+(($stop->poe->longitude - floor($stop->poe->longitude/100)*100)*100/60))/100,
				'r'		=> $stop->route->name,
			);
            $count++;
        }
        $result['count'] = $count;
        echo json_encode($result);
   	}
	public function actionStations(){
        $count = 0;
        $result = array(
			'points'=>array(),
			'count'=>array()
		);
	  	if($_POST['level'] === '1'){      		  		
           	$stations = StationsScenario::model()->with('stations', 'route')->findAll(array(
    			'condition'=>'route.transport_types_id=:trtypeid',
        		'params'=>array(':trtypeid'=>$_POST['nodeid']),
        		'order' => 't.id')
           	);	
		}
		if($_POST['level'] === '2'){      		  		
           	$stations = StationsScenario::model()->with('stations', 'route')->findAll(array(
				'condition'=>'t.routes_id=:routeid',
				'params'=>array(':routeid'=>$_POST['nodeid']),
				'order' => 't.number')
			);
		}
		if($_POST['level'] === '3'){      		  		
           	$stations = StationsScenario::model()->with('stations', 'route.graphs')->findAll(array(
				'condition'=>'graphs.id=:grid',
				'params'=>array(':grid'=>$_POST['nodeid']),
				'order' => 't.number')
			);
		}   	     		  	
        foreach($stations as $stop){
        	if(!isset($stop->stations->id)){
				echo $stop->id; echo ",";                     			
        	}
			$result['points'][] = array(
				'id'	=> $stop->stations->id,
				'name'	=> $stop->stations->name,
				'lt'	=> (double) (floor($stop->stations->latitude/100)*100+(($stop->stations->latitude   - floor($stop->stations->latitude/100)*100)*100/60))/100,
				'ln' 	=> (double) (floor($stop->stations->longitude/100)*100+(($stop->stations->longitude - floor($stop->stations->longitude/100)*100)*100/60))/100,
				'r'		=> $stop->route->name,
			);
            $count++;
        }
        $result['count'] = $count;
        echo json_encode($result);
	} 	   
 	   public function actionRoutes()
   	   {
				if($_POST['level'] === '1'){
      		  		$routes = Route::model()->with('stops_scenario.stops')->findAll(array(
                   					'condition'=>'stops.id=:stopid',
                   					'params'=>array(':stopid'=>$_POST['znak_id']),
                   					'order' => 't.id'));
                   					$str = '';
                   		foreach($routes as $route){
                   		 	$str .= $route->name." ";
                   		}			
                   		echo $str;
				}   	   	
		}
	public function actionPointsControl(){
		$count = 0;
		$result = array(
			'points'=>array(),
			'count'=>array()
		);
		if (isset($_POST)){
		  	if($_POST['level'] === '1'){
   				$pointscontrols = PointsOfEvents::model()->with('stations_scenario.route')->findAll(array(
      				'condition'=>'route.transport_types_id=:trtypeid and stations_scenario.pc_status = "yes"',
           			'params'=>array(':trtypeid'=>$_POST['nodeid']),
   					'order' => 't.id'));
			}
            if($_POST['level'] === '2'){
  				$pointscontrols = PointsOfEvents::model()->with('stations_scenario.route')->findAll(array(
           			'condition'=>'route.id=:routid and stations_scenario.pc_status = "yes"',
           			'params'=>array(':routid'=>$_POST['nodeid']),
   					'order' => 't.id'));
            }
            if($_POST['level'] === '3'){
  				$pointscontrols = PointsOfEvents::model()->with('stations_scenario.route.graphs')->findAll(array(
           			'condition'=>'graphs.id=:grid and stations_scenario.pc_status = "yes"',
           			'params'=>array(':grid'=>$_POST['nodeid']),
   					'order' => 't.id'));
            }                        
			foreach($pointscontrols as $pointscontrol){
				$result['points'][] = array(
					'id'	=> $pointscontrol->id,
					'name'	=> $pointscontrol->name,
					'lt'	=> (double) (floor($pointscontrol->latitude/100)*100+(($pointscontrol->latitude   - floor($pointscontrol->latitude/100)*100)*100/60))/100,
					'ln' 	=> (double) (floor($pointscontrol->longitude/100)*100+(($pointscontrol->longitude - floor($pointscontrol->longitude/100)*100)*100/60))/100,
				);
                $count++;
            }
        }    	
		$result['count'] = $count;
		echo json_encode($result);
	}

		public function actionRPointsDirection(){

			$result = array('items' => array());

			if (isset($_POST['nids'])){
				foreach ($_POST['nids'] as $nodeid) {

					$simple = array(
						'points' =>array(),
						'nodeid' => $nodeid
					);

					$pointsdirections = RouteWaypoints::model()->with('route')->findAll(array(
                   		'condition'=>'t.routes_id=:routid',
                   		'params'=>array(':routid'=>$nodeid),
           				'order' => 't.number'));

	           			foreach($pointsdirections as $pointsdirection){
						$simple['points'][] = array(
							'id' => $pointsdirection->number,										
							'latitude'	=> $pointsdirection->latitude,
							'longitude' => $pointsdirection->longitude
						);
		            }
		            $result['items'][] = $simple;
				}
			}
			
		    echo json_encode($result);
		}

		public function actionPointsDirection(){
		                   $count = 0;
		                   $result = array(
											'points'=>array(),
											'count'=>array()
						   );
                  
				      if (isset($_POST)){
				      	if($_POST['level'] === '1'){
		                      $result['count'] = $count;
		                      echo json_encode($result);
		                      exit;
				      	}
                        if($_POST['level'] === '2'){
          					$pointsdirections = RouteWaypoints::model()->with('route')->findAll(array(
                   					'condition'=>'t.routes_id=:routid',
                   					'params'=>array(':routid'=>$_POST['nodeid']),
           							'order' => 't.number'));
                        }
                        if($_POST['level'] === '3'){
          					$pointsdirections = RouteWaypoints::model()->with('route.graphs')->findAll(array(
                   					'condition'=>'graphs.id=:grid',
                   					'params'=>array(':grid'=>$_POST['nodeid']),
           							'order' => 't.number'));
                        }

		                      foreach($pointsdirections as $pointsdirection){

									$result['points'][] = array(
										'id'				=> $pointsdirection->number,										
										'latitude'	 		=> $pointsdirection->latitude,
										'longitude' 		=> $pointsdirection->longitude
									);
		                            $count++;

		                      }
                      }			       	
		                      $result['count'] = $count;
		                      echo json_encode($result);
		}		
	public function actionAdvertisement(){
		$count = 0;
       	$result = array(
			'points'=>array(),
			'count'=>array()
	   	);
	   	if(Yii::app()->user->name != "guest"){
	   		$carrier = Yii::app()->user->checkUser(Yii::app()->user);
	       	if (isset($_POST)){
	       		if ($carrier){
	       			if($_POST['level'] === '1'){
		     			$advertisements   = Advertisement::model()->with('advertisement_scenario.route')->findAll(array(
   		     		    	'condition'=>'t.carriers_id=:carrid and route.transport_types_id=:trtypeid',
	                   		'params'=>array(':carrid'=>$carrier['carrier_id'],':trtypeid'=>$_POST['nodeid']),
			     		    'order' => 't.id'));
			     	}
			     	if($_POST['level'] === '2'){
	     		        $advertisements   = Advertisement::model()->with('advertisement_scenario.route')->findAll(array(
		     		    	'condition'=>'t.carriers_id=:carrid and advertisement_scenario.routes_id=:routid',
                   			'params'=>array(':carrid'=>$carrier['carrier_id'],':routid'=>$_POST['nodeid']),
		     		        'order' => 't.id'));
			     	}
			     	if($_POST['level'] === '3'){
	     		        $advertisements   = Advertisement::model()->with('advertisement_scenario.route.graphs')->findAll(array(
		     		    	'condition'=>'t.carriers_id=:carrid and graphs.id=:grid',
                   			'params'=>array(':carrid'=>$carrier['carrier_id'],':grid'=>$_POST['nodeid']),
		     		        'order' => 't.id'));
			     	}				     		      
		    	}
		     	else{
	     		    $advertisements   = Advertisement::model()->findAll(array('order' => 'id'));
		     	}
                foreach($advertisements as $advertisement){
					$result['points'][] = array(
						'id'	=> $advertisement->id,
						'name'	=> $advertisement->name,
						'lt'	=> (double) (floor($advertisement->latitude/100)*100+(($advertisement->latitude   - floor($advertisement->latitude/100)*100)*100/60))/100,
						'ln' 	=> (double) (floor($advertisement->longitude/100)*100+(($advertisement->longitude - floor($advertisement->longitude/100)*100)*100/60))/100,
					);
                    $count++;
                }
		    }
	    	$result['count'] = $count;
	        echo json_encode($result);		                 
	    }
	    else{     	
	    	echo "noaccess";
	    }
	}
  	   public function actionRouteDirection(){
  	   					if (isset($_POST)){ 
  	   						if($_POST['level'] === '2'){  	   		
  	   							RouteWaypoints::model()->deleteAll(array(
	  	   							'condition'=>'routes_id=:routid',
                   					'params'=>array(':routid'=>$_POST['nodeid'])));					 	   							  	   							
	  	   						$arr = CJSON::decode($_POST['data']);
	  	   						foreach	($arr as $ar){	
	  	   							$pointsdirection = new RouteWaypoints;
	  	   							$pointsdirection->routes_id = $_POST['nodeid'];							  	   							
	  	   							while (list($key, $value) = each($ar)){  	   									   									
											$pointsdirection->$key=$value;															
									}
									$pointsdirection->save();
								}
							}
  	   						
  	   					}
  	   }
  	   public function actionRouteDirectionUpdate(){
  	   					if (isset($_POST)){ 
  	   						if($_POST['level'] === '2'){  	   		
  	   							$count = RouteWaypoints::model()->count(array(
	  	   							'condition'=>'routes_id=:routid',
                   					'params'=>array(':routid'=>$_POST['nodeid'])));
                   				if ($count){		  	   							
		  	   						$arr = CJSON::decode($_POST['data']);
		  	   						foreach	($arr as $ar){	
		  	   							$pointsdirection = new RouteWaypoints;
		  	   							$pointsdirection->routes_id = $_POST['nodeid'];							  	   							
		  	   							while (list($key, $value) = each($ar)){
		  	   									if($key == 'number'){
		  	   										$value = $value+$count;	  	   										
		  	   									}  	   									   									
												$pointsdirection->$key=$value;															
										}
										$pointsdirection->save();
									}
									echo json_encode('success:true');
								}
								else{
									echo json_encode('nodata');
								}
							}
  	   						
  	   					}
  	   }  	   
  	   public function actionRouteShow(){
  	   					if (isset($_GET)){ 
  	   						if($_GET['level'] === '2'){  	   		
  	   							$points_direction = PointsDirection::model()->findAll(array(
  	   								'select'=>'number,latitude,longitude',
	  	   							'condition'=>'routes_id=:routid',
                   					'params'=>array(':routid'=>$_GET['nodeid']),
                   					'order' => 'id'));
                   					$res =  CJSON::encode(array(
		                        		'success'=>true,
		                        		'data'=>$points_direction
		                        	));
		                        echo $res;	   							  	   								  	   							  	   						
							}
  	   						
  	   					}
  	   }
  	    public function actionRouteErase(){
  	   					if (isset($_POST)){ 
  	   						if($_POST['level'] === '2'){  	   		
  	   							PointsDirection::model()->deleteAll(array(
	  	   							'condition'=>'routes_id=:routid',
                   					'params'=>array(':routid'=>$_POST['nodeid'])));					 	   							  	   							
							}
  	   						
  	   					}
  	   }
		public function actionDirectionFromRoutesName(){
			$this->writeLog = false;
			$count = 0;
			$result = array(
				'points'=>array(),
				'count'=>array()
			);                  
			if (isset($_GET)){   	
				$pointsdirections = RouteWaypoints::model()->with(array(
					'route'=>array(
						'select'=>false,
						'condition'=>'route.name=:routname',
						'params'=>array(':routname'=>$_GET['routename']),
					)))
					->findAll(array(                   					
					'order' => 't.number'
				));
			    foreach($pointsdirections as $pointsdirection){
					$result['points'][] = array(
						'id'		=> $pointsdirection->number,										
						'latitude'	=> $pointsdirection->latitude,
						'longitude' => $pointsdirection->longitude
					);
		            $count++;
			    }
			}			       	
			$result['count'] = $count;
			echo json_encode($result);
		}
    public function actionGetFewRouteLocations(){
        $moveonmaps = MoveOnMap::model()->with(array(
            'bort'=>array(
                'select'=>'number,state_number,special_needs',
                'condition'=>'connection = "yes" ',
            ),
            'graph'=>array(
                'select'=>'name',
            ),
            'bort.park.carrier'=>array(
                'select'=>'name',
            ),
            'route'=>array(
                'select'=>'name',
            )))
            ->findAll(array(
                    'select'=>'id, latitude, longitude, speed, direction, datatime,time_difference',
                    'condition'=>'date(t.datatime) = CURRENT_DATE() and t.routes_id IN '.$_GET['nodes_id_list'],
                    //'params'=>array(':rtid'=>$r_id),
                    'order' => 't.routes_id')
            );

        $count = 0;
        $result = array(
            'mark'=>array(),
            'count'=>array()
        );
        foreach($moveonmaps as $moveonmap){
            if (isset($moveonmap->route->name)){
                $routename = $moveonmap->route->name;
                $graph = $moveonmap->graph->name;
            }
            else{
                $routename = "100";
                $graph = 1;
            }
            /*if (isset($moveonmap->time_difference)){
				$timediff	= $moveonmap->time_difference;
			}
			else{
				$timediff	= 'немає в графіку';
			}*/
            $result['mark'][] = array(
                'id'		=> $moveonmap->id,
                'n'			=> $moveonmap->bort->number,
                'sn'		=> $moveonmap->bort->state_number,
                'sb'		=> $moveonmap->bort->special_needs,
                'car'		=> $moveonmap->bort->park->carrier->name,
                'lt'	 	=> round((double) (floor($moveonmap->latitude/100)*100+(($moveonmap->latitude   - floor($moveonmap->latitude/100)*100)*100/60))/100,6),
                'ln' 		=> round((double) (floor($moveonmap->longitude/100)*100+(($moveonmap->longitude - floor($moveonmap->longitude/100)*100)*100/60))/100,6),
                //'latitude_ukr'  	=> $moveonmap->latitude,
                //'longitude_ukr' 	=> $moveonmap->longitude,
                's'			=> $moveonmap->speed,
                'c'			=> $moveonmap->direction,
                'd' 		=> $moveonmap->datatime,
                'r'			=> $routename,
                //'img'		=> $routename,
                'g'     	=> (int)$graph,
                //'e'		=> 'трек',
                'td'		=> $moveonmap->time_difference
            );
            $count++;
        }
        $result['count'] = $count;
        echo json_encode($result);

    }
}
?>
