<?php

class OrdersController extends Controller
{
	public function actionOrders(){
      	if (isset($_POST)){
		    if($_POST['level'] === '1'){
                $sql = Orders::model()->findAll(array('order' => 'id'));
                $res =  CJSON::encode(array(
	                'success'=>true,
	                'data'=>$sql
                ));
                echo $res;
		    }
      	}
	}
	public function actionDelete($id){
		$orders=Orders::model()->find(array(
			'condition'=>'t.from >= :today and :today <= t.to and t.id=:tid',
			'params'=>array('today' => date('Y-m-d'),'tid'=> $id)
		));
        $orders->delete();
		$res =  CJSON::encode(array(
            'success'=>true
        ));
        echo $res;
	}
    public function actionRead(){
        $carrier = Yii::app()->user->checkUser(Yii::app()->user);			 					
		$result = array(
			'success'=>true,
			'data'=>array(),
		);
        if($_GET['level'] === '1'){
        	if($carrier['id']){
                $orders = Orders::model()->with('bort','bort.model','bort.park','status','graph')->findAll(array(
  					'condition'=>'model.transport_types_id=:trtypeid and t.from <= :today and :today <= t.to
  					            and park.carriers_id=:carid',
   					'params'=>array(':trtypeid'=>$_GET['nodeid'], 'today' => $_GET['date'],':carid'=>
                        $carrier['carrier_id']),
                    'order' => 't.id'));
                $rt_gr_list = Graphs::model()->with('route')->findAll(array(
  					'condition'=>'route.transport_types_id=:trtypeid and route.status = "yes" and t.status = "yes"
  					            and route.carriers_id=:carid',
   					'params'=>array(':trtypeid'=>$_GET['nodeid'],':carid'=>$carrier['carrier_id']),
                    'order' => 'cast(route.name AS DECIMAL),cast(t.name AS DECIMAL)'));                
            }
            else{
                $orders = Orders::model()->with('bort','bort.model','status','graph')->findAll(array(
  					'condition'=>'model.transport_types_id=:trtypeid and t.from <= :today and :today <= t.to',
   					'params'=>array(':trtypeid'=>$_GET['nodeid'], 'today' => $_GET['date']),
                    'order' => 't.id'));		                	
                $rt_gr_list = Graphs::model()->with('route')->findAll(array(
  					'condition'=>'route.transport_types_id=:trtypeid and route.status = "yes" and t.status = "yes"',
   					'params'=>array(':trtypeid'=>$_GET['nodeid']),
                    'order' => 'cast(route.name AS DECIMAL),cast(t.name AS DECIMAL)'));
            }
        }
        if($_GET['level'] === '2'){
            $orders = Orders::model()->with('bort','status','graph')->findAll(array(
				'condition'=>'graph.routes_id=:grrouteid and t.from <= :today and :today <= t.to',
				'params'=>array(':grrouteid'=>$_GET['nodeid'], 'today' => $_GET['date']),
                'order' => 't.id'));
            $rt_gr_list = Graphs::model()->with('route')->findAll(array(
				'condition'=>'routes_id=:grrouteid and route.status = "yes"',
				'params'=>array(':grrouteid'=>$_GET['nodeid']),
                'order' => 'cast(t.name AS DECIMAL)'));            
        }
        if($_GET['level'] === '3'){
            $orders = Orders::model()->with('bort','status','graph')->findAll(array(
				'condition'=>'t.graphs_id=:grid',
				'params'=>array(':grid'=>$_GET['nodeid']),
                'order' => 't.id DESC'));
			foreach($orders as $order){
				$result['data'][] = array(
					'id'				=> $order->id,
					'park'				=> $order->bort->park->name,
					'state_number'		=> $order->bort->state_number,
					'number'			=> $order->bort->number,
					'from'	 			=> $order->from,
					'to'	 			=> $order->to,
					'route'	 			=> $order->graph->route->name,
					'graph'	 			=> $order->graph->name,
					'created'	 		=> $order->created,
					'status'	 		=> $order->status->name,
					//'bort_status_id' 	=> $order->bort_status_id					
				);
			}     
      
        }
        if($_GET['level'] != '3'){
	$gr_id = false;
			foreach($rt_gr_list as $rgl){
				foreach($orders as $order){
					if ($rgl->id == $order->graph->id){
						$result['data'][] = array(
							'id'				=> $order->id,
							'park'				=> $order->bort->park->name,
							'state_number'		=> $order->bort->state_number,
							'number'			=> $order->bort->number,
							'from'	 			=> $order->from,
							'to'	 			=> $order->to,
							'route'	 			=> $rgl->route->name,
							'graph'	 			=> $rgl->name,
							'created'	 		=> $order->created,
							'status'	 		=> $order->status->name,
							//'bort_status_id'	=> $order->bort_status_id
						);
						$gr_id = $rgl->id;
					}
				}
				if(!$gr_id){
					$result['data'][] = array(
						'route'	 			=> $rgl->route->name,
						'graph'	 			=> $rgl->name,
					);
				}
				$gr_id = 0;
			}
			foreach($orders as $order){
				foreach($rt_gr_list as $rgl){
					if ($order->graph->id == $rgl->id){
						$gr_id = $rgl->id;
					}
				}
				if(!$gr_id){
					$result['data'][] = array(
						'id'				=> $order->id,
						'park'				=> $order->bort->park->name,
						'state_number'		=> $order->bort->state_number,
						'number'			=> $order->bort->number,
						'from'	 			=> $order->from,
						'to'	 			=> $order->to,
						'route'	 			=> $order->graph->route->name,
						'graph'	 			=> $order->graph->name,
						'created'	 		=> $order->created,
						'status'	 		=> $order->status->name,
						//'bort_status_id'	=> $order->bort_statuses_id
					);				
				}
				$gr_id = 0;
			}        
		}
        echo json_encode($result);
	}

    public function actionUpdate(){
		$data = json_decode(Yii::app()->request->getPut('data'),true);
		#if(Yii::app()->request->getPut('level') === '3'){
            if(isset($data['from']) or isset($data['to'])){
				$put=Orders::model()->findByPk($data['id']);
				$put->from = $data['from'];
				$put->to = $data['to'];
				$put->save();
            }
            if(isset($data['number'])){
	   			$borts = Borts::model()->find(array(
                    'condition'=>'t.number = :number',
					'params'=>array(':number' => $data['number'])
				));
				$put=Orders::model()->findByPk($data['id']);
				$put->borts_id = $borts->id;
				$put->save();
            }
		#}
        $res =  CJSON::encode(array(
        	'success'=>'true',
        	'data'=>$data
        ));
        echo $res;
	}
	public function actionCreate(){
		$data = json_decode(Yii::app()->request->getPost('data'),true);
        $answer = array(
            'success'=>false
        );
   		$bort = Borts::model()->getBortByNumber($data['number']);
        $route = Route::model()->getRouteByName($data['route']);
        $graph = Graphs::model()->getGraphByRouteIdAndGraphName($route->id,$data['graph']);

        $orderGraph = Orders::model()->checkOrderForGraphToday($graph->id,$data['from'],$data['to']);
        $orderBort = Orders::model()->checkOrderForBortToday($bort->id,$data['from'],$data['to']);

        if($orderGraph){
            $bort = Borts::model()->getBortByNumber($orderGraph->borts_id);
            $answer['message'] = "Дублювання графіка! Внесено для ТЗ ".$bort->state_number."-".$bort->number;
        }
        else if($orderBort){
            $graph = Graphs::model()->getGraphsById($orderBort->graphs_id);
            $route = Route::model()->getRouteById($graph->routes_id);
            $answer['message'] = "Дублювання борту! ТЗ внесено для маршрута №".$route->name." та графіка №".$graph->name;
        }
        else {
            if ($_POST['level'] === '3') {
                $order = new Orders;
                $order->borts_id = $bort->id;
                $order->graphs_id = $_POST['nodeid'];
                $order->created = date('Y-m-d G:i:s');
                $order->from = $data['from'];
                $order->to = $data['to'];
                $order->order_statuses_id = 1;
                //$order->bort_statuses_id = $data['bort_status_id'];
                $order->save();
            }
            if ($_POST['level'] === '2') {
                $gr = Graphs::model()->find(array(
                    'select' => 'id',
                    'condition' => 'name=:tnm and routes_id=:rtid',
                    'params' => array(':tnm' => $data['graph'], ':rtid' => $_POST['nodeid'])
                ));
                $order = new Orders;
                $order->borts_id = $bort->id;
                $order->graphs_id = $gr->id;
                $order->created = date('Y-m-d G:i:s');
                $order->from = $data['from'];
                $order->to = $data['to'];
                $order->order_statuses_id = 1;
                //$order->bort_statuses_id = $data['bort_status_id'];
                $order->save();
            }
            if ($_POST['level'] === '1') {
                $rt = Route::model()->find(array(
                    'select' => 'id',
                    'condition' => 'name=:tnm and transport_types_id=:trid',
                    'params' => array(':tnm' => $data['route'], ':trid' => $_POST['nodeid'])
                ));
                $gr = Graphs::model()->find(array(
                    'select' => 'id',
                    'condition' => 'name=:tnm and routes_id=:rtid',
                    'params' => array(':tnm' => $data['graph'], ':rtid' => $rt->id)
                ));
                $order = new Orders;
                $order->borts_id = $bort->id;
                $order->graphs_id = $gr->id;
                $order->created = date('Y-m-d G:i:s');
                $order->from = $data['from'];
                $order->to = $data['to'];
                $order->order_statuses_id = 1;
                //$order->bort_statuses_id = $data['bort_status_id'];
                $order->save();
            }
            $data['id'] = $order->id;
            $answer = array(
                'success'=>true,
                'message'=>'Створено запис',
                'data'=>$data
            );
        }
        echo CJSON::encode($answer);
	}
	public function actionBorts(){
		$parksArray = array();
		$carrier = Yii::app()->user->checkUser(Yii::app()->user);
		if($carrier['id']){
        $borts = Borts::model()->with('model','park')->findAll(array(
            'condition'=>'park.carriers_id=:carid and status = "yes"',
			'params'=>array(':carid'=>$carrier['carrier_id']),
        	'order' => 'cast(t.number AS DECIMAL)'));
	    }	
// SS umova		                        
        else{
        	if($_GET['level'] === '1'){
        	$borts = Borts::model()->with('model','park')->findAll(array(
                'condition'=>'status = "yes"',	    								
            	'order' => 'cast(t.number AS DECIMAL)'));
        	}
        	if($_GET['level'] === '2'){
        	$graphs = Graphs::model()->findAll(array(
        		'condition'=>'status = "yes" and routes_id=:routid',
        		'params'=>array(':routid'=>$_GET['nodeid'])));
        		//'group'=>'carriers_id'));
        	foreach($graphs as $graph){
try{
        		$parksArray[] = Parks::model()->find('carriers_id=:carID', array(':carID'=>$graph->carriers_id))->id;
}catch (Exception $e) {
 echo json_encode($e);
return null;
}
        	}
        	$parksList = "(".implode(",",$parksArray).")";

        	$borts = Borts::model()->with('model','park','park.carrier.route.graphs')->findAll(array(
                'condition'=>'t.status = "yes"',
                //'params'=>array(':grid'=>$_POST['nodeid']),	    								
            	'order' => 'cast(t.number AS DECIMAL)'));
        	} 
        	if($_GET['level'] === '3'){
        	$borts = Borts::model()->with('model','park','park.carrier.route.graphs')->findAll(array(
                'condition'=>'t.status = "yes"',
                //'params'=>array(':grid'=>$_POST['nodeid']),	    								
            	'order' => 'cast(t.number AS DECIMAL)'));
        	}                       	 
        }
		$result = array(
			'success'=>true,
			'data'=>array(),
		);
        foreach($borts as $bort){
        	$num = $bort->state_number." - ".$bort->number;
			$result['data'][] = array(
				'id'			=> $bort->id,
				'number'		=> $bort->number,
				'num_gov_num'	=> $num,
			);
        }
        echo json_encode($result);
	}
	public function actionBortStatuses(){
    	$bortst = BortStatuses::model()->findAll();  	                   	 
		$result = array(
			'success'=>true,
			'data'=>array(),
		);
        foreach($bortst as $bst){        	
			$result['data'][] = array(
				'bort_status_id'	=> $bst->id,
				'bort_status_name'	=> $bst->name
			);
        }
        echo json_encode($result);
	}	
	public function actionStatus(){
		$carrier = Yii::app()->user->checkUser(Yii::app()->user);
	    $result = array(
			'success'=>true,
			'data'=>array(),
		);					 											
        if($_GET['level'] === '1'){
        	if($carrier['id']){
		        $borts = Borts::model()->with('park','ip','moveonmap','moveonschedule')->findAll(array(
	                'condition'=>'park.carriers_id=:carid and status = "yes"',
					'params'=>array(':carid'=>$carrier['carrier_id'])
                ));
	        }
	        else{
		        $borts = Borts::model()->with('park','ip','moveonmap','moveonschedule')->findAll(array(
	                'condition'=>'status = "yes"',		    								
                    /*'order' => 'order.created DESC'*/));
               // echo $borts[0]->park->carrier->nick;        	
               // exit;        						        	
	        }                
        }
		if($_GET['level'] === '2'){
        	if($carrier['id']){
		        $borts = Borts::model()->with('park','ip','moveonmap','moveonschedule')->findAll(array(
	                'condition'=>'park.carriers_id=:carid and status = "yes" and moveonmap.routes_id=:rtid',
					'params'=>array(':carid'=>$carrier['carrier_id'],':rtid'=>$_GET['nodeid'])
                ));
	        }
	        else{
		        $borts = Borts::model()->with('park','ip','moveonmap','moveonschedule')->findAll(array(
	                'condition'=>'status = "yes" and moveonmap.routes_id=:rtid',
	                'params'=>array(':rtid'=>$_GET['nodeid'])		    								
                    /*'order' => 'order.created DESC'*/));
               // echo $borts[0]->park->carrier->nick;     						        	
	        }                
        }        
		foreach($borts as $bort){        
			if($bort->moveonmap){
				$moveonmap	= $bort->moveonmap->datatime;
				$route	 	= $bort->moveonmap->route->name;
				$graph	 	= $bort->moveonmap->graph->name;				
			}
			else{
				$moveonmap	= Yii::app()->session['NoData'];
				$route	 	= 0;
				$graph	 	= 0;					
			}
			if($bort->moveonschedule){
				$moveonschedule	 = $bort->moveonschedule->datatime;
			}
			else{
				$moveonschedule	 = Yii::app()->session['NoData'];	
			}
			if($bort->ip){
				$ip	 = $bort->ip->ip;
				$last_mds = $bort->ip->data;
                if (isset($bort->ip->sim_serial_number)){
                    $sn =  $bort->ip->sim_serial_number;
                }

			}
			else{
				$ip	 = Yii::app()->session['NoData'];
				$last_mds	 = Yii::app()->session['NoData'];
                $sn	= Yii::app()->session['NoData'];
			}			        
       
			$result['data'][] = array(
				'park'					=> $bort->park->name,
				'bort_id'				=> $bort->id,
				'number'				=> $bort->number,
				'st_number'				=> $bort->state_number,
				'charge'				=> $bort->moveonmap->charge,
				'route'	 				=> $route,
				'graph'	 				=> $graph,			
				//'datatime'	 		=> $datatime,
				//'status'	 		=> $status,
				'moneonmap'	 			=> $moveonmap,
				'moveonschedule'		=> $moveonschedule,
				'ip'					=> $ip,
                'sn'				    => $sn,
				'last_modem_data_send' 	=> $last_mds,
				'system_version'		=> $bort->ip->system_version
			);
		}
    	echo json_encode($result);    
	}
	public function actionReboot(){
        $bort_arr = explode(',',$_POST['bort']);
        for ($i=0; $i<count($bort_arr); $i++){
            $orders = Orders::model()->findAll(array(
                'condition'=>':orderid = t.id and t.from <= CURRENT_DATE and CURRENT_DATE <= t.to',
                'params'=>array(':orderid'=>$bort_arr[$i])
            ));
            OrdersLoad::model()->deleteAll(array(
                'condition'=>':bortid = borts_id',
                'params'=>array(':bortid'=>$orders[0]->bort->id)
            ));
            foreach($orders as $order) {
                $orderload = new OrdersLoad;
                $orderload->borts_id = $order->borts_id;
                $schedules_types_id = OrdersLoad::model()->SheduleTypeId();
                if ($order->graph->id == 0) {
                    $orderload->schedules_id = 0;
                    $orderload->number = $order->bort->number;
                    $orderload->route_num = 0;
                    $orderload->graph_num = 0;
                    $orderload->route_type = 0;
                    $orderload->reboot = 1;
                    $orderload->orders_id = $order->id;
                    $orderload->save();
                    // status change
                    $order->order_statuses_id = 1;
                    $order->save();
                } else {
                    $graphs = Graphs::model()->findByPk($order->graph->id);
                    $schedules = $graphs->getCurrentSchedule($schedules_types_id);
                    if ($schedules) {
                        $orderload->schedules_id = $schedules->id;
                        $orderload->number = $order->bort->number;
                        $orderload->route_num = $order->graph->route->name;
                        $orderload->graph_num = $order->graph->name;
                        $orderload->route_type = $order->graph->route->routemovemethod->name;
                        $orderload->reboot = 1;
                        $orderload->orders_id = $order->id;
                        $orderload->save();
                        // status change
                        $order->order_statuses_id = 1;
                        $order->save();
                    }
                }
            }
	    }
	}
  	public function actionDayOrder(){
		if($_POST['level'] === '1'){
  			$carrier = Yii::app()->user->checkUser(Yii::app()->user);

        	if($carrier['id']){
                $orders = Orders::model()->with('bort','bort.model','bort.park','status','graph')->findAll(array(
  					'condition'=>'model.transport_types_id=:trtypeid and t.from <= :today and :today <= t.to and park.carriers_id=:carid',
   					'params'=>array(':trtypeid'=>$_POST['nodeid'], 'today' => $_POST['date'],':carid'=>$carrier['carrier_id']),
                    'order' => 't.id'));
                $rt_gr_list = Graphs::model()->with('route')->findAll(array(
  					'condition'=>'route.transport_types_id=:trtypeid and route.status = "yes" and route.carriers_id=:carid',
   					'params'=>array(':trtypeid'=>$_POST['nodeid'],':carid'=>$carrier['carrier_id']),
                    'order' => 'cast(route.name AS DECIMAL),cast(t.name AS DECIMAL)'));                
            }
            else{
                $orders = Orders::model()->with('bort','bort.model','status','graph')->findAll(array(
  					'condition'=>'model.transport_types_id=:trtypeid and t.from <= :today and :today <= t.to',
   					'params'=>array(':trtypeid'=>$_POST['nodeid'], 'today' => $_POST['date']),
                    'order' => 't.id'));		                	
                $rt_gr_list = Graphs::model()->with('route')->findAll(array(
  					'condition'=>'route.transport_types_id=:trtypeid and route.status = "yes"',
   					'params'=>array(':trtypeid'=>$_POST['nodeid']),
                    'order' => 'cast(route.name AS DECIMAL),cast(t.name AS DECIMAL)'));
            }
            OrdersLoad::model()->deleteAll();
        }
		if($_POST['level'] === '2'){
            $orders = Orders::model()->with('bort','status','graph')->findAll(array(
				'condition'=>'graph.routes_id=:grrouteid and t.from <= :today and :today <= t.to',
				'params'=>array(':grrouteid'=>$_POST['nodeid'], 'today' => $_POST['date']),
                'order' => 't.id'));
            $rt_gr_list = Graphs::model()->with('route')->findAll(array(
				'condition'=>'routes_id=:grrouteid and route.status = "yes"',
				'params'=>array(':grrouteid'=>$_POST['nodeid']),
                'order' => 'cast(t.name AS DECIMAL)'));
            $route = Route::model()->findByPk($_POST['nodeid']);
			OrdersLoad::model()->with('route')->deleteAll(array(
				'condition'=>'route_num=:rtname',
				'params'=>array(':rtname'=>$route->name)
            ));                            
        }
		foreach($rt_gr_list as $rgl){
			foreach($orders as $order){
				if ($rgl->id == $order->graph->id){
					$orderload = new OrdersLoad;
					$orderload->borts_id = $order->borts_id;
	  			 	$schedules_types_id = OrdersLoad::model()->SheduleTypeId();	  			 	
  			 		$graphs = Graphs::model()->findByPk($order->graph->id); 			 		
   			 		$schedules = $graphs->getCurrentSchedule($schedules_types_id);
	              	if($schedules){
						$orderload->schedules_id = $schedules->id;
						$orderload->number =  $order->bort->number;
						$orderload->route_num = $order->graph->route->name;
						$orderload->graph_num = $order->graph->name;
						$orderload->route_type = $order->graph->route->routemovemethod->name;
						$orderload->reboot = 0;
						$orderload->orders_id = $order->id;
						$orderload->save();
					}				
					$gr_id = $rgl->id;
					//echo $order->graph->route->name." - ".$order->graph->name." - ".$order->bort->number."\n";					
				}
				if($gr_id){
					$gr_id = 0;
					break;
				}			
			}	
		}
   	}
   	public function actionRouteGraph(){
   		$carrier = Yii::app()->user->carrier; 			 		   			 		   
		$result = array(
			'success'=>true,
			'data'=>array(),
		);
		if($_POST['level'] === '1'){	            			
			if($carrier['id']){                        
                $routes = Route::model()->findAll(array(
  					'condition'=>'transport_types_id=:trtypeid and carriers_id =:carid',
   					'params'=>array(':trtypeid'=>$_POST['nodeid'],':carid'=>$carrier['id']),
                    'order' => 'cast(t.name AS DECIMAL)'
                )); 
            }
// SS umova				              
            else{
                $routes = Route::model()->findAll(array(
  					'condition'=>'transport_types_id=:trtypeid',
   					'params'=>array(':trtypeid'=>$_POST['nodeid']),
                    'order' => 'cast(t.name AS DECIMAL)'
                ));				                	
            }  
	    }
	    if($_POST['level'] === '2'){
			if($carrier['id']){                        
                $routes = Route::model()->findAll(array(
  					'condition'=>'t.id=:rtid and carriers_id =:carid',
   					'params'=>array(':rtid'=>$_POST['nodeid'],':carid'=>$carrier['id']),
                    'order' => 'cast(t.name AS DECIMAL)'
                )); 
            }
// SS umova				              
            else{
                $routes = Route::model()->findAll(array(
  					'condition'=>'t.id=:rtid',
   					'params'=>array(':rtid'=>$_POST['nodeid']),
                    'order' => 'cast(t.name AS DECIMAL)'
                ));				                	
            }
	    }                                         
		foreach($routes as $route){
			$result['data'][] = array(
				'nodeid'			=> $route->id,
				'text'				=> $route->name								
			);								
		}								
		echo json_encode($result);	   			    			 	
   	}
   	public function actionGraph(){   			    			 	
		$result = array(
			'success'=>true,
			'data'=>array(),
		); 
		if($_POST['level'] === '1'){
            $graphs = Graphs::model()->with('route')->findAll(array(
				'condition'=>'transport_types_id=:trtypeid and route.name=:routename',
				'params'=>array(':trtypeid'=>$_POST['nodeid'],':routename'=>$_POST['routename']),
                'order' => 'cast(t.name AS DECIMAL)'));                                     
        }
        if($_POST['level'] === '2'){
            $graphs = Graphs::model()->findAll(array(
				'condition'=>'t.routes_id=:rtid',
				'params'=>array(':rtid'=>$_POST['nodeid']),
                'order' => 'cast(t.name AS DECIMAL)'));
        }
		foreach($graphs as $graph){
			$result['data'][] = array(
				'nodeid'	=> $graph->id,
				'text'		=> $graph->name								
			);								
		}								
		echo json_encode($result);				
   	}
   	public function actionUpOrder(){
   		$carrier = Yii::app()->user->carrier->id;
        $answer = array(
            'success'=>'false'
        );
   		list ($gn,$_POST['number']) = explode(" - ", $_POST['num_gov_num']);

        $bort = Borts::model()->getBortByNumber($_POST['number']);
        $route = Route::model()->getRouteByName($_POST['route']);
        $graph = Graphs::model()->getGraphByRouteIdAndGraphName($route->id,$_POST['graph']);

        $orderGraph = Orders::model()->checkOrderForGraphToday($graph->id,$_POST['from'],$_POST['to']);
        $orderBort = Orders::model()->checkOrderForBortToday($bort->id,$_POST['from'],$_POST['to']);

        if($orderGraph){
            $bort = Borts::model()->getBortById($orderGraph->borts_id);
            $answer['message'] = "Дублювання графіка! Внесено для ТЗ ".$bort->state_number."-".$bort->number;
        }
        else if($orderBort){
            $graph = Graphs::model()->getGraphsById($orderBort->graphs_id);
            $route = Route::model()->getRouteById($graph->routes_id);
            $answer['message'] = "Дублювання борту! ТЗ внесено для маршрута №".$route->name." та графіка №".$graph->name;
        }
        else {
            if ($_POST['level'] === '1') {
                $borts = Borts::model()->find(array(
                    'condition' => 't.number = :number',
                    'params' => array(':number' => $_POST['number'])
                ));
                $graphs = Graphs::model()->with('route')->findAll(array(
                    'condition' => 'transport_types_id=:trtypeid and route.name=:routename and t.name=:graphname',
                    'params' => array(':trtypeid' => $_POST['nodeid'], ':routename' => $_POST['route'], ':graphname' => $_POST['graph']),
                    'order' => 't.id'
                ));
            }
            if ($_POST['level'] === '2') {
                $borts = Borts::model()->find(array(
                    'condition' => 't.number = :number',
                    'params' => array(':number' => $_POST['number'])
                ));
                $graphs = Graphs::model()->findAll(array(
                    'condition' => 't.routes_id=:rtid and t.name=:graphname',
                    'params' => array(':rtid' => $_POST['nodeid'], ':graphname' => $_POST['graph']),
                    'order' => 't.id'
                ));
            }
            $order = new Orders;
            $order->borts_id = $borts->id;
            $order->graphs_id = $graphs[0]->id;
            $order->created = date('Y-m-d G:i:s');
            $order->from = $_POST['from'];
            $order->to = $_POST['to'];
            $order->order_statuses_id = 1;
            //$order->bort_statuses_id = $_POST['bort_status_id'];
            $order->save();
            $answer = array(
                'success'=>true,
                'message'=>'Створено запис'
            );
        }
		echo CJSON::encode($answer);
    }	
    public function actionDuplicateOrder(){
		if($_POST['level'] === '1'){
			$carrier = Yii::app()->user->checkUser(Yii::app()->user);
  			if ($carrier['id']){
		    	$orders = Orders::model()->with('bort','bort.park','graph.route')->findAll(array(	    							
					'condition'=>'t.from <=:dt and t.to >=:dt and route.status = "yes" and park.carriers_id =:carid',//'t.to=:dt', t.from <= CURRENT_DATE and CURRENT_DATE <= t.to and
					'params'=>array(':dt'=>$_POST['from_day'],':carid'=>$carrier['carrier_id'])			                        	
	            ));
	        }
	        else{
		    	$orders = Orders::model()->with('bort','graph.route')->findAll(array(	    							
					'condition'=>'t.from <=:dt and t.to >=:dt and route.status = "yes"',//'t.to=:dt', t.from <= CURRENT_DATE and CURRENT_DATE <= t.to and
					'params'=>array(':dt'=>$_POST['from_day'])			                        	
	            ));	        	
	        }
		    foreach($orders as $order){		    				                        
                $new_order = new Orders;
			    $new_order->borts_id = $order->borts_id;
			    $new_order->graphs_id = $order->graphs_id;
			    $new_order->created =date('Y-m-d H:i:s');
			    $new_order->from = $_POST['for_day'];
			    $new_order->to = $_POST['for_day'];
			    $new_order->order_statuses_id = 1;
			    //$new_order->bort_statuses_id = $order->bort_statuses_id;
			    $new_order->save();													
     	  	}
     	}
		if($_POST['level'] === '2'){
			$dub_orders = Orders::model()->with('graph.route')->findAll(array(	    							
				'condition'=>'t.from <=:dt and t.to >=:dt and route.id =:rtid',
				'params'=>array(':dt'=>$_POST['for_day'],':rtid'=>$_POST['nodeid'])			                        	
            ));	
            if(count($dub_orders)){
		        $res =  CJSON::encode(array(
		            'success' => false,
		            'msg'	  => 'Наряд на '.$_POST['for_day'].' для даного маршруту уже дубльований'
		        ));            	
            }
            else{		                   	
		    	$orders = Orders::model()->with('bort','graph.route')->findAll(array(	    							
					'condition'=>'t.from <=:dt and t.to >=:dt and route.id =:rtid',//'t.to=:dt', t.from <= CURRENT_DATE and CURRENT_DATE <= t.to and
					'params'=>array(':dt'=>$_POST['from_day'],':rtid'=>$_POST['nodeid'])			                        	
	            ));
			    foreach($orders as $order){		    				                        
	                $new_order = new Orders;
				    $new_order->borts_id = $order->borts_id;
				    $new_order->graphs_id = $order->graphs_id;
				    $new_order->created =date('Y-m-d H:i:s');
				    $new_order->from = $_POST['for_day'];
				    $new_order->to = $_POST['for_day'];
				    $new_order->order_statuses_id = 1;
				    //$new_order->bort_statuses_id = $order->bort_statuses_id;
				    $new_order->save();													
	     	  	}
				$res =  CJSON::encode(array(
		            'success'=> true,
		            'msg'	=> Yii::app()->session['OrderTo'].$_POST['for_day'].Yii::app()->session['ForThisRouteSuccessfullyDuplicated']
		        ));      	  	
		    }
		   	echo $res;
     	}    	
    }

    public function actionGetNotice(){
        $constTimeDiff = 900;
        $now = new DateTime('now');
        $nowTimestamp = $now->getTimestamp();
        $noticeType = 4;
        $noticeStatus = 1;
        $createNotice = false;
        $todayMoveOnMapList =  MoveOnMap::model()->findAll(array(
            'condition' => 'date(t.datatime) = CURDATE()',
            'order' => 'borts_id'
        ));
        $todayNoticeList = NoticeAllHistory::model()->findAll(array(
            'condition' => 'notice_types =:nt and notice_statuses =:ns and date_from = CURDATE()',
            'params' => array(':nt' => $noticeType, ':ns' => $noticeStatus)
        ));
        $countTodayNoticeList = count($todayNoticeList);

        foreach ($todayMoveOnMapList as $todayMoveOnMapRecord){
            $bortDatetime = new DateTime($todayMoveOnMapRecord->datatime);
            $bortTimestamp = $bortDatetime->getTimestamp();
            $timestampDiff = $nowTimestamp - $bortTimestamp;
            if($timestampDiff > $constTimeDiff){
                $createNotice = true;
            }
            else{
                $createNotice = false;
            }

            if ($createNotice){
                if($countTodayNoticeList == 0){
                    //create notice
                    $notice = new NoticeAllHistory();
                    $notice->notice_types = 4;
                    $notice->date_from = $bortDatetime->format('Y-m-d');
                    $notice->time_from = $bortDatetime->format('H:i:s');
                    $notice->value = $timestampDiff;
                    $notice->borts_id = $todayMoveOnMapRecord->borts_id;
                    $notice->routes_id = $todayMoveOnMapRecord->routes_id;
                    $notice->graphs_id = $todayMoveOnMapRecord->graphs_id;
                    $notice->notice_statuses = 1;
                    $notice->save();
                }
                else {
                    $i = 1;
                    foreach ($todayNoticeList as $todayNoticeRecord) {
                        if ($todayMoveOnMapRecord->borts_id === $todayNoticeRecord->borts_id) {
                            $notice = NoticeAllHistory::model()->find(array(
                                'condition' => 't.borts_id=:bid and date_from= CURDATE() and notice_types =:nt',
                                'params' => array(':bid' => $todayNoticeRecord->borts_id, ':nt' => $noticeType)
                            ));
                            $notice->value = $timestampDiff;
                            $notice->save();
                            // update notice
                            $noticeUpdate = true;
                        }
                        if(($i === $countTodayNoticeList) and !isset($noticeUpdate)) {
                            //create notice
                            $notice = new NoticeAllHistory();
                            $notice->notice_types = 4;
                            $notice->date_from = $bortDatetime->format('Y-m-d');
                            $notice->time_from = $bortDatetime->format('H:i:s');
                            $notice->value = $timestampDiff;
                            $notice->borts_id = $todayMoveOnMapRecord->borts_id;
                            $notice->routes_id = $todayMoveOnMapRecord->routes_id;
                            $notice->graphs_id = $todayMoveOnMapRecord->graphs_id;
                            $notice->notice_statuses = 1;
                            $notice->save();
                            $i=1;
                            $noticeUpdate = false;
                        }
                        $i++;
                    }
                }
            }
            else{
                foreach ($todayNoticeList as $todayNoticeRecord) {
                    if ($todayMoveOnMapRecord->borts_id === $todayNoticeRecord->borts_id) {
                        //close notice
                        $notice = NoticeAllHistory::model()->find(array(
                            'condition' => 't.borts_id=:bid and date_from= CURDATE() and notice_types =:nt',
                            'params' => array(':bid' => $todayNoticeRecord->borts_id, ':nt' => $noticeType)
                        ));
                        $notice->notice_statuses = 3;
                        $notice->date_to = $now->format('Y-m-d');
                        $notice->time_to = $now->format('H:i:s');
                        $notice->save();

                    } else {
                        //nothing do
                    }
                }
            }

        }

        $res = 1;
    }
    /**
     *
     */
    public function actionGetCurrentReport (){
        if( Yii::app()->user->checkAccess('GetCurrentReport') ) {
            $todayOrdersList = Orders::model()->with('bort', 'bort.model', 'status', 'graph', 'bort.moveonmap', 'bort.moveonschedule')->findAll(array(
                'condition' => 'model.transport_types_id=:trtypeid and t.from <= CURDATE() and CURDATE() <= t.to',
                'params' => array(':trtypeid' => $_GET['nodeid']),
                'order' => 't.id'));
            $ordersArray = array();
            foreach ($todayOrdersList as $todayOrderRecord) {
                if (isset($todayOrderRecord->bort->moveonmap->datatime)) {
                    $last_location_send = $todayOrderRecord->bort->moveonmap->datatime;
                } else {
                    $last_location_send = '';
                }
                if (isset($todayOrderRecord->bort->moveonschedule->datatime)) {
                    $last_schedule_send = $todayOrderRecord->bort->moveonschedule->datatime;
                } else {
                    $last_schedule_send = '';
                }
                $ordersArray[] = array(
                    'bort_number' => $todayOrderRecord->bort->number,
                    'bort_state_number' => $todayOrderRecord->bort->state_number,
                    'graph_name' => $todayOrderRecord->graph->name,
                    'route_name' => $todayOrderRecord->graph->route->name,
                    'last_location_send' => $last_location_send,
                    'last_schedule_send' => $last_schedule_send,
                    'bort_id' => $todayOrderRecord->bort->id,
                    'graph_id' => $todayOrderRecord->graph->id,
                    'route_id' => $todayOrderRecord->graph->route->id,
                    'order_status' => $todayOrderRecord->status->name
                );
            }
            $routeGraphList = Graphs::model()->with('route')->findAll(array(
                'condition' => 'route.transport_types_id=:trtypeid and route.status = "yes" and t.status = "yes"',
                'params' => array(':trtypeid' => $_GET['nodeid']),
                'order' => 'cast(route.name AS DECIMAL),cast(t.name AS DECIMAL)'));
            $graphsArray = array();
            foreach ($routeGraphList as $routeGraphRecord) {
                $graphsArray[] = array(
                    'graph_id' => $routeGraphRecord->id,
                    'graph_name' => $routeGraphRecord->name,
                    'route_name' => $routeGraphRecord->route->name,
                    'bort_number' => '',
                    'bort_state_number' => '',
                    'last_location_send' => '',
                    'last_schedule_send' => '',
                    'bort_id' => '',
                    'order_status' => ''
                );
            }
            $reportArray = array();
            $timeFirst = strtotime(date("Y-m-d H:i:s"));
            $timeDiff = 900;
            for ($i = 0; $i < count($graphsArray); $i++) {
                //print_r($graphsArray[$i]);
                for ($j = 0; $j < count($ordersArray); $j++) {
                    if ($graphsArray[$i]['graph_id'] == $ordersArray[$j]['graph_id']) {
                        $timeSecond = strtotime($ordersArray[$j]['last_location_send']);
                        if ($timeFirst - $timeSecond > $timeDiff) {
                            $reportArray[] = $ordersArray[$j];
                        }
                    }
                }
            }
            // create Word docx
            Yii::setPathOfAlias('PhpOffice', Yii::getPathOfAlias('application.vendors.PhpOffice'));
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $section = $phpWord->addSection();
            $header = array('size' => 8, 'bold' => true);
            $section->addText(htmlspecialchars('Звіт про транспортні засоби, що не відображаються на карті ' .
                date("Y-m-d H:i:s"), ENT_COMPAT, 'UTF-8'), $header);
            $styleCell = array('valign' => 'center');
            $tableStyle = array('cellMarginTop' => 80,
                'cellMarginLeft' => 80,
                'cellMarginRight' => 80,
                'cellMarginBottom' => 80,
                'borderSize' => 10);
            $table = $section->addTable($tableStyle);
            $table->addRow(900);
            $table->addCell(500, $styleCell)->addText(htmlspecialchars('№ з/п', ENT_COMPAT, 'UTF-8'));
            $table->addCell(800, $styleCell)->addText(htmlspecialchars('Маршрут', ENT_COMPAT, 'UTF-8'));
            $table->addCell(700, $styleCell)->addText(htmlspecialchars('Графік', ENT_COMPAT, 'UTF-8'));
            $table->addCell(800, $styleCell)->addText(htmlspecialchars('Борт', ENT_COMPAT, 'UTF-8'));
            $table->addCell(1500, $styleCell)->addText(htmlspecialchars('Державний номер ТЗ', ENT_COMPAT, 'UTF-8'));
            $table->addCell(4500, $styleCell)->addText(htmlspecialchars('Роз\'яснення причини не відображення на карті', ENT_COMPAT, 'UTF-8'));
            $table->addCell(1500, $styleCell)->addText(htmlspecialchars('Знаходження бортового пристрою', ENT_COMPAT, 'UTF-8'));
            for ($h = 0; $h < count($reportArray); $h++) {
                $table->addRow();
                $table->addCell(500, $styleCell)->addText(htmlspecialchars(($h + 1), ENT_COMPAT, 'UTF-8'));
                $table->addCell(800, $styleCell)->addText(htmlspecialchars($reportArray[$h]['route_name'], ENT_COMPAT, 'UTF-8'));
                $table->addCell(700, $styleCell)->addText(htmlspecialchars($reportArray[$h]['graph_name'], ENT_COMPAT, 'UTF-8'));
                $table->addCell(800, $styleCell)->addText(htmlspecialchars($reportArray[$h]['bort_number'], ENT_COMPAT, 'UTF-8'));
                $table->addCell(1500, $styleCell)->addText(htmlspecialchars($reportArray[$h]['bort_state_number'], ENT_COMPAT, 'UTF-8'));
                $table->addCell(4500, $styleCell)->addText(htmlspecialchars('', ENT_COMPAT, 'UTF-8'));
                $table->addCell(1500, $styleCell)->addText(htmlspecialchars('', ENT_COMPAT, 'UTF-8'));
            }
            $fileName = 'OrderReport_' . date('Ymd') . '.docx';
            $filePath = Yii::getPathOfAlias('webroot') . '/info/tmp/' . $fileName;
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save($filePath);
            Yii::app()->getRequest()->sendFile($fileName, @file_get_contents($filePath));
            // create excel file
            /*Save file
           echo write($phpWord, basename(__FILE__, '.php'), $writers);
           if (!CLI) {
               include_once 'Sample_Footer.php';
           }*/

            /*Yii::import('ext.phpexcel.XPHPExcel');
            $objPHPExcel= XPHPExcel::createPHPExcel();
            $objPHPExcel->getProperties()->setCreator("MAK Lutsk")
                ->setLastModifiedBy("MAK")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX")
                ->setKeywords("office 2007")
                ->setCategory("Report file");
            $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:H1');
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Звіт станом на '.date("Y-m-d H:i:s"));
            $style_title = array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            );
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray($style_title);
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A2', '№ з/п')
                ->setCellValue('B2', 'Маршрут')
                ->setCellValue('C2', 'Графік')
                ->setCellValue('D2', 'Борт')
                ->setCellValue('E2', 'Державний номер ТЗ')
                ->setCellValue('F2', 'Місцеположення')
                ->setCellValue('G2', 'Графіковість')
                ->setCellValue('H2', 'Статус');
            $style_body = array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );
            $objPHPExcel->getActiveSheet()->getStyle('A2:H2')->applyFromArray($style_body);
            for($h=0;$h<count($reportArray);$h++){
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.(3+$h), $h+1)
                    ->setCellValue('B'.(3+$h), $reportArray[$h]['route_name'])
                    ->setCellValue('C'.(3+$h), $reportArray[$h]['graph_name'])
                    ->setCellValue('D'.(3+$h), $reportArray[$h]['bort_number'])
                    ->setCellValue('E'.(3+$h), $reportArray[$h]['bort_state_number'])
                    ->setCellValue('F'.(3+$h), $reportArray[$h]['last_location_send'])
                    ->setCellValue('G'.(3+$h), $reportArray[$h]['last_schedule_send'])
                    ->setCellValue('H'.(3+$h), $reportArray[$h]['order_status']);
                $objPHPExcel->getActiveSheet()->getRowDimension(3+$h)->setRowHeight(25);
                $objPHPExcel->getActiveSheet()->getStyle('A'.(3+$h).':H'.(3+$h))->applyFromArray($style_body);
            }
            $objPHPExcel->getActiveSheet()->setTitle('Звіт');
            $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(40);
            $objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
            $objPHPExcel->setActiveSheetIndex(0);
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="report.xls"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header ('Pragma: public'); // HTTP/1.0
            $fileName = 'OrderReport_'.date('Ymd').'.xlsx';
            $filePath = Yii::getPathOfAlias('webroot').'/info/tmp/'.$fileName;
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save($filePath);
            Yii::app()->getRequest()->sendFile($fileName, @file_get_contents($filePath));*/
        }
        else{
            echo "{success: false, msg: 'Not permitted!}";
        }
    }
}
?>
