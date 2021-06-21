<?php
class SmartStopController extends CController  {
	public function actionRead() 
	{
		$stationsIdFind=Yii::app()->request->getParam('stationsIdFind');

		//шукаемо всі маршрути буквенні назви
		$routesName=Route::model()->findAll();
		foreach ($routesName as $k) {
			if ($k->id!=6) {
				if ($k->id!=44) {
					if ($k->id!=103) {
						$arrayRouteName[$k->id]=$k->name;
					}
				}
			}
		}
		natsort($arrayRouteName);
		//print_r($arrayRouteName);

		$graphsName=Graphs::model()->findAll(array(
			'order'=>'name'));
		foreach ($graphsName as $k) {
			$arrayGraphsName[$k->id]=$k->name;
			$arrayGraphsToRoutes[$k->id]=$k->routes_id;
		}
		$todayTime=date("H:i:s");// поточний час
		$time=explode(":", $todayTime);
		$timeFound1=$time[2]+($time[1]*60)+($time[0]*60*60);//поточний час - 1 година
		$todayDataTimeFrom=date("Y-m-d H:i:s",time()-1200);
		$todayDataTimeTo=date("Y-m-d H:i:s");

		$typeDay=date('w');//тип дня
		if (($typeDay==0) || ($typeDay==6)) {
			$typeDayToday=2;
		}
		else {
			$typeDayToday=1;
		}

		//шукаемо напрямок
		$StationScenario=StationsScenario::model()->with('route','route_directions')->findAll(array(
			'order'=>'t.id'));
		foreach ($StationScenario as $k) {
			$arrayScenario[$k->routes_id][$k->stations_id]=$k->number;
			if ($k->stations_id==$stationsIdFind) {
				$arrayDirections1[$k->routes_id]=$k->route_directions->name;
				$arrayNumberNeedStation[$k->routes_id]=$k->number;
			}
		}

		//print_r($arrayDirections1);

		

		//шукаемо активні скедли на сьогодні
		$schedules=Schedules::model()->with('graph')->findAll(array(
			'order'=>'t.id',
			'condition'=> 'schedule_types_id = :f AND t.status = :st AND histories_id > :hi',
			'params'   =>array(':f'=>$typeDayToday, ':st'=>'yes', ':hi'=>0)));

		foreach ($schedules as $k) {
			$needhistory[$k->histories_id][$k->graph->name]=$k->graph->routes_id;
		}
			   			
		//print_r($needhistory);
		//шукаемо дані в табиці з часами
					$e1=$timeFound1+(30*60);
					$e2=$timeFound1-(10*60);
		$RouteTimeTable=RouteTimeTable::model()->findAll(array(
			'order'=>'time',
			'condition'=> 'stations_id = :f AND time >= :tf AND time<= :tt',
			'params'   =>array(':f'=>$stationsIdFind, ':tf'=>$e2, ':tt'=>$e1)));
		foreach ($RouteTimeTable as $k) {
			$nn[$k->routes_history_id][$k->graphs_number][$k->flights_number]=$k->time;
		}				
		//print_r($nn);	
		foreach ($nn as $idHis => $arg) {
						foreach ($arg as $gn => $value) {
							if (isset($needhistory[$idHis][$gn])) {
								foreach ($value as $flight => $time) {
									$rows[$idHis][$gn]=array(
										'idHis'=>$idHis,
										'routesId'=>$needhistory[$idHis][$gn],
										'routesName'=>$arrayRouteName[$needhistory[$idHis][$gn]],
										'graphsName'=>$gn,
										'flight'=>$flight,
										'time'=>$time
									);
								}	
							}
						}
					}
		//print_r($rows);

		//шукаемо дані з останньої зупинки відмітки
		$MoveOnScheduleTable=MoveOnSchedule::model()->with('schedule','graph')->findAll(array(
			'condition'=> 'datatime >= :tf AND datatime <= :tt',
			'params'   =>array(':tf'=>$todayDataTimeFrom, ':tt'=>$todayDataTimeTo)));
		foreach ($MoveOnScheduleTable as $k) {
			$arrayMoveOnSchedule[$k->schedule->histories_id][$k->graph->name]=array(
									'stations_id'=>$k->stations_id,
									'routes_id'=>$k->routes_id,
									'flight'=>$k->flight_number,
									'time_difference'=>$k->time_difference
								);
		}
		//print_r($arrayMoveOnSchedule);

		foreach ($rows as $idHis => $arg) {
						foreach ($arg as $gn => $value) {
							if (isset($arrayMoveOnSchedule[$idHis][$gn])) {
								$rows[$idHis][$gn]['time_difference']=$arrayMoveOnSchedule[$idHis][$gn]['time_difference'];
								$rows[$idHis][$gn]['stations_id']=$arrayMoveOnSchedule[$idHis][$gn]['stations_id'];
								$rows[$idHis][$gn]['flight_stations_id']=$arrayMoveOnSchedule[$idHis][$gn]['flight'];
								$rows[$idHis][$gn]['number_need_station']=$arrayNumberNeedStation[$value['routesId']];
								$rows[$idHis][$gn]['number_old_station']=$arrayScenario[$value['routesId']][$arrayMoveOnSchedule[$idHis][$gn]['stations_id']];
							}
						}
					}
		//print_r($rows);				
					
					


					
					

					foreach ($rows as $idHis => $arg) {
						foreach ($arg as $gn => $value) {
							$waitOnPlan=round(($rows[$idHis][$gn]['time']-$timeFound1)/60,0);
							if ($waitOnPlan==-0){$waitOnPlan=0;}
							if (!isset($rows[$idHis][$gn]['time_difference'])) {
								
								$rows2[]=array(
									'needCheck'=>'n',
									'waitOnPlan'=>$waitOnPlan,
									'time'=>$rows[$idHis][$gn]['time'],
									'rN'=>$rows[$idHis][$gn]['routesName'],
									'gN'=>$gn,
									'idHis'=>$idHis,
									'routes_id'=>$rows[$idHis][$gn]['routesId']
								);
							}
							if (isset($rows[$idHis][$gn]['time_difference'])) {
								//якщо номер цуму більший за номер ост зловленої і рейси рівні
								if (($rows[$idHis][$gn]['number_need_station']>$rows[$idHis][$gn]['number_old_station']) && ($rows[$idHis][$gn]['flight']==$rows[$idHis][$gn]['flight_stations_id'])) {
									$waitPrognnoz=$waitOnPlan-$rows[$idHis][$gn]['time_difference'];
									
									
										$rows2[]=array(
											'needCheck'=>'nn',
											'waitOnPlan'=>$waitOnPlan,
											'time'=>$rows[$idHis][$gn]['time'],
											'time_difference'=>$rows[$idHis][$gn]['time_difference'],
											'waitPrognnoz'=>$waitPrognnoz,
											'rN'=>$rows[$idHis][$gn]['routesName'],
											'gN'=>$gn,
											'idHis'=>$idHis,
											'routes_id'=>$rows[$idHis][$gn]['routesId']
										);
								}
								//якщо номер цуму менший за номер ост зловленої і рейси більший на одиницю
								else if (($rows[$idHis][$gn]['number_need_station']<=$rows[$idHis][$gn]['number_old_station']) && ($rows[$idHis][$gn]['flight']==$rows[$idHis][$gn]['flight_stations_id']+1)) {
									$waitPrognnoz=$waitOnPlan-$rows[$idHis][$gn]['time_difference'];
									$rows2[]=array(
										'needCheck'=>'nnn',
										'waitOnPlan'=>$waitOnPlan,
										'time'=>$rows[$idHis][$gn]['time'],
										'time_difference'=>$rows[$idHis][$gn]['time_difference'],
										'waitPrognnoz'=>$waitPrognnoz,
										'rN'=>$rows[$idHis][$gn]['routesName'],
										'gN'=>$gn,
										'idHis'=>$idHis,
										'routes_id'=>$rows[$idHis][$gn]['routesId']
									);
								}
								else if (($rows[$idHis][$gn]['number_need_station']==$rows[$idHis][$gn]['number_old_station']) && ($rows[$idHis][$gn]['flight']==$rows[$idHis][$gn]['flight_stations_id'])) {
									$waitPrognnoz=$waitOnPlan-$rows[$idHis][$gn]['time_difference'];
									$rows2[]=array(
										'needCheck'=>'nnnn',
										'waitOnPlan'=>$waitOnPlan,
										'time'=>$rows[$idHis][$gn]['time'],
										'time_difference'=>$rows[$idHis][$gn]['time_difference'],
										'waitPrognnoz'=>$waitPrognnoz,
										'rN'=>$rows[$idHis][$gn]['routesName'],
										'gN'=>$gn,
										'idHis'=>$idHis,
										'routes_id'=>$rows[$idHis][$gn]['routesId']
									);
								}
								else {
									$waitPrognnoz=$waitOnPlan-$rows[$idHis][$gn]['time_difference'];
									$rows2[]=array(
										'needCheck'=>'nnnnn',
										'waitOnPlan'=>$waitOnPlan,
										'time'=>$rows[$idHis][$gn]['time'],
										'time_difference'=>$rows[$idHis][$gn]['time_difference'],
										'waitPrognnoz'=>$waitPrognnoz,
										'rN'=>$rows[$idHis][$gn]['routesName'],
										'gN'=>$gn,
										'idHis'=>$idHis,
										'routes_id'=>$rows[$idHis][$gn]['routesId']
									);
								}
							}

						}
					}
					unset($rows);
					//print_r($rows2);
					for ($i=0; $i <count($rows2) ; $i++) { 
									$timeInHour = intval($rows2[$i]['time']/3600);
									if ($timeInHour<10) {
										$timeInHour="0".$timeInHour;
									}
									$timeInMinutes=intval(($rows2[$i]['time']%3600)/60);
									if ($timeInMinutes<10) {
										$timeInMinutes="0".$timeInMinutes;
									}
									$timeInSecond=$time-($timeInHour*3600)-($timeInMinutes*60);
									if ($timeInSecond<10) {
										$timeInSecond="0".$timeInSecond;
									}
									$rows2[$i]['t2']=$timeInHour.":".$timeInMinutes.":00";
					}
					//print_r($rows2);

					//шукаемо дані з останньої зупинки відмітки
					$todayDataTimeFromSecond=date("Y-m-d H:i:s",time()-60);
					$todayDataTimeToSecond=date("Y-m-d H:i:s");

					$MoveOnMapTable=MoveOnMap::model()->with('route','graph')->findAll(array(
						'condition'=> 'datatime >= :tf AND datatime <= :tt',
						'params'   =>array(':tf'=>$todayDataTimeFromSecond, ':tt'=>$todayDataTimeToSecond)));
					foreach ($MoveOnMapTable as $k) {
						$arrayMoveOnMap[$k->routes_id][$k->graph->name]=array(
									'longitude'=>$k->longitude,
									'latitude'=>$k->latitude);
					}
					//print_r($arrayMoveOnMap);
					$lnltStationNeed=Stations::model()->findByAttributes(array('id'=>$stationsIdFind));
					$longNeed=$k->longitude;
					$latNeed=$k->latitude;
					

					foreach ($rows2 as $key => $value) {
						if ($value['waitPrognnoz']>=5) {
							$rows2[$key]['waitPrognnoz1']=$rows2[$key]['waitPrognnoz']." хв";
						}
						elseif ((isset($value['waitPrognnoz'])) && ($value['waitPrognnoz']<5)) {
							if (isset($arrayMoveOnMap[$rows2[$key]['rN']][$rows2[$key]['gN']])) {
								$rows2[$key]['waitPrognnoz1']=$rows2[$key]['waitPrognnoz']." хв";

								$diffLong=abs($longNeed-$arrayMoveOnMap[$rows2[$key]['rN']][$rows2[$key]['gN']]['longitude']);
								$diffLat=abs($latNeed-$arrayMoveOnMap[$rows2[$key]['rN']][$rows2[$key]['gN']]['latitude']);
								if (($diffLong<0.07) && ($diffLat<0.07)) {
									$rows2[$key]['waitPrognnoz1']="p";
								}
							}
							else {
								$rows2[$key]['waitPrognnoz1']="v";
							}	
						}
						elseif (!isset($value['waitPrognnoz'])) {
							$rows2[$key]['waitPrognnoz1']="v";
						}
					}
					//print_r($rows2);
					for ($i=0; $i < count($rows2); $i++) { 
						if (!isset($rows3[$rows2[$i]['rN']])) {
							$rows3[$rows2[$i]['rN']]=$rows2[$i];

						}
						if (isset($rows3[$rows2[$i]['rN']])) {
							if ($rows2[$i]['waitPrognnoz1']=='p') {
								$rows3[$rows2[$i]['rN']]=$rows2[$i];
							}
							elseif ($rows2[$i]['waitPrognnoz1']!='p') {
								if ($rows3[$rows2[$i]['rN']]['waitPrognnoz1']!='p') {
									if ((isset($rows3[$rows2[$i]['rN']]['waitPrognnoz'])) && ($rows3[$rows2[$i]['rN']]['waitPrognnoz']<0)){
										$rows3[$rows2[$i]['rN']]=$rows2[$i];
									}
									if (($rows3[$rows2[$i]['rN']]['t2']<$todayTime) && ($rows3[$rows2[$i]['rN']]['waitPrognnoz1']=='v')) {
										$rows3[$rows2[$i]['rN']]=$rows2[$i];
									}
									if ($rows3[$rows2[$i]['rN']]['waitPrognnoz1']=="0 хв") {
										$rows3[$rows2[$i]['rN']]['waitPrognnoz1']="1 хв";
									}
								}
							}
						}
					}
					//поточний час в unixtime
					$timeUnixNow=strtotime('now');
					foreach ($arrayRouteName as $routId => $routesName) {
						if (isset($rows3[$routesName])) {
							if ($rows3[$routesName]['waitPrognnoz1']=="v") {
								$rows3[$routesName]['waitPrognnoz1']="---";
								$explT2=explode(":", $rows3[$routesName]['t2']);
								$timeUnixSchedule=strtotime('today')+$explT2[0]*3600+$explT2[1]*60+$explT2[2];
								$rows3[$routesName]['waitPrognnoz1']=ceil(($timeUnixSchedule-$timeUnixNow)/60)." хв";
								$rows3[$routesName]['status']="no in schedule";
							}
							else {
								$rows3[$routesName]['status']="in schedule";
							}
							$rows5[]=array(
								'r'=>$rows3[$routesName]['rN'],
								't1'=>$rows3[$routesName]['waitPrognnoz1'],
								't2'=>$rows3[$routesName]['t2'],
								'n'=>$arrayDirections1[$routId],
								'st'=>$rows3[$routesName]['status']
							);
						}
					}
					unset($rows2);
					unset($rows3);
					//print_r($rows5);


					
					$countRows=count($rows5);
				    $result = array('success' => true, 'rows'=>$rows5, 'totalCount'=>$countRows); 
				    echo json_encode($result);  						
					
}
}
				