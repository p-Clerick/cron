<?php
Yii::import('application.models.*');
class ReportCachStopsCommand extends CConsoleCommand
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
 		$pc=StationsScenario::model()->findAll();
		foreach ($pc as $k) {
			$arrPc[$k->routes_id][$k->stations_id]=array(
				'routeId'=>$k->routes_id,
				'direct' => $k->route_directions_id, 
				'number'=>$k->number,
				'stid'=>$k->stations_id,
				'pcst'=>$k->pc_status,
				'poesId'=>$k->points_of_events_id,
				'id'=>$k->id
			);
		}
		$longlat=PointsOfEvents::model()->findAll();
		
		foreach ($longlat as $k) {
			$arrLonglat[$k->id]=array('ln'=>$k->longitude, 'lt'=>$k->latitude);
		}
		for ($cd=0; $cd < $countDate; $cd++) {
			$startTimeReport[$cd]=time();
			if ($rewiew[$cd]!=null) {//якщо треба перерахувати вручну за якийсь день
		   		$dayToCalc[$cd]=$rewiew[$cd];//присвоюємо час що ввели вручну
		   		//видаляемо дані з таблиць
	    		ReportCachStops::model()->deleteAll(array(
    			'condition' => 'date = :d',
				'params' => array(':d' => $dayToCalc[$cd])));
	    	}//якщо перерахунок
	    	if ($rewiew[$cd]==null){//робимо вночі кожного дня
	    		$dayToCalc[$cd]=date("Y-m-d",strtotime ("yesterday"));//присвоюємо час що відповідає вчорашньому дню
	    	}//if calc yesterday
	    	//сам розрахунок
	    	$todayFrom[$cd]=strtotime($dayToCalc[$cd])+3600;
			$todayTo[$cd]=strtotime($dayToCalc[$cd])+23*3600+59*60+60+3600;
			$a=LocationsFlight::model()->findAll(array(
				'condition'=> 'unixtime >= :f AND unixtime <= :t',
				'params'   =>array(':f'=>$todayFrom[$cd], ':t'=>$todayTo[$cd]),
				'order'    => 'unixtime'));
			$countA=count($a);
			if ($countA==0) {
				$success[$cd]='N';
				$message[$cd]="no found records in table for date ".$dayToCalc[$cd];
				$endTimeReport[$cd]=time();
				$newRecordReport = new ExecutionsCommands;
				$newRecordReport->date=date("Y-m-d");
				$newRecordReport->commands_id=9;
				$newRecordReport->start_time=$startTimeReport[$cd];
				$newRecordReport->end_time=$endTimeReport[$cd];
				$newRecordReport->duration=$endTimeReport[$cd]-$startTimeReport[$cd];
				$newRecordReport->success=$success[$cd];
				$newRecordReport->comment=$message[$cd];
				$newRecordReport->save();
			}
			else if ($countA!=0) {
				foreach ($a as $k) {
					$arrayFakt[$k->schedules_id][$k->flights_number][$k->stations_id]=$k->stations_id;
					$arraySchedules[$k->schedules_id]=array(
						$k->routes_id,
						$k->graphs_id
					);
					$arrayBorts[$k->schedules_id]=$k->borts_id;
					$maxTime[$k->schedules_id]=$k->unixtime;
					$minTime[$k->schedules_id][]=$k->unixtime;
				}
			
				foreach ($arraySchedules as $key => $value) {
					$b=ScheduleTimes::model()->findAll(array(
						'condition'=> 'schedules_id = :stid',
						'params'   =>array(':stid'=>$key),
						'order'    => 'id'));
					foreach ($b as $k) {
						$arrayPlan[$k->schedules_id][$k->flights_number][$k->stations_id]=$k->time;
						$countPlanAmount=$countPlanAmount+1;
					}
					$dinners=Dinner::model()->findAll(array(
						'condition'=> 'schedules_id = :stid',
						'params'   =>array(':stid'=>$key),
						'order'    => 'id'));
					foreach ($dinners as $k) {
						$arrayDinnersTime[$key][$k->flight_number]['start']=$k->start_time;
						$arrayDinnersTime[$key][$k->flight_number]['end']=$k->end_time;
					}
				}
				
				foreach ($arrayPlan as $sc => $value) {
					foreach ($value as $fl => $stat) {
						foreach ($stat as $key => $value1) {
							if (!isset($arrayFakt[$sc][$fl][$key])) {
								$com[$sc][$fl][$key]="donotcach";
								$arrayNePopav[$sc][$fl][$key]=$value1;
								$newUnixAfter=strtotime($dayToCalc[$cd])+$value1;
								if (($newUnixAfter<=$maxTime[$sc]) && ($newUnixAfter>=$minTime[$sc][0])){
									$com[$sc][$fl][$key]=$com[$sc][$fl][$key]."_buv_v_rusi";
									//if (!isset($arrayDinnersTime[$sc][$value1])) {
									if (($value1>=$arrayDinnersTime[$sc][$fl]['start']) && ($value1<=$arrayDinnersTime[$sc][$fl]['end'])){
										//$arrayNePopav[$sc][$fl][$key]=$value1;
										$com[$sc][$fl][$key]=$com[$sc][$fl][$key]."_v_obid";
									}
								}
							}
						}
					}
				}
			//print_r($arrayNePopav);
				foreach ($arrayNePopav as $sc => $value) {
					foreach ($value as $fl => $st) {
						foreach ($st as $statid => $time) {
							$arrayToInsert[]=array(
								'scheduleId'=>$sc,
								'flightNumber'=>$fl,
								'stationsId'=>$statid,
								'time'=>$time
							);
						}
					}
				}	
				$countInsert=count($arrayToInsert);
				for ($i=0; $i <$countInsert ; $i++) { 
					$arrayToInsert[$i]['routeId']=$arraySchedules[$arrayToInsert[$i]['scheduleId']][0];
					$arrayToInsert[$i]['graphId']=$arraySchedules[$arrayToInsert[$i]['scheduleId']][1];
					$arrayToInsert[$i]['poeId']=$arrPc[$arrayToInsert[$i]['routeId']][$arrayToInsert[$i]['stationsId']]['poesId'];
					$arrayToInsert[$i]['bortId']=$arrayBorts[$arrayToInsert[$i]['scheduleId']];
				}
				//print_r($arrayToInsert);
				for ($i=0; $i <$countInsert ; $i++) { 
					if ($arrayToInsert[$i]['time']>7200) {
						$ee = new ReportCachStops;
						$ee->date=$dayToCalc[$cd];
						$ee->amount=$arrayToInsert[$i]['scheduleId'];
						$ee->routes_id=$arrayToInsert[$i]['routeId'];
						$ee->graphs_id=$arrayToInsert[$i]['graphId'];
						$ee->borts_id=$arrayToInsert[$i]['bortId'];
						$ee->stations_id=$arrayToInsert[$i]['stationsId'];
						$ee->flights_number=$arrayToInsert[$i]['flightNumber'];
						$ee->poes_id=$arrayToInsert[$i]['poeId'];
						$ee->arrival_plan=$arrayToInsert[$i]['time'];
						$ee->longitude=$arrLonglat[$arrayToInsert[$i]['poeId']]['ln'];
						$ee->latitude=$arrLonglat[$arrayToInsert[$i]['poeId']]['lt'];
						$ee->comment=$com[$arrayToInsert[$i]['scheduleId']][$arrayToInsert[$i]['flightNumber']][$arrayToInsert[$i]['stationsId']];
						$ee->save();
					}
				}
				$success[$cd]='Y';
				$endTimeReport[$cd]=time();
				$cdPlusOne=$cd+1;
				$percentCach=round($countInsert*100/$countPlanAmount,2);
				$message[$cd]="calc report on day ".$dayToCalc[$cd]." ".$cdPlusOne." from ".$countDate." amount poes: ".$countPlanAmount." don't cach: ".$countInsert." percentDoNotCach: ".$percentCach;

				$newRecordReport = new ExecutionsCommands;
				$newRecordReport->date=date("Y-m-d");
				$newRecordReport->commands_id=9;
				$newRecordReport->start_time=$startTimeReport[$cd];
				$newRecordReport->end_time=$endTimeReport[$cd];
				$newRecordReport->duration=$endTimeReport[$cd]-$startTimeReport[$cd];
				$newRecordReport->success=$success[$cd];
				$newRecordReport->comment=$message[$cd];
				$newRecordReport->save();

				unset($com);
				unset($ins);//
				unset($arrayToInsert);//
				unset($arrayNePopav);//
				unset($arrayFakt);//
				unset($arraySchedules);//
				unset($arrayPlan);//
				unset($countPlanAmount);
				unset($countInsert);
			}
		}//for ($cd=0; $cd < $countDate; $cd++)
	}
}

?>