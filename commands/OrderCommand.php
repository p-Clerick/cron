 <?php
Yii::import('application.models.*');
class OrderCommand extends CConsoleCommand
{
    public function run($day) {
    	MoveOnSchedule::model()->deleteAll();
    	OrdersLoad::model()->deleteAll();
    	$move_on_map = MoveOnMap::model()->findAll(array(
	   		'select'=>'borts_id',
	   		'condition'=>'date(t.datatime) = CURRENT_DATE()',                   					
	   		'order' => 't.borts_id')
	    );
		foreach ($move_on_map as $mom){
			$cl_send_com_load = new ClientSendCommandsLoad;
			$cl_send_com_load->borts_id = $mom->borts_id;
			$cl_send_com_load->client_commands = 'console';
			$cl_send_com_load->params = 'am broadcast -a "mak.android.device.shutdown"';
			$cl_send_com_load->save();
		}	    

        $orders = Orders::model()->with('bort','bort.model','status','graph')->findAll(array(
			'condition'=>'model.transport_types_id=:trtypeid and t.from <= CURRENT_DATE and CURRENT_DATE <= t.to',
			'params'=>array(':trtypeid'=>1),
            'order' => 't.id'));		                	
        $rt_gr_list = Graphs::model()->with('route')->findAll(array(
			'condition'=>'route.transport_types_id=:trtypeid and route.status = "yes"',
			'params'=>array(':trtypeid'=>1),
            'order' => 'cast(route.name AS DECIMAL),cast(t.name AS DECIMAL)'));
        $bort_arr[] = 0;
       // $borts_exist = [];
		foreach($rt_gr_list as $rgl){
			$gr_id = 0;
			foreach($orders as $order){
				if ($rgl->id == $order->graph->id){
					$gr_id = $rgl->id; 
				}
			}
			if(!$gr_id){
				//echo "No order for: ".$rgl->route->name." - ".$rgl->name."\n";
				$past_order = Orders::model()->find(array(	    							
	                'condition'=>'t.to = CURRENT_DATE - INTERVAL "1" DAY and graphs_id=:gr_id',                  	    			
	                'params'=>array(':gr_id'=>$rgl->id),				                        	
				));
				if($past_order && $past_order->id){
					//echo "Yesterday order exist\n";
                   // $cont = false;
                   // foreach ($borts_exist as $bb) {
                   //     if($bb == $past_order->borts_id) {
                   //         $cont = true;
                   //     }
                   // }
                   // if($cont) continue;
					$new_order = new Orders;
				    $new_order->borts_id = $past_order->borts_id;
				    $new_order->graphs_id = $past_order->graphs_id;
				    $new_order->created =date('Y-m-d H:i:s');
				    $new_order->from = date('Y-m-d');
				    $new_order->to = date('Y-m-d');
				    $new_order->order_statuses_id = 1;
				    //$new_order->bort_statuses_id = $order->bort_statuses_id;
			    	$new_order->save();
                   // $borts_exist[] = $past_order->borts_id;
				}									
			}
			$gr_id = 0;
		}  
		 
        $orders = Orders::model()->with('bort','bort.model','status','graph')->findAll(array(
			'condition'=>'model.transport_types_id=:trtypeid and t.from <= CURRENT_DATE and CURRENT_DATE <= t.to',
			'params'=>array(':trtypeid'=>1),
            'order' => 't.id'));

		foreach($rt_gr_list as $rgl){
			foreach($orders as $order){
				if ($rgl->id == $order->graph->id){
					$orderload = new OrdersLoad;
					$orderload->borts_id = $order->borts_id;
	  			 	$schedules_types_id = OrdersLoad::model()->SheduleTypeId();	  			 	
  			 		$graphs = Graphs::model()->findByPk($order->graph->id); 			 		
   			 		$schedules = $graphs->getCurrentSchedule($schedules_types_id);   			 		
   			 		foreach ($bort_arr as $bort) {
						$borts_id = 0;
   			 			if($bort == $orderload->bort->id){
   			 				//echo $bort." dubl\n";
   			 				$borts_id = $orderload->bort->id;
   			 			}
   			 		}
   			 		if($borts_id){
   			 			$borts_id = 0;
   			 			continue;
   			 		}
   			 		$bort_arr[] = $orderload->bort->id;
	              	if($schedules){
						$orderload->schedules_id = $schedules->id;
						$orderload->number =  $order->bort->number;
						$orderload->route_num = $order->graph->route->name;
						$orderload->graph_num = $order->graph->name;
						$orderload->route_type = $order->graph->route->routemovemethod->name;
						$orderload->reboot = 0;
						$orderload->orders_id = $order->id;
						$orderload->save();

						$orderloadhistory = new OrdersLoadHistory;
						$orderloadhistory->borts_id = $order->borts_id;						
						$orderloadhistory->number =  $order->bort->number;
						$orderloadhistory->schedules_id = $schedules->id;
						$orderloadhistory->created = date('Y-m-d G:i:s');
						$orderloadhistory->schedule_types_id = $schedules->schedule_types_id;
						$orderloadhistory->route_num = $order->graph->route->name;
						$orderloadhistory->graph_num = $order->graph->name;						
						$orderloadhistory->reboot = 0;
						$orderloadhistory->orders_id = $order->id;
						$orderloadhistory->save();						
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
		//print_r($bort_arr);

































    	exit;
    	$orderss =Orders::model()->with('bort','graph.route')->findAll(array(
		    	'condition' => 't.from <= CURRENT_DATE and CURRENT_DATE <= t.to and route.status = "yes"'		    		
	    	));
	    	$borts = "(";
	    	foreach($orderss as $order){
	    		$borts .=  $order->bort->id;	
	    		$borts .=",";
	    	}   	    
			$borts = substr($borts, 0, -1);	    	
	    	$borts .=")";
	    	 OrdersLoad::model()->deleteAll();
	    	 if (!$orderss){
     			$borts = "(0)";
	    	 }
	    	 print_r($borts);
	    	 exit;
    	if (
	    	Orders::model()->with('bort','graph.route')->find('t.to = CURRENT_DATE - INTERVAL "1" DAY and route.status = "yes" and bort.id NOT IN '.$borts,
		    	array(
					':today' => date('Y-m-d')					
				)	
	    	)
	    ){
	    	echo "Yesterday order exist\n";
	    	$orders = Orders::model()->with('bort','bort','graph.route')->findAll(array(	    							
	                  					'condition'=>'t.to = CURRENT_DATE - INTERVAL "1" DAY and route.status = "yes" and bort.id NOT IN '.$borts,                  	    			
	                   					'group' => 't.graphs_id'				                        	
				                        ));
	     						    foreach($orders as $order){
	     						    				                        
			 			                    $new_order = new Orders;
			           					    $new_order->borts_id = $order->borts_id;
			           					    $new_order->graphs_id = $order->graphs_id;
			           					    $new_order->created =date('Y-m-d H:i:s');
			           					    $new_order->from = date('Y-m-d');
			           					    $new_order->to = date('Y-m-d');
			           					    $new_order->order_statuses_id = 1;
			           					    //$new_order->bort_statuses_id = $order->bort_statuses_id;
										    $new_order->save();													
     	  	           				}   	
     	}
     	    	    	
    	//if ($orderss){
			//echo "Today order exist\n";
			$orders = Orders::model()->with('bort','graph.route')->findAll(array(
	                  					'condition'=>'t.from <= CURRENT_DATE and CURRENT_DATE <= t.to and route.status = "yes"',	                   					
				                        'order' => 't.id'));				                      

	     						    foreach($orders as $order){
											$orderload = new OrdersLoad;
											$schedules_types_id = OrdersLoad::model()->SheduleTypeId();
											if ($order->graph->id == 0){
												$orderload->schedules_id = 0;
												$orderload->borts_id = $order->borts_id;
												$orderload->number =  $order->bort->number;
												$orderload->route_num = 0;
												$orderload->graph_num = 0;
												$orderload->route_type = 0;
												$orderload->reboot = 0;
												$orderload->orders_id = $order->id;
												$orderload->save();
											}
											else{
								   			 	$graphs = Graphs::model()->findByPk($order->graph->id);
								   			 	$schedules = $graphs->getCurrentSchedule($schedules_types_id);
								   			 	if($schedules){
													$orderload->schedules_id = $schedules->id;
													$orderload->borts_id = $order->borts_id;
													$orderload->number =  $order->bort->number;
													$orderload->route_num = $order->graph->route->name;
													$orderload->graph_num = $order->graph->name;
													$orderload->route_type = $order->graph->route->routemovemethod->name;
													$orderload->reboot = 0;
													$orderload->orders_id = $order->id;
													$orderload->save();									
												}												
											}

     	  	           				}	     	
	     	$orderload = '';
     	}
    //}
}

