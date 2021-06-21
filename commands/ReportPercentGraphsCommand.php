<?php
Yii::import('application.models.*');
class ReportPercentGraphsCommand extends CConsoleCommand
{
	public function run($dateToRecalc) {
		$rewiew=$dateToRecalc;
		$countDate=count($rewiew);
		if ($countDate==0){
 			$day=date('Y-m-d');
 			$find=DaysToReport::model()->findByAttributes(array(
 				'date'=>$day));
 			$dy=$find->found_days;
 			$dyy=explode(",", $dy);
 			foreach ($dyy as $key => $value) {
 				$rewiew[$key]=$value;
 			}
 			$countDate=count($rewiew);
 		}
 		for ($cd=0; $cd < $countDate; $cd++) {
 			$startTimeReport[$cd]=time();
			if ($rewiew[$cd]!=null) {//якщо треба перерахувати вручну за якийсь день
		   		$dayToCalc[$cd]=$rewiew[$cd];//присвоюємо час що ввели вручну
		   	
	    	}//якщо перерахунок
	    	if ($rewiew[$cd]==null){//робимо вночі кожного дня
	    		$dayToCalc[$cd]=date("Y-m-d",strtotime ("yesterday"));//присвоюємо час що відповідає вчорашньому дню
	    	}//if calc yesterday
	    	//сам розрахунок
	    	$locate=ReportPercentageFlightsGraphs::model()->findAll(array(
	    		'condition' => 'date = :d',
				'params' => array(':d' => $dayToCalc[$cd])));	
    		
			$countSql=count($locate);
			if ($countSql==0) {
				$success[$cd]='N';
				$message[$cd]="no found records in table for date ".$dayToCalc[$cd];
				$endTimeReport[$cd]=time();
				$newRecordReport = new ExecutionsCommands;
				$newRecordReport->date=date("Y-m-d");
				$newRecordReport->commands_id=11;
				$newRecordReport->start_time=$startTimeReport[$cd];
				$newRecordReport->end_time=$endTimeReport[$cd];
				$newRecordReport->duration=$endTimeReport[$cd]-$startTimeReport[$cd];
				$newRecordReport->success=$success[$cd];
				$newRecordReport->comment=$message[$cd];
				$newRecordReport->save();
			}
			else if ($countSql!=0) {
				$success[$cd]='Y';
				foreach ($locate as $k) {
					$arrayInsertFlights[]=array(
						'route_id'=>$k->routes_id,
						'graphs_id'=>$k->graphs_id,
						'borts_id'=>$k->borts_id,
						'flights_number'=>$k->flights_number,
						'percentage_realization'=>$k->percentage_realization,
						'percentage_stations'=>$k->percentage_stations,
						'percentage_flight'=>$k->percentage_flight,
						'count_stations_plan'=>$k->count_stations_plan,
						'count_stations_fakt'=>$k->count_stations_fakt,
						'count_flight_plan'=>$k->count_flight_plan,
						'count_flight_fakt'=>$k->count_flight_fakt,
						'percentage_end_stops'=>$k->percentage_end_stops,
						'count_route_directions'=>$k->count_route_directions
					);
				}
				for ($i=0; $i <count($arrayInsertFlights); $i++) {
					//всього рейсів за планом
					$arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_flight_plan']=$arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_flight_plan']+$arrayInsertFlights[$i]['count_flight_plan'];
					//всього рейсів по факту
					$arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_flight_fakt']=$arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_flight_fakt']+$arrayInsertFlights[$i]['count_flight_fakt'];
					//всього % рейсів за ф/план*100,2
					$arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['percentage_flight']=round($arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_flight_fakt']/$arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_flight_plan']*100,2);
					//всього точок за планом
					$arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_stations_plan']=$arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_stations_plan']+$arrayInsertFlights[$i]['count_stations_plan'];
					//всього точок по факту
					$arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_stations_fakt']=$arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_stations_fakt']+$arrayInsertFlights[$i]['count_stations_fakt'];
					//всього % точок за ф/план*100,2
					$arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['percentage_stations']=round($arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_stations_fakt']/$arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_stations_plan']*100,2);

					//сума % реалізації
					$arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['percentage_realization00']=$arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['percentage_realization00']+$arrayInsertFlights[$i]['percentage_realization'];
					//сума % реалізації/кількіть рейсів - середнє арифметичне
					$arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['percentage_realization11']=round($arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['percentage_realization00']/$arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_flight_plan'],2);
					//всього пів рейсів
					$arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_route_directions']=$arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_route_directions']+$arrayInsertFlights[$i]['count_route_directions'];
				}	
				//print_r($arrayInsertGraphs);
				//видаляемо дані з таблиць
	    		ReportPercentageGraphs::model()->deleteAll(array(
	    			'condition' => 'date = :d',
					'params' => array(':d' => $dayToCalc[$cd])));
				foreach ($arrayInsertGraphs as $routeid => $value) {
					foreach ($value as $graphsid => $value1) {
						if ($value1['percentage_realization11']>$value1['percentage_stations']) {
							$value1['percentage_realization11']=$value1['percentage_stations'];
						}
						$ins=new ReportPercentageGraphs;
						$ins->date=$dayToCalc[$cd];
						$ins->routes_id=$routeid;
						$ins->graphs_id=$graphsid;
						$ins->percentage_realization=$value1['percentage_realization11'];
						$ins->percentage_stations=$value1['percentage_stations'];
						$ins->percentage_flight=$value1['percentage_flight'];
						$ins->count_stations_plan=$value1['count_stations_plan'];
						$ins->count_stations_fakt=$value1['count_stations_fakt'];
						$ins->count_flight_plan=$value1['count_flight_plan'];
						$ins->count_flight_fakt=$value1['count_flight_fakt'];
						$ins->count_route_directions=$value1['count_route_directions'];
						$ins->save();
					}
				}
				foreach ($arrayInsertGraphs as $routeid => $value) {
					$countGraph[$routeid]=count($value);
					foreach ($value as $graphsid => $value1) { 
						
							$arrayInsertRoute[$routeid]['count_stations_plan']=$arrayInsertRoute[$routeid]['count_stations_plan']+$arrayInsertGraphs[$routeid][$graphsid]['count_stations_plan'];
							$arrayInsertRoute[$routeid]['count_stations_fakt']=$arrayInsertRoute[$routeid]['count_stations_fakt']+$arrayInsertGraphs[$routeid][$graphsid]['count_stations_fakt'];
							$arrayInsertRoute[$routeid]['count_flight_plan']=$arrayInsertRoute[$routeid]['count_flight_plan']+$arrayInsertGraphs[$routeid][$graphsid]['count_flight_plan'];
							$arrayInsertRoute[$routeid]['count_flight_fakt']=$arrayInsertRoute[$routeid]['count_flight_fakt']+$arrayInsertGraphs[$routeid][$graphsid]['count_flight_fakt'];
							$arrayInsertRoute[$routeid]['percentage_realization11']=$arrayInsertRoute[$routeid]['percentage_realization11']+$arrayInsertGraphs[$routeid][$graphsid]['percentage_realization11'];

							$arrayInsertRoute[$routeid]['count_route_directions']=$arrayInsertRoute[$routeid]['count_route_directions']+$arrayInsertGraphs[$routeid][$graphsid]['count_route_directions'];
						
					}
				}
				//видаляемо дані з таблиць
	    		ReportPercentageRoutesGraphs::model()->deleteAll(array(
	    			'condition' => 'date = :d',
					'params' => array(':d' => $dayToCalc[$cd])));
				foreach ($arrayInsertRoute as $rout => $value) {
					$rty=round($value['percentage_realization11']/$countGraph[$rout],2);
					$rty1=round($value['count_stations_fakt']/$value['count_stations_plan']*100,2);
					if ($rty>$rty1) {
						$rty=$rty1;
					}
					$ins=new ReportPercentageRoutesGraphs;
					$ins->date=$dayToCalc[$cd];
					$ins->routes_id=$rout;
					$ins->percentage_realization=$rty;
					$ins->percentage_stations=$rty1;
					$ins->percentage_flight=round($value['count_flight_fakt']/$value['count_flight_plan']*100,2);
					$ins->count_stations_plan=$value['count_stations_plan'];
					$ins->count_stations_fakt=$value['count_stations_fakt'];
					$ins->count_flight_plan=$value['count_flight_plan'];
					$ins->count_flight_fakt=$value['count_flight_fakt'];
					$ins->count_route_directions=$value['count_route_directions'];
					$ins->save();
				}
				
				
				
				
				unset($locate);
				unset($countSql);
				unset($arrayInsertFlights);
				unset($arrayInsertGraphs);
				unset($countGraph);//
				unset($arrayInsertRoute);
				
				
				$endTimeReport[$cd]=time();
				$cdPlusOne=$cd+1;
				$message[$cd]="calc report on day ".$dayToCalc[$cd]." ".$cdPlusOne." from ".$countDate;
				$newRecordReport = new ExecutionsCommands;
				$newRecordReport->date=date("Y-m-d");
				$newRecordReport->commands_id=11;
				$newRecordReport->start_time=$startTimeReport[$cd];
				$newRecordReport->end_time=$endTimeReport[$cd];
				$newRecordReport->duration=$endTimeReport[$cd]-$startTimeReport[$cd];
				$newRecordReport->success=$success[$cd];
				$newRecordReport->comment=$message[$cd];
				$newRecordReport->save();
			}
		}
	}
}
?>