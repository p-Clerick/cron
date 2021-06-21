<?php
class ProblemsMoveOnLineController extends Controller  {
	public function actionRead()//на посилання з гет
	{
		$level=Yii::app()->request->getParam('level');
		$nodeName=Yii::app()->request->getParam('recordIdLevel');
		//$typeView=Yii::app()->request->getParam('typeView');

		$typeViewNumber=Yii::app()->request->getParam('typeView');

		if ($typeViewNumber==1) {
			$typeView=Yii::app()->session['AllText'];
		}
		else if ($typeViewNumber==2) {
			$typeView=Yii::app()->session['OnboardDevice'];
		}
		else if ($typeViewNumber==3) {
			$typeView=Yii::app()->session['Clock'];
		}
		else if ($typeViewNumber==4) {
			$typeView=Yii::app()->session['ComplianceSchedule'];
		}
		else if ($typeViewNumber==5) {
			$typeView=Yii::app()->session['Communication'];
		}
		else if ($typeViewNumber==6) {
			$typeView=Yii::app()->session['Map'];
		}
		else if ($typeViewNumber==7) {
			$typeView=Yii::app()->session['Order'];
		}
		else if ($typeViewNumber==8) {
			$typeView=Yii::app()->session['SpeedLevel'];
		}

		if(Yii::app()->user->name != "guest"){
	        $carrier = Yii::app()->user->checkUser(Yii::app()->user);
	    }
	    if ($carrier) {
	    	//cтворюємо дату в таблиці сьогоднішню
			$today = date('Y-m-d');
			$todayTime = time();//-3600;
			$todayPlusOneHour=$today." 02:00:00";
			$st='yes';
			$typeDay=date('w');
			if (($typeDay==0) || ($typeDay==6)) {
				$typeDayToday=2;
				$typeDayTodayText=Yii::app()->session['DayTypeWork'];
			}
			else {
				$typeDayToday=1;
				$typeDayTodayText=Yii::app()->session['DayTypeHollyday'];
			}
			$allGraphs=Graphs::model()->with('route','carrier')->findAll(array(
				'condition'=> 't.status = :t',
				'params'   =>array(':t'=>$st),
				'order'    => 't.id'));
			foreach ($allGraphs as $h) {
				if ($h->carriers_id==$carrier['carrier_id']) {
					$rowsGraphs[$h->id]=array(
						'idGraph'=>$h->id,
						'graphName'=>$h->name,
						'idRoute'=>$h->routes_id,
						'routeName'=>$h->route->name,
						'carrierName'=>$h->carrier->name
					);
					$arrayCarrierName[$h->carriers_id]=$h->carrier->name;
				}
			}
	//print_r($rowsGraphs);
			//шукаемо наряди
			$ord=Orders::model()->with('bort','graph')->findAll(array(
				'condition'=> 't.from >= :t AND t.to <= :f',
				'params'   =>array(':t'=>$today, ':f'=>$today),
				'order'    => 't.id'));
			foreach ($ord as $k) {
				if ($k->graph->carriers_id==$carrier['carrier_id']) {
					$ordersLoadGraphs[$k->graphs_id][]=$k->bort->number."-".$k->bort->state_number;
					$ordersLoadBorts[$k->bort->number."-".$k->bort->state_number][]=$k->graphs_id;
					$loadBortsId[$k->borts_id]=$k->graphs_id;
				}
			}
			// bez naryadu
			foreach ($rowsGraphs as $graphsId => $arrayGraphsParams) {
				if (!isset($ordersLoadGraphs[$graphsId])) {
					$rows[]=array(
						'routeName'=>$arrayGraphsParams['routeName'],
						'graphName'=>$arrayGraphsParams['graphName'],
						'carrierName'=>$arrayGraphsParams['carrierName'],
						'problems'=>Yii::app()->session['OrderExampleProblems1'],
						'problemsName'=>Yii::app()->session['Order']
					);
				}
			}
			foreach ($ordersLoadGraphs as $graphsId => $arrayDoubleGraphs) {
				$countDoubleGraphs=count($arrayDoubleGraphs);
				if ($countDoubleGraphs>=2) {
					for ($i=0; $i < $countDoubleGraphs; $i++) { 
						$bortInsert[$graphsId]=$bortInsert[$graphsId]." ".$arrayDoubleGraphs[$i];
					}
					$rows[]=array(
						'routeName'=>$rowsGraphs[$graphsId]['routeName'],
						'graphName'=>$rowsGraphs[$graphsId]['graphName'],
						'carrierName'=>$rowsGraphs[$graphsId]['carrierName'],
						'bortNameState'=>$bortInsert[$graphsId],
						'problems'=>Yii::app()->session['OrderExampleProblems2Text'].$countDoubleGraphs,
						'problemsName'=>Yii::app()->session['Order']
					);
				}
			}
			foreach ($ordersLoadBorts as $bortsId => $arrayDoubleBorts) {
				$countDoubleBorts=count($arrayDoubleBorts);
				if ($countDoubleBorts>=2) {
					for ($i=0; $i < $countDoubleBorts; $i++) {
						if ($roun[$bortsId]==$rowsGraphs[$arrayDoubleBorts[$i]]['routeName']) {
						}
						if ($roun[$bortsId]!=$rowsGraphs[$arrayDoubleBorts[$i]]['routeName']) {
							$roun[$bortsId]=$roun[$bortsId]." ".$rowsGraphs[$arrayDoubleBorts[$i]]['routeName'];
						} 
						$cn=$rowsGraphs[$arrayDoubleBorts[$i]]['carrierName'];
						$grn[$bortsId]=$grn[$bortsId]." ".$rowsGraphs[$arrayDoubleBorts[$i]]['graphName'];
					}
					$rows[]=array(
						'routeName'=>$roun[$bortsId],
						'graphName'=>$grn[$bortsId],
						'bortNameState'=>$bortsId,
						'carrierName'=>$cn,
						'problems'=>Yii::app()->session['OrderExampleProblems3Text'].$countDoubleBorts,
						'problemsName'=>Yii::app()->session['Order']
					);
				}
			}

			$allMove=MoveOnMap::model()->with('graph','route','bort','schedule')->findAll(array(
				'condition'=> 'datatime >= :t',
				'params'   =>array(':t'=>$todayPlusOneHour),
				'order'    => 't.id'));
			foreach ($allMove as $k) {
				
				if ($k->graph->carriers_id==$carrier['carrier_id']) {
					if ($k->speed>60){
						$rows[]=array(
							'routeName'=>$rowsGraphs[$k->graphs_id]['routeName'],
							'graphName'=>$rowsGraphs[$k->graphs_id]['graphName'],
							'carrierName'=>$rowsGraphs[$k->graphs_id]['carrierName'],
							'bortNameState'=>$k->bort->number."-".$k->bort->state_number,
							'problems'=>Yii::app()->session['MoveOnMapProblemsSpeedText'].": ".$k->speed." ".Yii::app()->session['KmPerHoursText'],
							'problemsName'=>Yii::app()->session['SpeedLevel']
						);
					}
					//їздить за розкладом але без наряду
					if (!isset($loadBortsId[$k->borts_id])) {
						$rows[]=array(
							'routeName'=>$rowsGraphs[$k->graphs_id]['routeName'],
							'graphName'=>$rowsGraphs[$k->graphs_id]['graphName'],
							'carrierName'=>$rowsGraphs[$k->graphs_id]['carrierName'],
							'bortNameState'=>$k->bort->number."-".$k->bort->state_number,
							'problems'=>Yii::app()->session['OrderExampleProblems4Text'],
							'problemsName'=>Yii::app()->session['Order']
						);
					}
				
					//тип дня
					$typeDayMove=$k->schedule->schedule_types_id;
					if (($typeDayMove!=$typeDayToday)  && ($k->schedules_id!=0)){
						$rows[]=array(
							'routeName'=>$rowsGraphs[$k->graphs_id]['routeName'],
							'graphName'=>$rowsGraphs[$k->graphs_id]['graphName'],
							'carrierName'=>$rowsGraphs[$k->graphs_id]['carrierName'],
							'bortNameState'=>$k->bort->number."-".$k->bort->state_number,
							'problems'=>Yii::app()->session['OrderExampleProblems5Text'].$typeDayTodayText,
							'problemsName'=>Yii::app()->session['Order']
						);
					}
					//not in schedule
					if (!isset($k->time_difference)) {
						$schedfind=Yii::app()->db->createCommand("SELECT time,min(id) from schedule_times where schedules_id='".$k->schedules_id."'")->queryAll();
						foreach ($schedfind as $key => $value) {
							$t=new Time($value['time']);
							$tt=$t->getFormattedTime();
						}
						$rows[]=array(
							'routeName'=>$rowsGraphs[$k->graphs_id]['routeName'],
							'graphName'=>$rowsGraphs[$k->graphs_id]['graphName'],
							'bortNameState'=>$k->bort->number."-".$k->bort->state_number,
							'carrierName'=>$rowsGraphs[$k->graphs_id]['carrierName'],
							'problems'=>Yii::app()->session['MapDescription4'].$tt,
							'problemsName'=>Yii::app()->session['MapDescription5']
						);
					}
					//велика різниця в часі
					if (isset($k->time_difference)) {
						if (($k->time_difference>3) || ($k->time_difference<-3)) {
							$rows[]=array(
								'routeName'=>$rowsGraphs[$k->graphs_id]['routeName'],
								'graphName'=>$rowsGraphs[$k->graphs_id]['graphName'],
								'carrierName'=>$rowsGraphs[$k->graphs_id]['carrierName'],
								'bortNameState'=>$k->bort->number."-".$k->bort->state_number,
								'problems'=>Yii::app()->session['ComplianceScheduleDescription1'].$k->time_difference." ".Yii::app()->session['MinutesText'],
								'problemsName'=>Yii::app()->session['ComplianceSchedule']
							);
						}
					}
				
					$timeDifference=round(($todayTime-(strtotime($k->datatime)))/60,0);
					//не має передачі
					if ($timeDifference>2) {
						$rows[]=array(
								'routeName'=>$rowsGraphs[$k->graphs_id]['routeName'],
								'graphName'=>$rowsGraphs[$k->graphs_id]['graphName'],
								'carrierName'=>$rowsGraphs[$k->graphs_id]['carrierName'],
								'bortNameState'=>$k->bort->number."-".$k->bort->state_number,
								'problems'=>Yii::app()->session['CommunicationExampleProblems1'].$timeDifference." ".Yii::app()->session['MinutesText'],
								'problemsName'=>Yii::app()->session['Communication']
							);
					}
					//годинник на перед
					if ($timeDifference<-2) {
						$timeDifference=-$timeDifference;
						$rows[]=array(
								'routeName'=>$rowsGraphs[$k->graphs_id]['routeName'],
								'graphName'=>$rowsGraphs[$k->graphs_id]['graphName'],
								'bortNameState'=>$k->bort->number."-".$k->bort->state_number,
								'carrierName'=>$rowsGraphs[$k->graphs_id]['carrierName'],
								'problems'=>Yii::app()->session['ClockExampleProblems1'].$timeDifference." ".Yii::app()->session['MinutesText']
								,
								'problemsName'=>Yii::app()->session['Clock']
							);
					}
					// низький рівень заряду
					if ($k->charge<20) {
						
									$rows[]=array(
										'routeName'=>$rowsGraphs[$k->graphs_id]['routeName'],
										'graphName'=>$rowsGraphs[$k->graphs_id]['graphName'],
										'bortNameState'=>$k->bort->number."-".$k->bort->state_number,
										'carrierName'=>$rowsGraphs[$k->graphs_id]['carrierName'],
										'problems'=>Yii::app()->session['OnboardDeviceExampleProblems1'].$k->charge,
										'problemsName'=>Yii::app()->session['OnboardDevice']
									);
							
						
					}
					// два борти на одному графіку
					$moveDoubleGraphs[$k->graphs_id][]=array(
						'bortId'=>$k->borts_id,
						'bortName'=>$k->bort->number,
						'bortNameState'=>$k->bort->state_number,
						'graphId'=>$k->graphs_id,
						'graphName'=>$k->graph->name,
						'routeId'=>$k->routes_id,
						'routeName'=>$k->route->name
					);
					//один борт на різних графіках
					$moveDoubleBorts[$k->borts_id][]=array(
						'bortId'=>$k->borts_id,
						'bortName'=>$k->bort->number,
						'bortNameState'=>$k->bort->state_number,
						'graphId'=>$k->graphs_id,
						'graphName'=>$k->graph->name,
						'routeId'=>$k->routes_id,
						'routeName'=>$k->route->name
					);
					//перелік тих що відображаються на карті
					$allSee[$k->graphs_id]=$k->graphs_id;
				}	
			}
			//перелік тих що відображаються на карті
			foreach ($rowsGraphs as $graphsId => $arrayGraphsParams) {
				if (!isset($allSee[$graphsId])) {
					$rows[]=array(
						'routeName'=>$arrayGraphsParams['routeName'],
						'graphName'=>$arrayGraphsParams['graphName'],
						'carrierName'=>$arrayGraphsParams['carrierName'],
						'bortNameState'=>$ordersLoadGraphs[$graphsId],
						'problems'=>Yii::app()->session['MapExampleProblems2'],
						'problemsName'=>Yii::app()->session['Map']
					);
				}
			}
				$allSee[$k->graphs_id]=$k->graphs_id;
			if (isset($moveDoubleBorts)) {
				foreach ($moveDoubleBorts as $key => $value) {
					if (count($value)>=2) {
						for ($i=0; $i <count($value) ; $i++) { 

							$routeNameDGb[$key]=$routeNameDGb[$key]." ".$value[$i]['routeName'];
							$graphNameDGb[$key]=$graphNameDGb[$key]." ".$value[$i]['graphName'];
							$bortNameDGb[$key]=$bortNameDGb[$key]." ".$value[$i]['bortName']."-".$value[$i]['bortNameState'];
						}
						$rows[]=array(
							'routeName'=>$routeNameDGb[$key],
							'graphName'=>$graphNameDGb[$key],
							'bortNameState'=>$bortNameDGb[$key],
							'carrierName'=>$rowsGraphs[$value[0]['graphId']]['carrierName'],
							'problems'=>Yii::app()->session['OneBortOn']." ".count($value)." ".Yii::app()->session['InGrafiks'],
							'problemsName'=>Yii::app()->session['MapDubbing']
						);				
					}
				}
			}
			if (isset($moveDoubleGraphs)) {	
				foreach ($moveDoubleGraphs as $key => $value) {
					if (count($value)>=2) {
						for ($i=0; $i <count($value) ; $i++) {
							if ($routeNameDG[$key]==$value[$i]['routeName']) {
							}
							if ($routeNameDG[$key]!=$value[$i]['routeName']) {
								$routeNameDG[$key]=$value[$i]['routeName'];
							}
							if ($graphNameDG[$key]==$value[$i]['routeName']) {
							}
							if ($graphNameDG[$key]!=$value[$i]['routeName']) {
								$graphNameDG[$key]=$value[$i]['graphName'];
							}
							$bortNameDG[$key]=$bortNameDG[$key]." ".$value[$i]['bortName']."-".$value[$i]['bortNameState'];
						}
						$rows[]=array(
							'routeName'=>$routeNameDG[$key],
							'graphName'=>$graphNameDG[$key],
							'bortNameState'=>$bortNameDG[$key],
							'carrierName'=>$rowsGraphs[$key]['carrierName'],
							'problems'=>Yii::app()->session['OnOneGrafik'] ." ".count($value)." ".Yii::app()->session['BortsIn'] ,
							'problemsName'=>Yii::app()->session['MapDubbing']
						);
					}
				}
			}
			$moveOnSched=MoveOnSchedule::model()->with('route','graph','bort')->findAll(array(
				'condition'=> 'datatime >= :t',
				'params'   =>array(':t'=>$todayPlusOneHour),
				'order'    => 't.id'));
			foreach ($moveOnSched as $k) {
				if ($k->route->carriers_id==$carrier['carriers_id']) {
					$arrayMoveOnSched[$k->route->name][$k->graph->name]=$k->stations_id;
					$arrayMoveOnSchedCheckClock[$k->route->name][$k->graph->name]=array(
						'arrival_plan'=>$k->arrival_plan,
						'arrival_fakt'=>$k->datatime,
						'diff'=>$k->time_difference,
						'bort'=>$k->bort->number." - ".$k->bort->state_number,
						'gid'=>$k->graphs_id
					);
				}
				
			}
			$stationsScenario=StationsScenario::model()->with('route_directions','route')->findAll();
			foreach ($stationsScenario as $k) {
				$arrayRouteDirections[$k->route->name][$k->stations_id]=$k->route_directions->name;
			}
			if (isset($arrayMoveOnSchedCheckClock)) {
				foreach ($arrayMoveOnSchedCheckClock as $rid => $ridArray) {
					foreach ($ridArray as $gid => $gidArray) {
						$timePlanMoveOnSched=$gidArray['arrival_plan']+strtotime($today);
						$timeFaktMoveOnSched=strtotime($gidArray['arrival_fakt']);
						$timePlanMinusFakt=round(($timePlanMoveOnSched-$timeFaktMoveOnSched)/60);
						if (($timePlanMinusFakt!=$gidArray['diff']) && ($timePlanMinusFakt!=$gidArray['diff']+1) && ($timePlanMinusFakt!=$gidArray['diff']-1)) {
							$formatTimePlan= new Time($gidArray['arrival_plan']);
							$formatTimeFakt=explode(" ", $gidArray['arrival_fakt']);

							$rows[]=array(
								'routeName'=>$rid,
								'graphName'=>$gid,
								'bortNameState'=>$gidArray['bort'],
								'carrierName'=>$rowsGraphs[$gidArray['gid']]['carrierName'],
								'problems'=> $formatTimePlan->getFormattedTime()." - ".$formatTimeFakt[1]." != ".$gidArray['diff'] ,
								'problemsName'=>Yii::app()->session['Clock']
							);
						}
					}
				}
			}

			$countRows=count($rows);
			
			function sortbyName ($a,$b) {
				if ( $a['routeName'] == $b['routeName'] ) {
					if ( $a['graphName'] == $b['graphName'] ) {
						return 0;
					}
					if ( $a['graphName'] > $b['graphName'] ) {
						return 1;
					}
					if ( $a['graphName'] < $b['graphName'] ) {
						return -1;
					}
				}
				if ( $a['routeName'] > $b['routeName'] ) {
					return 1;
				}
				if ( $a['routeName'] < $b['routeName'] ) {
					return -1;
				}
			}
			if (isset($rows)) {
				usort($rows, "sortbyName");
			}
			for ($i=0; $i < $countRows; $i++) { 
				$rows[$i]['npp']=$i+1;
				$rows[$i]['direction']=$arrayRouteDirections[$rows[$i]['routeName']][$arrayMoveOnSched[$rows[$i]['routeName']][$rows[$i]['graphName']]];
			}
			if ($typeView==Yii::app()->session['AllText'])	{
				if ($level==0) {
					$rowsNeed=$rows;
				}
				if ($level==1) {
					$rowsNeed=$rows;
				}
				if ($level==2) {
					for ($i=0; $i < $countRows; $i++) {
						if ($rows[$i]['routeName']==$nodeName) {
							$rowsNeed[]=$rows[$i];
						}
					}
				}
				if ($level==3) {
					for ($i=0; $i < $countRows; $i++) {
						$nodeNameExplode=explode("***", $nodeName);
						if ($rows[$i]['routeName']==$nodeNameExplode[0]) {
							if ($rows[$i]['graphName']==$nodeNameExplode[1]) {
								$rowsNeed[]=$rows[$i];
							}
						}
					}
				}
			}
			if ($typeView!=Yii::app()->session['AllText'])	{
				if ($typeView=='')	{
					if ($level==0) {
						$rowsNeed=$rows;
					}
					if ($level==1) {
						$rowsNeed=$rows;
					}
					if ($level==2) {
						for ($i=0; $i < $countRows; $i++) {
							if ($rows[$i]['routeName']==$nodeName) {
								$rowsNeed[]=$rows[$i];
							}
						}
					}
					if ($level==3) {
						for ($i=0; $i < $countRows; $i++) {
							$nodeNameExplode=explode("***", $nodeName);
							if ($rows[$i]['routeName']==$nodeNameExplode[0]) {
								if ($rows[$i]['graphName']==$nodeNameExplode[1]) {
									$rowsNeed[]=$rows[$i];
								}
							}
						}
					}
				}
				else {
					for ($i=0; $i < $countRows; $i++) { 
						$problemsExplode=explode(" ", $rows[$i]['problemsName']);
						$typeViewExplode=explode(" ", $typeView);
						if ($problemsExplode[0]==$typeViewExplode[0]) {
							$rowsFiltr[]=$rows[$i];
						}
					}
					$countRowsFiltr=count($rowsFiltr);
					if ($level==0) {
						$rowsNeed=$rowsFiltr;
					}
					if ($level==1) {
						$rowsNeed=$rowsFiltr;
					}
					if ($level==2) {
						for ($i=0; $i < $countRowsFiltr; $i++) {
							if ($rowsFiltr[$i]['routeName']==$nodeName) {
								$rowsNeed[]=$rowsFiltr[$i];
							}
						}
					}
					if ($level==3) {
						for ($i=0; $i < $countRowsFiltr; $i++) {
							$nodeNameExplode=explode("***", $nodeName);
							if ($rowsFiltr[$i]['routeName']==$nodeNameExplode[0]) {
								if ($rowsFiltr[$i]['graphName']==$nodeNameExplode[1]) {
									$rowsNeed[]=$rowsFiltr[$i];
								}
							}
						}
					}
				}
			}
			$countRowsNeed=count($rowsNeed);
			for ($i=0; $i < $countRowsNeed; $i++) {
				$rt[$rowsNeed[$i]['problemsName']]=$rt[$rowsNeed[$i]['problemsName']]+1;
				$nameProblems[$rowsNeed[$i]['problemsName']]=$rowsNeed[$i]['problemsName'];
			}
			if (isset($nameProblems)) {
				natsort($nameProblems);
				foreach ($nameProblems as $key => $value) {
					$sumResultNeed=$sumResultNeed." ".$key." - ".$rt[$value]." ".Yii::app()->session['PiecesText']."</br>";
				}
				$nppNeed=1;
				for ($i=0; $i < $countRowsNeed; $i++) { 
					$rowsNeed[$i]['npp']=$nppNeed;
					$nppNeed=$nppNeed+1;
				}
			}
			
			
			$result = array('success' => true, 'rows'=>$rowsNeed, 'totalCount'=>$countRowsNeed, 'sumResult'=>$sumResultNeed); 
			echo CJSON::encode($result);
	    }
	    else {
			//cтворюємо дату в таблиці сьогоднішню
			$today = date('Y-m-d');
			$todayTime = time();//*-3600;
			$todayPlusOneHour=$today." 02:00:00";
			$st='yes';
			$typeDay=date('w');
			if (($typeDay==0) || ($typeDay==6)) {
				$typeDayToday=2;
				$typeDayTodayText=Yii::app()->session['DayTypeWork'];
			}
			else {
				$typeDayToday=1;
				$typeDayTodayText=Yii::app()->session['DayTypeHollyday'];
			}
			$allGraphs=Graphs::model()->with('route','carrier')->findAll(array(
				'condition'=> 't.status = :t',
				'params'   =>array(':t'=>$st),
				'order'    => 't.id'));
			foreach ($allGraphs as $h) {
				$rowsGraphs[$h->id]=array(
					'idGraph'=>$h->id,
					'graphName'=>$h->name,
					'idRoute'=>$h->routes_id,
					'routeName'=>$h->route->name,
					'carrierName'=>$h->carrier->name
				);
				$arrayCarrierName[$h->carriers_id]=$h->carrier->name;
			}
	//print_r($rowsGraphs);
			//шукаемо наряди
			$ord=Orders::model()->with('bort')->findAll(array(
				'condition'=> 't.from >= :t AND t.to <= :f',
				'params'   =>array(':t'=>$today, ':f'=>$today),
				'order'    => 't.id'));
			foreach ($ord as $k) {
				$ordersLoadGraphs[$k->graphs_id][]=$k->bort->number."-".$k->bort->state_number;
				$ordersLoadBorts[$k->bort->number."-".$k->bort->state_number][]=$k->graphs_id;
				$loadBortsId[$k->borts_id]=$k->graphs_id;
			}
			// bez naryadu
			foreach ($rowsGraphs as $graphsId => $arrayGraphsParams) {
				if (!isset($ordersLoadGraphs[$graphsId])) {
					$rows[]=array(
						'routeName'=>$arrayGraphsParams['routeName'],
						'graphName'=>$arrayGraphsParams['graphName'],
						'carrierName'=>$arrayGraphsParams['carrierName'],
						'problems'=>Yii::app()->session['OrderExampleProblems6'],
						'problemsName'=>Yii::app()->session['Order']
					);
				}
			}
			foreach ($ordersLoadGraphs as $graphsId => $arrayDoubleGraphs) {
				$countDoubleGraphs=count($arrayDoubleGraphs);
				if ($countDoubleGraphs>=2) {
					for ($i=0; $i < $countDoubleGraphs; $i++) { 
						$bortInsert[$graphsId]=$bortInsert[$graphsId]." ".$arrayDoubleGraphs[$i];
					}
					$rows[]=array(
						'routeName'=>$rowsGraphs[$graphsId]['routeName'],
						'graphName'=>$rowsGraphs[$graphsId]['graphName'],
						'carrierName'=>$rowsGraphs[$graphsId]['carrierName'],
						'bortNameState'=>$bortInsert[$graphsId],
						'problems'=>Yii::app()->session['OrderExampleProblems2Text'].$countDoubleGraphs,
						'problemsName'=>Yii::app()->session['Order']
					);
				}
			}
			foreach ($ordersLoadBorts as $bortsId => $arrayDoubleBorts) {
				$countDoubleBorts=count($arrayDoubleBorts);
				if ($countDoubleBorts>=2) {
					for ($i=0; $i < $countDoubleBorts; $i++) {
						if ($roun[$bortsId]==$rowsGraphs[$arrayDoubleBorts[$i]]['routeName']) {
						}
						if ($roun[$bortsId]!=$rowsGraphs[$arrayDoubleBorts[$i]]['routeName']) {
							$roun[$bortsId]=$roun[$bortsId]." ".$rowsGraphs[$arrayDoubleBorts[$i]]['routeName'];
						} 
						$cn=$rowsGraphs[$arrayDoubleBorts[$i]]['carrierName'];
						$grn[$bortsId]=$grn[$bortsId]." ".$rowsGraphs[$arrayDoubleBorts[$i]]['graphName'];
					}
					$rows[]=array(
						'routeName'=>$roun[$bortsId],
						'graphName'=>$grn[$bortsId],
						'bortNameState'=>$bortsId,
						'carrierName'=>$cn,
						'problems'=>Yii::app()->session['OrderExampleProblems3Text'].$countDoubleBorts,
						'problemsName'=>Yii::app()->session['Order']
					);
				}
			}

			$allMove=MoveOnMap::model()->with('graph','route','bort','schedule')->findAll(array(
				'condition'=> 'datatime >= :t',
				'params'   =>array(':t'=>$todayPlusOneHour),
				'order'    => 't.id'));
			foreach ($allMove as $k) {
				if ($k->speed>60){
					$rows[]=array(
						'routeName'=>$rowsGraphs[$k->graphs_id]['routeName'],
						'graphName'=>$rowsGraphs[$k->graphs_id]['graphName'],
						'carrierName'=>$rowsGraphs[$k->graphs_id]['carrierName'],
						'bortNameState'=>$k->bort->number."-".$k->bort->state_number,
						'problems'=>Yii::app()->session['MoveOnMapProblemsSpeedText'].": ".$k->speed." ".Yii::app()->session['KmPerHoursText'],
						'problemsName'=>Yii::app()->session['SpeedLevel']
					);
				}
				//їздить за розкладом але без наряду
				if (!isset($loadBortsId[$k->borts_id])) {
					$rows[]=array(
						'routeName'=>$rowsGraphs[$k->graphs_id]['routeName'],
						'graphName'=>$rowsGraphs[$k->graphs_id]['graphName'],
						'carrierName'=>$rowsGraphs[$k->graphs_id]['carrierName'],
						'bortNameState'=>$k->bort->number."-".$k->bort->state_number,
						'problems'=>Yii::app()->session['OrderExampleProblems4Text'],
						'problemsName'=>Yii::app()->session['Order']
					);
				}
			
				//тип дня
				$typeDayMove=$k->schedule->schedule_types_id;
				if (($typeDayMove!=$typeDayToday)  && ($k->schedules_id!=0)){
					$rows[]=array(
						'routeName'=>$rowsGraphs[$k->graphs_id]['routeName'],
						'graphName'=>$rowsGraphs[$k->graphs_id]['graphName'],
						'carrierName'=>$rowsGraphs[$k->graphs_id]['carrierName'],
						'bortNameState'=>$k->bort->number."-".$k->bort->state_number,
						'problems'=>Yii::app()->session['OrderExampleProblems5Text'].$typeDayTodayText,
						'problemsName'=>Yii::app()->session['Order']
					);
				}
				//not in schedule
				if (!isset($k->time_difference)) {
					$schedfind=Yii::app()->db->createCommand("SELECT time,min(id) from schedule_times where schedules_id='".$k->schedules_id."'")->queryAll();
					foreach ($schedfind as $key => $value) {
						$t=new Time($value['time']);
						$tt=$t->getFormattedTime();
					}
					$rows[]=array(
						'routeName'=>$rowsGraphs[$k->graphs_id]['routeName'],
						'graphName'=>$rowsGraphs[$k->graphs_id]['graphName'],
						'bortNameState'=>$k->bort->number."-".$k->bort->state_number,
						'carrierName'=>$rowsGraphs[$k->graphs_id]['carrierName'],
						'problems'=>Yii::app()->session['MapDescription4'].$tt,
						'problemsName'=>Yii::app()->session['MapDescription5']
					);
				}
				//велика різниця в часі
				if (isset($k->time_difference)) {
					if (($k->time_difference>3) || ($k->time_difference<-3)) {
						$rows[]=array(
							'routeName'=>$rowsGraphs[$k->graphs_id]['routeName'],
							'graphName'=>$rowsGraphs[$k->graphs_id]['graphName'],
							'carrierName'=>$rowsGraphs[$k->graphs_id]['carrierName'],
							'bortNameState'=>$k->bort->number."-".$k->bort->state_number,
							'problems'=>Yii::app()->session['ComplianceScheduleDescription1'].$k->time_difference." ".Yii::app()->session['MinutesText'],
							'problemsName'=>Yii::app()->session['ComplianceSchedule']
						);
					}
				}
				
				$timeDifference=round(($todayTime-(strtotime($k->datatime)))/60,0);
				//не має передачі
				if ($timeDifference>5) {
					$rows[]=array(
							'routeName'=>$rowsGraphs[$k->graphs_id]['routeName'],
							'graphName'=>$rowsGraphs[$k->graphs_id]['graphName'],
							'carrierName'=>$rowsGraphs[$k->graphs_id]['carrierName'],
							'bortNameState'=>$k->bort->number."-".$k->bort->state_number,
							'problems'=>Yii::app()->session['CommunicationExampleProblems1'].$timeDifference." ".Yii::app()->session['MinutesText'],
							'problemsName'=>Yii::app()->session['Communication']
						);
				}
				//годинник на перед
				if ($timeDifference<-2) {
					$timeDifference=-$timeDifference;
					$rows[]=array(
							'routeName'=>$rowsGraphs[$k->graphs_id]['routeName'],
							'graphName'=>$rowsGraphs[$k->graphs_id]['graphName'],
							'bortNameState'=>$k->bort->number."-".$k->bort->state_number,
							'carrierName'=>$rowsGraphs[$k->graphs_id]['carrierName'],
							'problems'=>Yii::app()->session['ClockExampleProblems1'].$timeDifference." ".Yii::app()->session['MinutesText']
							,
							'problemsName'=>Yii::app()->session['Clock']
						);
				}
				// низький рівень заряду
				if ($k->charge<20) {
					
								$rows[]=array(
									'routeName'=>$rowsGraphs[$k->graphs_id]['routeName'],
									'graphName'=>$rowsGraphs[$k->graphs_id]['graphName'],
									'bortNameState'=>$k->bort->number."-".$k->bort->state_number,
									'carrierName'=>$rowsGraphs[$k->graphs_id]['carrierName'],
									'problems'=>Yii::app()->session['OnboardDeviceExampleProblems1'].$k->charge,
									'problemsName'=>Yii::app()->session['OnboardDevice']
								);
						
					
				}
				// два борти на одному графіку
				$moveDoubleGraphs[$k->graphs_id][]=array(
					'bortId'=>$k->borts_id,
					'bortName'=>$k->bort->number,
					'bortNameState'=>$k->bort->state_number,
					'graphId'=>$k->graphs_id,
					'graphName'=>$k->graph->name,
					'routeId'=>$k->routes_id,
					'routeName'=>$k->route->name
				);
				//один борт на різних графіках
				$moveDoubleBorts[$k->borts_id][]=array(
					'bortId'=>$k->borts_id,
					'bortName'=>$k->bort->number,
					'bortNameState'=>$k->bort->state_number,
					'graphId'=>$k->graphs_id,
					'graphName'=>$k->graph->name,
					'routeId'=>$k->routes_id,
					'routeName'=>$k->route->name
				);
				//перелік тих що відображаються на карті
				$allSee[$k->graphs_id]=$k->graphs_id;
			}
			//перелік тих що відображаються на карті
			foreach ($rowsGraphs as $graphsId => $arrayGraphsParams) {
				if (!isset($allSee[$graphsId])) {
					$rows[]=array(
						'routeName'=>$arrayGraphsParams['routeName'],
						'graphName'=>$arrayGraphsParams['graphName'],
						'carrierName'=>$arrayGraphsParams['carrierName'],
						'bortNameState'=>$ordersLoadGraphs[$graphsId],
						'problems'=>Yii::app()->session['MapExampleProblems2'],
						'problemsName'=>Yii::app()->session['Map']
					);
				}
			}
				$allSee[$k->graphs_id]=$k->graphs_id;
			if (isset($moveDoubleBorts)) {
				foreach ($moveDoubleBorts as $key => $value) {
					if (count($value)>=2) {
						for ($i=0; $i <count($value) ; $i++) { 

							$routeNameDGb[$key]=$routeNameDGb[$key]." ".$value[$i]['routeName'];
							$graphNameDGb[$key]=$graphNameDGb[$key]." ".$value[$i]['graphName'];
							$bortNameDGb[$key]=$bortNameDGb[$key]." ".$value[$i]['bortName']."-".$value[$i]['bortNameState'];
						}
						$rows[]=array(
							'routeName'=>$routeNameDGb[$key],
							'graphName'=>$graphNameDGb[$key],
							'bortNameState'=>$bortNameDGb[$key],
							'carrierName'=>$rowsGraphs[$value[0]['graphId']]['carrierName'],
							'problems'=>Yii::app()->session['OneBortOn']." ".count($value)." ".Yii::app()->session['InGrafiks'],
							'problemsName'=>Yii::app()->session['MapDubbing']
						);				
					}
				}
			}
			if (isset($moveDoubleGraphs)) {	
				foreach ($moveDoubleGraphs as $key => $value) {
					if (count($value)>=2) {
						for ($i=0; $i <count($value) ; $i++) {
							if ($routeNameDG[$key]==$value[$i]['routeName']) {
							}
							if ($routeNameDG[$key]!=$value[$i]['routeName']) {
								$routeNameDG[$key]=$value[$i]['routeName'];
							}
							if ($graphNameDG[$key]==$value[$i]['routeName']) {
							}
							if ($graphNameDG[$key]!=$value[$i]['routeName']) {
								$graphNameDG[$key]=$value[$i]['graphName'];
							}
							$bortNameDG[$key]=$bortNameDG[$key]." ".$value[$i]['bortName']."-".$value[$i]['bortNameState'];
						}
						$rows[]=array(
							'routeName'=>$routeNameDG[$key],
							'graphName'=>$graphNameDG[$key],
							'bortNameState'=>$bortNameDG[$key],
							'carrierName'=>$rowsGraphs[$key]['carrierName'],
							'problems'=>Yii::app()->session['OnOneGrafik'] ." ".count($value)." ".Yii::app()->session['BortsIn'] ,
							'problemsName'=>Yii::app()->session['MapDubbing']
						);
					}
				}
			}

			$moveOnSched=MoveOnSchedule::model()->with('route','graph','bort')->findAll(array(
				'condition'=> 'datatime >= :t',
				'params'   =>array(':t'=>$todayPlusOneHour),
				'order'    => 't.id'));
			foreach ($moveOnSched as $k) {
				$arrayMoveOnSched[$k->route->name][$k->graph->name]=$k->stations_id;
				$arrayMoveOnSchedCheckClock[$k->route->name][$k->graph->name]=array(
					'arrival_plan'=>$k->arrival_plan,
					'arrival_fakt'=>$k->datatime,
					'diff'=>$k->time_difference,
					'bort'=>$k->bort->number." - ".$k->bort->state_number,
					'gid'=>$k->graphs_id
				);
			}
			$stationsScenario=StationsScenario::model()->with('route_directions','route')->findAll();
			foreach ($stationsScenario as $k) {
				$arrayRouteDirections[$k->route->name][$k->stations_id]=$k->route_directions->name;
			}
			if (isset($arrayMoveOnSchedCheckClock)) {
				foreach ($arrayMoveOnSchedCheckClock as $rid => $ridArray) {
					foreach ($ridArray as $gid => $gidArray) {
						$timePlanMoveOnSched=$gidArray['arrival_plan']+strtotime($today);
						$timeFaktMoveOnSched=strtotime($gidArray['arrival_fakt']);
						$timePlanMinusFakt=round(($timePlanMoveOnSched-$timeFaktMoveOnSched)/60);
						if (($timePlanMinusFakt!=$gidArray['diff']) && ($timePlanMinusFakt!=$gidArray['diff']+1) && ($timePlanMinusFakt!=$gidArray['diff']-1)) {
							$formatTimePlan= new Time($gidArray['arrival_plan']);
							$formatTimeFakt=explode(" ", $gidArray['arrival_fakt']);
							$rows[]=array(
								'routeName'=>$rid,
								'graphName'=>$gid,
								'bortNameState'=>$gidArray['bort'],
								'carrierName'=>$rowsGraphs[$gidArray['gid']]['carrierName'],
								'problems'=> $formatTimePlan->getFormattedTime()." - ".$formatTimeFakt[1]." != ".$gidArray['diff'] ,
								'problemsName'=>Yii::app()->session['Clock']
							);
						}
					}
				}
			}
			$countRows=count($rows);
			
			function sortbyName ($a,$b) {
				if ( $a['routeName'] == $b['routeName'] ) {
					if ( $a['graphName'] == $b['graphName'] ) {
						return 0;
					}
					if ( $a['graphName'] > $b['graphName'] ) {
						return 1;
					}
					if ( $a['graphName'] < $b['graphName'] ) {
						return -1;
					}
				}
				if ( $a['routeName'] > $b['routeName'] ) {
					return 1;
				}
				if ( $a['routeName'] < $b['routeName'] ) {
					return -1;
				}
			}
			if (isset($rows)) {
				usort($rows, "sortbyName");
			}
			for ($i=0; $i < $countRows; $i++) { 
				$rows[$i]['npp']=$i+1;
				$rows[$i]['direction']=$arrayRouteDirections[$rows[$i]['routeName']][$arrayMoveOnSched[$rows[$i]['routeName']][$rows[$i]['graphName']]];
			}	
			if ($typeView==Yii::app()->session['AllText'])	{
				if ($level==0) {
					$rowsNeed=$rows;
				}
				if ($level==1) {
					$rowsNeed=$rows;
				}
				if ($level==2) {
					for ($i=0; $i < $countRows; $i++) {
						if ($rows[$i]['routeName']==$nodeName) {
							$rowsNeed[]=$rows[$i];
						}
					}
				}
				if ($level==3) {
					for ($i=0; $i < $countRows; $i++) {
						$nodeNameExplode=explode("***", $nodeName);
						if ($rows[$i]['routeName']==$nodeNameExplode[0]) {
							if ($rows[$i]['graphName']==$nodeNameExplode[1]) {
								$rowsNeed[]=$rows[$i];
							}
						}
					}
				}
			}

			if ($typeView!=Yii::app()->session['AllText'])	{
				if ($typeView=='')	{
					if ($level==0) {
						$rowsNeed=$rows;
					}
					if ($level==1) {
						$rowsNeed=$rows;
					}
					if ($level==2) {
						for ($i=0; $i < $countRows; $i++) {
							if ($rows[$i]['routeName']==$nodeName) {
								$rowsNeed[]=$rows[$i];
							}
						}
					}
					if ($level==3) {
						for ($i=0; $i < $countRows; $i++) {
							$nodeNameExplode=explode("***", $nodeName);
							if ($rows[$i]['routeName']==$nodeNameExplode[0]) {
								if ($rows[$i]['graphName']==$nodeNameExplode[1]) {
									$rowsNeed[]=$rows[$i];
								}
							}
						}
					}
				}
				else {
					for ($i=0; $i < $countRows; $i++) { 
						$problemsExplode=explode(" ", $rows[$i]['problemsName']);
						$typeViewExplode=explode(" ", $typeView);
						if ($problemsExplode[0]==$typeViewExplode[0]) {
							$rowsFiltr[]=$rows[$i];
						}
					}
					$countRowsFiltr=count($rowsFiltr);
					if ($level==0) {
						$rowsNeed=$rowsFiltr;
					}
					if ($level==1) {
						$rowsNeed=$rowsFiltr;
					}
					if ($level==2) {
						for ($i=0; $i < $countRowsFiltr; $i++) {
							if ($rowsFiltr[$i]['routeName']==$nodeName) {
								$rowsNeed[]=$rowsFiltr[$i];
							}
						}
					}
					if ($level==3) {
						for ($i=0; $i < $countRowsFiltr; $i++) {
							$nodeNameExplode=explode("***", $nodeName);
							if ($rowsFiltr[$i]['routeName']==$nodeNameExplode[0]) {
								if ($rowsFiltr[$i]['graphName']==$nodeNameExplode[1]) {
									$rowsNeed[]=$rowsFiltr[$i];
								}
							}
						}
					}
				}
			}
			$countRowsNeed=count($rowsNeed);
			for ($i=0; $i < $countRowsNeed; $i++) {
				$rt[$rowsNeed[$i]['problemsName']]=$rt[$rowsNeed[$i]['problemsName']]+1;
				$nameProblems[$rowsNeed[$i]['problemsName']]=$rowsNeed[$i]['problemsName'];
			}
			if (isset($nameProblems)) {
				natsort($nameProblems);
				foreach ($nameProblems as $key => $value) {
					$sumResultNeed=$sumResultNeed." ".$key." - ".$rt[$value]." ".Yii::app()->session['PiecesText']."</br>";
				}
				$nppNeed=1;
				for ($i=0; $i < $countRowsNeed; $i++) { 
					$rowsNeed[$i]['npp']=$nppNeed;
					$nppNeed=$nppNeed+1;
				}
			}
			
			
			$result = array('success' => true, 'rows'=>$rowsNeed, 'totalCount'=>$countRowsNeed, 'sumResult'=>$sumResultNeed); 
			echo CJSON::encode($result);
		}
	}
}
