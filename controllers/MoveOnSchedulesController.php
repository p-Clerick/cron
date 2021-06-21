<?php

class MoveOnSchedulesController extends CController
{
	public function actionRead(){
        $count = 0;
        $result = array(
            'success'=>'true',
            'data'=>array()
        );
        if(Yii::app()->user->name != "guest"){
            $carrier = Yii::app()->user->checkUser(Yii::app()->user);
        }
        if($_GET['level'] === '1'){       
            if ($carrier){
                $moveonschedules = MoveOnSchedule::model()->with('bort','bort.model','schedule.graph','route')->findAll(array(
                    'condition'=>
                    'date(datatime) = CURRENT_DATE() and
                     model.transport_types_id=:trtypeid and route.carriers_id=:carrid and bort.connection = "yes"',
                    'params'=>array(':carrid'=>$carrier['carrier_id'],':trtypeid'=>$_GET['nodeid']),
                    'order' => 't.id')
                );
            }
            else{
                $moveonschedules = MoveOnSchedule::model()->with('bort','bort.model','schedule.graph')->findAll(array(
                    'condition'=>
                    'date(datatime) = CURRENT_DATE() and
                     model.transport_types_id=:trtypeid and bort.connection = "yes"',
                    'params'=>array(':trtypeid'=>$_GET['nodeid']),
                    'order' => 't.id')
                );
            }                
        }
        
        if($_GET['level'] === '2'){
            $moveonschedules = MoveOnSchedule::model()->with('bort','bort.model','schedule.graph','points_control_scenario.points_control','stations')->findAll(array(
                'condition'=>
                    'date(datatime) = CURRENT_DATE() and
                    graph.routes_id=:grrouteid and bort.connection = "yes"',
                'params'=>array(':grrouteid'=>$_GET['nodeid']),
                'order' => 't.id')
            );
        }
        if($_GET['level'] === '3'){
            $unixtime_from = strtotime(date('Y-m-d'));
            $locinflights = LocationsFlight::model()->with('route','stations','graph','bort')->findAll(array(
                'condition'=>'unixtime >= :from and graphs_id=:grid and bort.connection = "yes"',
                'params'=>array(':grid'=>$_GET['nodeid'],':from'=>$unixtime_from),
                'order' => 't.id DESC')
            );
            $graphs = Graphs::model()->findByPk($_GET['nodeid']);
            $rt_id = $graphs->routes_id;
           
            $st_sc=StationsScenario::model()->findAll(array(
                'condition'=>'routes_id=:rtid',
                'params'=>array(':rtid'=>$rt_id),
                'order' => 't.id DESC')
            );
            foreach ($st_sc as $k) {
                $arrayDirections[]=array('stationsId'=>$k->stations_id, 'direction'=>$k->route_directions_id);
            }
            $rt_dir=RouteDirections::model()->findAll(array(
                'condition'=>'routes_id=:rtid',
                'params'=>array(':rtid'=>$rt_id),
                'order' => 't.id DESC')
            );
            foreach ($rt_dir as $k) {
                $arrNameDir[$k->id]=$k->name;
            }      
            $countdir=count($arrayDirections);        

            foreach($locinflights as $locinflight){
                if ($locinflight->arrival_plan!=null) {
                    $arrPlan= new Time($locinflight->arrival_plan);
                }
                if ($locinflight->arrival_plan==null) {
                    $arrPlan= new Time('00000');
                }

                $result['data'][] = array(
                    'id'                => $locinflight->id,
                    'route'             => $locinflight->route->name,
                    'graph'             => (int)$locinflight->graph->name,                
                    'number'            => $locinflight->bort->number,
                    'st_number'         => $locinflight->bort->state_number,
                    'spec_need'         => $locinflight->bort->special_needs,                
                    'stopname'          => $locinflight->stations->name,
                    'stationsId'        => $locinflight->stations_id,
                    'flight_number'     => $locinflight->flights_number,
                    'arrival_plan'      => $arrPlan->getFormattedTime(),
                    'arrival_fact'      => substr(strftime('%Y-%m-%d %H:%M:%S',$locinflight->unixtime),-8),               
                    'time_difference'   => $locinflight->time_difference                
                );
                $count++;                
            }
            for ($i=0; $i < $count; $i++) { 
                for ($a=0; $a < $countdir; $a++) {                                      
                    if ($result['data'][$i]['stationsId'] == $arrayDirections[$a]['stationsId']) {
                        $result['data'][$i]['direction'] = $arrNameDir[$arrayDirections[$a]['direction']];
                    }                    
                }
            }            
        }  

        if($_GET['level'] != '3'){
            foreach($moveonschedules as $moveonschedule){
                    if($moveonschedule->stations_id != 0){
                        $stid = $moveonschedule->stations_id;
                        $stname = $moveonschedule->stations->name;
                    }
                    else{
                        $stid = 0;
                        $stname = $moveonschedule->points_control_scenario->points_control->name;                    
                    }
                $result['data'][] = array(
                    'id'                => $moveonschedule->id,
                    'route'             => $moveonschedule->schedule->graph->route->name,
                    'graph'             => (int)$moveonschedule->schedule->graph->name,                
                    'number'            => $moveonschedule->bort->number,
                    'st_number'         => $moveonschedule->bort->state_number,
                    'spec_need'         => $moveonschedule->bort->special_needs,                
                    'stopname'          => $stname,
                    'stationsId'        => $stid,
                    'flight_number'     => $moveonschedule->flight_number,
                    'arrival_plan'      => gmdate('H:i:s', $moveonschedule->arrival_plan),
                    'arrival_fact'      => substr($moveonschedule->datatime,-8),               
                    'time_difference'   => $moveonschedule->time_difference                
                );
                $count++;
            }
            $st_sc=StationsScenario::model()->with('route')->findAll();
            foreach ($st_sc as $k) {
                $arrayDirections[]=array('route'=>$k->route->name, 'stationsId'=>$k->stations_id, 'direction'=>$k->route_directions_id);
            }
            $rt_dir=RouteDirections::model()->findAll();
            foreach ($rt_dir as $k) {
                $arrNameDir[$k->id]=$k->name;
            }      
            $countdir=count($arrayDirections);        
            for ($i=0; $i < $count; $i++) { 
                for ($a=0; $a < $countdir; $a++) {
                    if ($result['data'][$i]['route'] === $arrayDirections[$a]['route']) {                   
                        if ($result['data'][$i]['stationsId'] == $arrayDirections[$a]['stationsId']) {
                            $result['data'][$i]['direction'] = $arrNameDir[$arrayDirections[$a]['direction']];
                        }
                    }
                }
            }
        }
     
        echo json_encode($result);
        exit;



                      











                   $count = 0;
                   $result = array(
                   					'success'=>'true',
									'data'=>array()
									//'count'=>array()
				   );
                     // print_r($moveonschedules[0]->schedule->scheduletime[1]);
                     // exit;
                 /*     foreach($moveonschedules as $moveonschedule){
                      		$last = $moveonschedule->points_control_scenario_id;
                      		foreach($moveonschedule->schedule->graph->route->points_control_scenario as $pcs){
                      			 	 $point[$pcs->id] = $pcs->points_control->name;
                      		}
                      		ksort($point);
                      		reset($point);
                      		print_r($point);
                      		while (list($key, $val) = each($point)) {

                      			if ($key == $last){
                      				 echo $key." = ".$val; echo "\n";
                      				 if (prev($point));
                                     if (next($point)){
                                     	 $tmp = current($point);
                                     	 echo key($point)." = ".$tmp; echo "\n";
                                     }
                                     else{
                                     	 reset($point);
                                     	 $tmp = current($point);
                                     	 echo key($point)." = ".$tmp; echo "\n";
                                     	 break;
                                     }
                      			}
                      		}
                      		foreach($moveonschedule->schedule->scheduletime as $times){
                                     $timem[$times->points_control_scenario_id] = $times->time;
                      		}
                      		ksort($timem);
                      		reset($timem);
                      		while (list($key, $val) = each($timem)) {

                      			if ($key == $last){
                      				 echo $key." = ".$val; echo "\n";
                      				 if (prev($timem));
                                     if (next($timem)){
                                     	 $tmp = current($timem);
                                     	 echo key($timem)." = ".$tmp; echo "\n";
                                     }
                                     else{
                                     	 reset($timem);
                                     	 $tmp = current($timem);
                                     	 echo key($timem)." = ".$tmp; echo "\n";
                                     	 break;
                                     }
                      			}
                      		}
                      		$timem = '';
                      		$point = '';
                      }



                      exit;  */





                      foreach($moveonschedules as $moveonschedule){

                      		$last = $moveonschedule->points_control_scenario_id;
                      		foreach($moveonschedule->schedule->graph->route->points_control_scenario as $pcs){
                      			 	 $point[$pcs->id] = $pcs->points_control->name;
                      		}
                      		ksort($point);
                      		reset($point);
                      		while (list($key, $val) = each($point)) {
                      			if ($key == $last){
                      				 $stopname = $val;
                      				 if($_GET['level'] != '3'){
	                      				 if (prev($point));
	                                     if (next($point)){
	                                     	 $tmp = current($point);
	                                     	 $nextstopname = $tmp;
	                                     }
	                                     else{
	                                     	 reset($point);
	                                     	 $tmp = current($point);
	                                     	 $nextstopname = $tmp;
	                                     	 break;
	                                     }
                                     }
                                     else{
                                     	 $nextstopname = 'null';
                                     }
                      			}
                      		}
                      		foreach($moveonschedule->schedule->scheduletime as $times){
                                     $timem[$times->points_control_scenario_id] = $times->time;
                      		}
                      		ksort($timem);
                      		reset($timem);
                      		if($_GET['level'] != '3'){
	                      		while (list($key, $val) = each($timem)) {
	                      			if ($key == $last){
	                                $tmp  = new Time($val);
	                      				 $arrival = $tmp->getFormattedTime();

		                      				 if (prev($timem));
		                                     if (next($timem)){
		                                     	 $tmp = new Time(current($timem));
		                                     	 $nextarrival = $tmp->getFormattedTime();
		                                     }
		                                     else{
		                                     	 reset($timem);
		                                     	 $tmp = current($timem);
		                                     	 $nextarrival = $tmp;
		                                     	 $schedules = Schedules::model()->with('scheduletime')->findAll(array(
				                   					'condition'=>'scheduletime.schedules_id=:schedid and scheduletime.flight_number=:flnum',
				                   					'params'=>array(':schedid'=>$moveonschedule->schedules_id, ':flnum'=>$moveonschedule->flight_number+1),
				                   					'order' => 'scheduletime.time')
				                   					);
				                   					//$nextarrival = $schedules[0]->scheduletime[0]->time;
		                                        $tmp = new Time($schedules[0]->scheduletime[0]->time);
		                                        $nextarrival = $tmp->getFormattedTime();
		                                     	 break;
		                                     }

	                      			}
	                      		}
	                      	}
                             else{
		                                     	 $schedulestime = ScheduleTimes::model()->find(array(
				                   					'condition'=>'t.schedules_id=:schedid and t.flight_number=:flnum and t.points_control_scenario_id=:pcsid',
				                   					'params'=>array(':schedid'=>$moveonschedule->schedules_id, ':flnum'=>$moveonschedule->flight_number, ':pcsid'=>$moveonschedule->points_control_scenario_id),
				                   					'order' => 't.time')
				                   					);
				                   					//$nextarrival = $schedules[0]->scheduletime[0]->time;
		                                        $tmp = new Time($schedulestime->time);
		                                        $arrival = $tmp->getFormattedTime();
                                                $nextarrival = 'null';
                             }
                      		$timem = '';
                      		$point = '';

							$result['data'][] = array(
								'id'				=> $moveonschedule->id,
								'number'			=> $moveonschedule->bort->number,
								'stopname'			=> $stopname,
								'arrival'			=> $arrival,
								'nextstopname'		=> $nextstopname,
								'nextarrival'		=> $nextarrival,
								'datatime' 			=> $moveonschedule->datatime,
								'route'				=> $moveonschedule->schedule->graph->route->name,
								'graph'     		=> (int)$moveonschedule->schedule->graph->name,
								'time_difference'	=> $moveonschedule->time_difference,
								'flight_number'		=> $moveonschedule->flight_number,
							);
                            $count++;

                      }
                    /*   foreach($moveonschedules as $moveonschedule){
                       		 print_r($moveonschedule->points_control_scenario->points_control);

                       }  */
                      // echo json_encode($mas);
                     //  $result['count'] = $count;

                      echo json_encode($result);

   	   }
}
?>
