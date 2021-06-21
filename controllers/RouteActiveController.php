<?php
class RouteActiveController extends Controller
{
	public function actionRead($id)//на посилання з гет додавання comment
	{
				
		$idHis=Yii::app()->request->getParam('idHis');
		$comment=Yii::app()->request->getParam('textadd');
		
		$a= RouteHistoryIdAll::model()->findByAttributes(array('Id'=>$idHis));
		$oldCom=$a->comment;
		$newCom=$oldCom." ".$comment;
		$length=strlen($newCom);
		if ($length>=60)
		{
			$newtext = wordwrap($newCom, 60, "<br />\n");
		}
		else if ($length<60)
		{
			$newtext = $newCom;
		}
		$a->comment=$newtext;
		$a->save();
		$result = array('success' => true, 'rows'=>$arrSt);
		echo CJSON::encode($result);
	}
	public function actionCreate($id)//на посилання з пост для активації
	{
		$routeid=Yii::app()->request->getParam('routeid');
		$idHis=Yii::app()->request->getParam('idHis');
 		$routeAct=Yii::app()->request->getParam('routeAct');
 		$grNum=Yii::app()->request->getParam('numGrAct');

 		if ($routeAct==1)//активація маршруту
 		{
 			$todayForActive=date('Y-m-d H:i:s');
			$todayForSchedules=date('Y-m-d');

			$b = RouteHistoryIdAll::model()->findByAttributes(array('Id'=>$idHis));
			$N=$b->amount;
			$moveMethod=$b->move_method;
			$calcMethod=$b->calc_method;
			$scheduleTypeId=$b->schedules_type_id;

			$rewriteroute = Route::model()->findByAttributes(array('id'=>$routeid));
			$rewriteroute->move_methods_id=$moveMethod;
			$rewriteroute->save();

			//шукаемо ід графіків
			$c = Graphs::model()->findAll(array(
				'condition'=> 'routes_id = :rid',
				'params'   => array(':rid' => $routeid),
				'order'    => 'id'));
			foreach ($c as $cc) {
				$graphId=$cc->id;
				$graphName=$cc->name;
				$arrayGraph[]=array('id'=>$graphId, 'name'=>$graphName);
				if ($graphName<=$N) {
					$cc->status='yes';
				}
				if ($graphName>$N) {
					$cc->status='no';
				}
				$cc->save();
			}

			//створюємо schedules
			$countGr=count($arrayGraph);
			for ($i=0; $i < $countGr; $i++) { 
				for ($a=1; $a <=$N; $a++) { 
					if ($arrayGraph[$i]['name']==$a) {
						$arrayGraphForSchedules[]=array(
							'id'=>$arrayGraph[$i]['id'],
							'name'=>$arrayGraph[$i]['name']
						);
					}
				}
			}
			for ($i=0; $i < $countGr; $i++) { 
				$r2 = Schedules::model()->findAll(array(
					'condition'=> 'graphs_id = :gid AND schedule_types_id = :stid',
					'params'   => array(':gid' => $arrayGraph[$i]['id'], ':stid'=>$scheduleTypeId)));
				foreach ($r2 as $rr2) {
					$rr2->status='no';
					$rr2->save();
				}
			}
			for ($i=0; $i < $N; $i++) { 
				/*$r = Schedules::model()->findAll(array(
					'condition'=> 'graphs_id = :gid AND schedule_types_id = :stid',
					'params'   => array(':gid' => $arrayGraphForSchedules[$i]['id'], ':stid'=>$scheduleTypeId),
					'order'    => 'id'));
				foreach ($r as $rr) {
					if (isset($rr)) {
						$rr->status='no';
						$rr->save();
					}
				}*/
				$t = new Schedules;
				$t->graphs_id=$arrayGraphForSchedules[$i]['id'];
				$t->create_date=$todayForSchedules;
				$t->schedule_types_id=$scheduleTypeId;
				$t->status='yes';
				$t->histories_id=$idHis;
				$t->save();
				$arrayGraphForSchedules[$i]['idSchedules']=$t->id;
			}



			$a = RouteTimeTable::model()->findAll(array(
				'condition'=> 'routes_history_id = :rhid',
				'params'   => array(':rhid' => $idHis),
				'order'    => 'Id'));
			foreach ($a as $aa) {
				$graphNumber=$aa->graphs_number;
				$fl=$aa->flights_number;
				$time=$aa->time;
				$stationId=$aa->stations_id;
				$stationScenarioId=$aa->stations_scenario_id;
				$number=$aa->number;
				$ds=$aa->dinner_start;
				$de=$aa->dinner_end;
				$ws=$aa->workshift_start;
				$we=$aa->workshift_end;
				$arrayTime[]=array(
					'n'=>$graphNumber,
					'fl'=>$fl,
					'time'=>$time,
					'stationId'=>$stationId,
					'stationScenarioId'=>$stationScenarioId,
					'number'=>$number,
					'ws'=>$ws,
					'we'=>$we,
					'ds'=>$ds,
					'de'=>$de,
					'shiftNumber'=>1
				);
			}

			$countAT=count($arrayTime);
			//перерахунок номеру рейсу якщо видалили і залищився початок з 2
			for ($i=0; $i < $countAT; $i++) {
				if ($arrayTime[$i]['n']!=$arrayTime[$i-1]['n']) {
					if ($arrayTime[$i]['fl']!=1) {
						$flOld=$arrayTime[$i]['fl'];
						$numberI=$i;
						$riznFl=$flOld-1;
						$nOld=$arrayTime[$i]['n'];
						for ($s=$numberI; $s < $countAT; $s++) { 
							if ($arrayTime[$s]['n']==$nOld) {
								$arrayTime[$s]['fl']=$arrayTime[$s]['fl']-$riznFl;
							}
						}
					}
				}
			}

			for ($i=0; $i < $countAT; $i++) { 
				for ($a=0; $a < $N; $a++) { 
					if ($arrayTime[$i]['n']==$arrayGraphForSchedules[$a]['name']) {
						$arrayTime[$i]['graphId']=$arrayGraphForSchedules[$a]['id'];
						$arrayTime[$i]['idSchedules']=$arrayGraphForSchedules[$a]['idSchedules'];
					}
				}
			}
				$st='yes';
				//шукаемо точки контролю
				$pcStatus=StationsScenario::model()->findAll(array(
					'condition'=> 'routes_id = :rid AND pc_status = :st',
					'params'   => array(':rid' => $routeid, ':st'=>$st),
					'order'    => 'number'));
				$u=1;
				foreach ($pcStatus as $k) {
					$arrayStatusYesStations[]=$k->stations_id;
					$arrayStatusNumberStations[]=$u;
					$u=$u+1;
				}
				$countStatusYes=count($arrayStatusYesStations);
				for ($i=0; $i < $countAT; $i++) {
					for ($a=0; $a < $countStatusYes; $a++) { 
						if ($arrayTime[$i]['stationId']==$arrayStatusYesStations[$a]) {
							$arrayTime[$i]['stationStatus']=1;
							$arrayTime[$i]['stationStatusNumberPC']=$arrayStatusNumberStations[$a];
						}
					}
				}
			//вставляємо дані в сскедл таймс
			for ($i=0; $i < $countAT; $i++)
			{
				if ($arrayTime[$i]['stationStatus']==1) 
				{
					$r = new ScheduleTimes;
					$r->schedules_id=$arrayTime[$i]['idSchedules'];
					$r->flights_number=$arrayTime[$i]['fl'];
					$r->stations_id=$arrayTime[$i]['stationId'];
					$r->pc_number=$arrayTime[$i]['stationStatusNumberPC'];
					$r->time=$arrayTime[$i]['time'];
					$r->save();
				}
			}

			for ($i=0; $i < $countAT; $i++) {
				if ($i==0) {

				}
				if ($i!=0) {
					if ($arrayTime[$i]['n']==$arrayTime[$i-1]['n']) {
						if (isset($arrayTime[$i-1]['ws'])) {
							$arrayTime[$i]['shiftNumber']=2;
						}
						if (!isset($arrayTime[$i-1]['ws'])) {
							$arrayTime[$i]['shiftNumber']=$arrayTime[$i-1]['shiftNumber'];
						}
					}
				}
			}
			for ($i=0; $i < $countAT; $i++) {
				if (isset($arrayTime[$i]['ds'])) {
					$arrayTime[$i+1]['dindur']=$arrayTime[$i]['de']-$arrayTime[$i]['ds'];
				}
			}
			for ($i=0; $i < $countAT; $i++) {
				if ($i==0) {
					$arrayTime[$i]['startWork1']=$arrayTime[$i]['time'];
					$arrayTime[$i]['startWork2']=0;
					$arrayTime[$i]['elapsedWork1']=0;
					$arrayTime[$i]['elapsedWork2']=0;
				}
				else {
					if ($arrayTime[$i]['n']==$arrayTime[$i-1]['n']) {
						$arrayTime[$i]['startWork1']=$arrayTime[$i-1]['startWork1'];
						if ($arrayTime[$i]['shiftNumber']==$arrayTime[$i-1]['shiftNumber']) {
							$arrayTime[$i]['startWork2']=$arrayTime[$i-1]['startWork2'];
							if ($arrayTime[$i]['shiftNumber']==1) {
								$arrayTime[$i]['elapsedWork2']=0;
								$arrayTime[$i]['elapsedWork1']=$arrayTime[$i-1]['elapsedWork1']+($arrayTime[$i]['time']-$arrayTime[$i-1]['time'])-$arrayTime[$i]['dindur'];
							}
							if ($arrayTime[$i]['shiftNumber']==2) {
								$arrayTime[$i]['elapsedWork1']=0;
								$arrayTime[$i]['elapsedWork2']=$arrayTime[$i-1]['elapsedWork2']+($arrayTime[$i]['time']-$arrayTime[$i-1]['time'])-$arrayTime[$i]['dindur'];
							}
						}
						if ($arrayTime[$i]['shiftNumber']!=$arrayTime[$i-1]['shiftNumber']) {
							$arrayTime[$i]['startWork2']=$arrayTime[$i-1]['time'];
							$arrayTime[$i-1]['startWork2']=$arrayTime[$i-1]['time'];
							$arrayTime[$i-1]['elapsedWork2']=0;
							$arrayTime[$i]['elapsedWork1']=0;
							$arrayTime[$i]['elapsedWork2']=$arrayTime[$i-1]['elapsedWork2']+($arrayTime[$i]['time']-$arrayTime[$i-1]['time'])-$arrayTime[$i]['dindur'];
						}
					}
					if ($arrayTime[$i]['n']!=$arrayTime[$i-1]['n']) {
						$arrayTime[$i]['startWork1']=$arrayTime[$i]['time'];
						$arrayTime[$i]['startWork2']=0;
						$arrayTime[$i]['elapsedWork1']=0;
						$arrayTime[$i]['elapsedWork2']=0;
					}
				}
			}
			for ($i=0; $i < $countAT; $i++) {
				if ($arrayTime[$i]['elapsedWork1']<0) {
					$arrayTime[$i]['elapsedWork1']=$arrayTime[$i]['elapsedWork1']+24*3600;
				}
				if ($arrayTime[$i]['elapsedWork2']<0) {
					$arrayTime[$i]['elapsedWork2']=$arrayTime[$i]['elapsedWork2']+24*3600;
				}
			}

			//формуємо вставку в воркшіфтс
			for ($i=0; $i < $countAT; $i++) {
				if ($arrayTime[$i]['shiftNumber']==1) {
					$dur=$arrayTime[$i]['elapsedWork1'];
					$start=$arrayTime[$i]['startWork1'];
				}
				if ($arrayTime[$i]['shiftNumber']==2) {
					$dur=$arrayTime[$i]['elapsedWork2'];
					$start=$arrayTime[$i]['startWork2'];
				}
				$arrayShift[$arrayTime[$i]['n']-1][$arrayTime[$i]['shiftNumber']-1]=array(
					'schId'=>$arrayTime[$i]['idSchedules'],
					'number'=>$arrayTime[$i]['shiftNumber'],
					'limit'=>12*3600,
					'dur'=>$dur,
					'start'=>$start,
					'end'=>$arrayTime[$i]['time']
				);
			}
			$countShift=count($arrayShift);
			for ($i=0; $i < $countShift; $i++) { 
				for ($a=0; $a <2 ; $a++) { 
					if (isset($arrayShift[$i][$a]['schId'])) {
						$e = new Workshift;
						$e->schedule_id=$arrayShift[$i][$a]['schId'];
						$e->number=$arrayShift[$i][$a]['number'];
						$e->duration_limit=$arrayShift[$i][$a]['limit'];
						$e->duration=$arrayShift[$i][$a]['dur'];
						$e->start_time=$arrayShift[$i][$a]['start'];
						$e->end_time=$arrayShift[$i][$a]['end'];
						$e->save();
						$k=$e->id;
						$arrayShift[$i][$a]['workId']=$k;
					}
				}
			}

			//шукаемо обіди
			for ($i=0; $i < $countAT; $i++) {
				if (isset($arrayTime[$i]['ds'])) {
					if ($arrayTime[$i]['shiftNumber']==1) {
						$elaps=$arrayTime[$i]['elapsedWork1'];
					}
					if ($arrayTime[$i]['shiftNumber']==2) {
						$elaps=$arrayTime[$i]['elapsedWork2'];
					}
					$dinnerArray[$arrayTime[$i]['n']-1][$arrayTime[$i]['shiftNumber']-1][]=array(
						'schId'=>$arrayTime[$i]['idSchedules'],
						'numberShift'=>$arrayTime[$i]['shiftNumber'],
						'fl'=>$arrayTime[$i]['fl'],
						'stationId'=>$arrayTime[$i]['stationId'],
						'pcNumber'=>$arrayTime[$i]['stationStatusNumberPC'],
						'dur'=>$arrayTime[$i]['de']-$arrayTime[$i]['ds'],
						'start'=>$arrayTime[$i]['ds'],
						'end'=>$arrayTime[$i]['de'],
						'elaps'=>$elaps
					);
				}
			}
			$countAD=count($dinnerArray);
			for ($i=0; $i < $countAD; $i++) { 
				$countADW=count($dinnerArray[$i]);
				for ($a=0; $a < $countADW; $a++) { 
					$countADWA=count($dinnerArray[$i][$a]);
					for ($s=0; $s < $countADWA; $s++) { 
						$dinnerArray[$i][$a][$s]['numberDinner']=$s+1;
						$dinnerArray[$i][$a][$s]['workId']=$arrayShift[$i][$a]['workId'];
						$aa= new  Dinner;
						$aa->number=$dinnerArray[$i][$a][$s]['numberDinner'];
						$aa->schedules_id=$dinnerArray[$i][$a][$s]['schId'];
						$aa->workshift_id=$dinnerArray[$i][$a][$s]['workId'];
						$aa->flight_number=$dinnerArray[$i][$a][$s]['fl'];
						$aa->stations_id=$dinnerArray[$i][$a][$s]['stationId'];
						$aa->pc_number=$dinnerArray[$i][$a][$s]['pcNumber'];
						$aa->start_time=$dinnerArray[$i][$a][$s]['start'];
						$aa->end_time=$dinnerArray[$i][$a][$s]['end'];
						$aa->duration=$dinnerArray[$i][$a][$s]['dur'];
						$aa->elapsed_worktime=$dinnerArray[$i][$a][$s]['elaps'];
						$aa->save();
					}
				}
			}

			$b = RouteHistoryIdAll::model()->findByAttributes(array('Id'=>$idHis));
			$b->date_activity=$todayForActive;
			$b->save();
			
//print_r($dinnerArray);
//print_r($arrayShift);			
//print_r($arrayTime);

			$result = array('success' => true);
			echo CJSON::encode($result);

 		}//активація маршруту


 		//////////////////////////////////////////////////////////////////////


 		if ($routeAct==2)//активація grafika
 		{
 			$todayForActive=date('Y-m-d H:i:s');
			$todayForSchedules=date('Y-m-d');

			$b = RouteHistoryIdAll::model()->findByAttributes(array('Id'=>$idHis));
			$scheduleTypeId=$b->schedules_type_id;
			$oldCom=$b->comment;
			$N=$b->amount;

			if ($grNum>$N)
			{
				$result = array('failure' => true);
				echo CJSON::encode($result);
			}
			if ($grNum<=$N) {
				
				$a = RouteTimeTable::model()->findAll(array(
						'condition'=> 'routes_history_id = :rhid AND graphs_number = :gn',
						'params'   => array(':rhid' => $idHis, ':gn' => $grNum),
						'order'    => 'Id'));
				foreach ($a as $aa)
				{
					$flOld[]=$aa->flights_number;
					$riznFl=$flOld[0]-1;
					$graphNumber=$aa->graphs_number;
					$fl=($aa->flights_number)-$riznFl;
					$time=$aa->time;
					$stationId=$aa->stations_id;
					$stationScenarioId=$aa->stations_scenario_id;
					$number=$aa->number;
					$ds=$aa->dinner_start;
					$de=$aa->dinner_end;
					$ws=$aa->workshift_start;
					$we=$aa->workshift_end;
					$arrayTime[]=array(
						'n'=>$graphNumber,
						'fl'=>$fl,
						'time'=>$time,
						'stationId'=>$stationId,
						'stationScenarioId'=>$stationScenarioId,
						'number'=>$number,
						'ws'=>$ws,
						'we'=>$we,
						'ds'=>$ds,
						'de'=>$de,
						'shiftNumber'=>1
					);
				}
				$countAT=count($arrayTime);

				$c = Graphs::model()->findByAttributes(array('routes_id'=>$routeid,
					'name'=>$grNum));
				$IdGraph=$c->id;

				$r = Schedules::model()->findAll(array(
					'condition'=> 'graphs_id = :gid AND schedule_types_id = :stid',
					'params'   => array(':gid' => $IdGraph, ':stid'=>$scheduleTypeId),
					'order'    => 'id'));
				foreach ($r as $rr) {
					if (isset($rr)) {
						$rr->status='no';
						$rr->save();
					}
				}
				$t = new Schedules;
				$t->graphs_id=$IdGraph;
				$t->create_date=$todayForSchedules;
				$t->schedule_types_id=$scheduleTypeId;
				$t->status='yes';
				$t->histories_id=$idHis;
				$t->save();
				$idSchedules=$t->id;
			
				$st='yes';
				//шукаемо точки контролю
				$pcStatus=StationsScenario::model()->findAll(array(
					'condition'=> 'routes_id = :rid AND pc_status = :st',
					'params'   => array(':rid' => $routeid, ':st'=>$st),
					'order'    => 'number'));
				$u=1;
				foreach ($pcStatus as $k) {
					$arrayStatusYesStations[]=$k->stations_id;
					$arrayStatusNumberStations[]=$u;
					$u=$u+1;
				}
				$countStatusYes=count($arrayStatusYesStations);
				for ($i=0; $i < $countAT; $i++) {
					for ($a=0; $a < $countStatusYes; $a++) { 
						if ($arrayTime[$i]['stationId']==$arrayStatusYesStations[$a]) {
							$arrayTime[$i]['stationStatus']=1;
							$arrayTime[$i]['stationStatusNumberPC']=$arrayStatusNumberStations[$a];
						}
					}
				}
				//вставляємо дані в сскедл таймс
				for ($i=0; $i < $countAT; $i++)
				{
					if ($arrayTime[$i]['stationStatus']==1) 
					{
						$r= new ScheduleTimes;
						$r->schedules_id=$idSchedules;
						$r->flights_number=$arrayTime[$i]['fl'];
						$r->stations_id=$arrayTime[$i]['stationId'];
						$r->pc_number=$arrayTime[$i]['stationStatusNumberPC'];
						$r->time=$arrayTime[$i]['time'];
						$r->save();
					}
				}

				for ($i=0; $i < $countAT; $i++) {
					if ($i==0) {

					}
					if ($i!=0) {
						if ($arrayTime[$i]['n']==$arrayTime[$i-1]['n']) {
							if (isset($arrayTime[$i-1]['ws'])) {
								$arrayTime[$i]['shiftNumber']=2;
							}
							if (!isset($arrayTime[$i-1]['ws'])) {
								$arrayTime[$i]['shiftNumber']=$arrayTime[$i-1]['shiftNumber'];
							}
						}
					}
				}

				for ($i=0; $i < $countAT; $i++) {
				if (isset($arrayTime[$i]['ds'])) {
					$arrayTime[$i+1]['dindur']=$arrayTime[$i]['de']-$arrayTime[$i]['ds'];
				}
			}
			for ($i=0; $i < $countAT; $i++) {
				if ($i==0) {
					$arrayTime[$i]['startWork1']=$arrayTime[$i]['time'];
					$arrayTime[$i]['startWork2']=0;
					$arrayTime[$i]['elapsedWork1']=0;
					$arrayTime[$i]['elapsedWork2']=0;
				}
				else {
					if ($arrayTime[$i]['n']==$arrayTime[$i-1]['n']) {
						$arrayTime[$i]['startWork1']=$arrayTime[$i-1]['startWork1'];
						if ($arrayTime[$i]['shiftNumber']==$arrayTime[$i-1]['shiftNumber']) {
							$arrayTime[$i]['startWork2']=$arrayTime[$i-1]['startWork2'];
							if ($arrayTime[$i]['shiftNumber']==1) {
								$arrayTime[$i]['elapsedWork2']=0;
								$arrayTime[$i]['elapsedWork1']=$arrayTime[$i-1]['elapsedWork1']+($arrayTime[$i]['time']-$arrayTime[$i-1]['time'])-$arrayTime[$i]['dindur'];
							}
							if ($arrayTime[$i]['shiftNumber']==2) {
								$arrayTime[$i]['elapsedWork1']=0;
								$arrayTime[$i]['elapsedWork2']=$arrayTime[$i-1]['elapsedWork2']+($arrayTime[$i]['time']-$arrayTime[$i-1]['time'])-$arrayTime[$i]['dindur'];
							}
						}
						if ($arrayTime[$i]['shiftNumber']!=$arrayTime[$i-1]['shiftNumber']) {
							$arrayTime[$i]['startWork2']=$arrayTime[$i-1]['time'];
							$arrayTime[$i-1]['startWork2']=$arrayTime[$i-1]['time'];
							$arrayTime[$i-1]['elapsedWork2']=0;
							$arrayTime[$i]['elapsedWork1']=0;
							$arrayTime[$i]['elapsedWork2']=$arrayTime[$i-1]['elapsedWork2']+($arrayTime[$i]['time']-$arrayTime[$i-1]['time'])-$arrayTime[$i]['dindur'];
						}
					}
					if ($arrayTime[$i]['n']!=$arrayTime[$i-1]['n']) {
						$arrayTime[$i]['startWork1']=$arrayTime[$i]['time'];
						$arrayTime[$i]['startWork2']=0;
						$arrayTime[$i]['elapsedWork1']=0;
						$arrayTime[$i]['elapsedWork2']=0;
					}
				}
			}
			for ($i=0; $i < $countAT; $i++) {
				if ($arrayTime[$i]['elapsedWork1']<0) {
					$arrayTime[$i]['elapsedWork1']=$arrayTime[$i]['elapsedWork1']+24*3600;
				}
				if ($arrayTime[$i]['elapsedWork2']<0) {
					$arrayTime[$i]['elapsedWork2']=$arrayTime[$i]['elapsedWork2']+24*3600;
				}
			}

			//формуємо вставку в воркшіфтс
			for ($i=0; $i < $countAT; $i++) {
				if ($arrayTime[$i]['shiftNumber']==1) {
					$dur=$arrayTime[$i]['elapsedWork1'];
					$start=$arrayTime[$i]['startWork1'];
				}
				if ($arrayTime[$i]['shiftNumber']==2) {
					$dur=$arrayTime[$i]['elapsedWork2'];
					$start=$arrayTime[$i]['startWork2'];
				}
				$arrayShift[$arrayTime[$i]['n']-1][$arrayTime[$i]['shiftNumber']-1]=array(
					'schId'=>$idSchedules,
					'number'=>$arrayTime[$i]['shiftNumber'],
					'limit'=>12*3600,
					'dur'=>$dur,
					'start'=>$start,
					'end'=>$arrayTime[$i]['time']
				);
			}
			$countShift=count($arrayShift);
			for ($i=0; $i < $countShift; $i++) { 
				for ($a=0; $a <2 ; $a++) { 
					if (isset($arrayShift[$i][$a]['schId'])) {
						$e = new Workshift;
						$e->schedule_id=$arrayShift[$i][$a]['schId'];
						$e->number=$arrayShift[$i][$a]['number'];
						$e->duration_limit=$arrayShift[$i][$a]['limit'];
						$e->duration=$arrayShift[$i][$a]['dur'];
						$e->start_time=$arrayShift[$i][$a]['start'];
						$e->end_time=$arrayShift[$i][$a]['end'];
						$e->save();
						$k=$e->id;
						$arrayShift[$i][$a]['workId']=$k;
					}
				}
			}

			//шукаемо обіди
			for ($i=0; $i < $countAT; $i++) {
				if (isset($arrayTime[$i]['ds'])) {
					if ($arrayTime[$i]['shiftNumber']==1) {
						$elaps=$arrayTime[$i]['elapsedWork1'];
					}
					if ($arrayTime[$i]['shiftNumber']==2) {
						$elaps=$arrayTime[$i]['elapsedWork2'];
					}
					$dinnerArray[$arrayTime[$i]['n']-1][$arrayTime[$i]['shiftNumber']-1][]=array(
						'schId'=>$idSchedules,
						'numberShift'=>$arrayTime[$i]['shiftNumber'],
						'fl'=>$arrayTime[$i]['fl'],
						'stationId'=>$arrayTime[$i]['stationId'],
						'pcNumber'=>$arrayTime[$i]['stationStatusNumberPC'],
						'dur'=>$arrayTime[$i]['de']-$arrayTime[$i]['ds'],
						'start'=>$arrayTime[$i]['ds'],
						'end'=>$arrayTime[$i]['de'],
						'elaps'=>$elaps
					);
				}
			}
			$countAD=count($dinnerArray);
			for ($i=0; $i < $countAD; $i++) { 
				$countADW=count($dinnerArray[$i]);
				for ($a=0; $a < $countADW; $a++) { 
					$countADWA=count($dinnerArray[$i][$a]);
					for ($s=0; $s < $countADWA; $s++) { 
						$dinnerArray[$i][$a][$s]['numberDinner']=$s+1;
						$dinnerArray[$i][$a][$s]['workId']=$arrayShift[$i][$a]['workId'];
						$aa= new  Dinner;
						$aa->number=$dinnerArray[$i][$a][$s]['numberDinner'];
						$aa->schedules_id=$dinnerArray[$i][$a][$s]['schId'];
						$aa->workshift_id=$dinnerArray[$i][$a][$s]['workId'];
						$aa->flight_number=$dinnerArray[$i][$a][$s]['fl'];
						$aa->stations_id=$dinnerArray[$i][$a][$s]['stationId'];
						$aa->pc_number=$dinnerArray[$i][$a][$s]['pcNumber'];
						$aa->start_time=$dinnerArray[$i][$a][$s]['start'];
						$aa->end_time=$dinnerArray[$i][$a][$s]['end'];
						$aa->duration=$dinnerArray[$i][$a][$s]['dur'];
						$aa->elapsed_worktime=$dinnerArray[$i][$a][$s]['elaps'];
						$aa->save();
					}
				}
			}

				$b = RouteHistoryIdAll::model()->findByAttributes(array('Id'=>$idHis));
				$b->date_activity=$todayForActive;
				$b->comment=Yii::app()->session['ActivationDoing'].' '.$grNum.' '.Yii::app()->session['GrafikTextSmall'].'. '."<br />\n".$oldCom;
				$b->save();


				
	//print_r($dinnerArray);
	//print_r($arrayShift);			
	//print_r($arrayTime);

				$result = array('success' => true);
				echo CJSON::encode($result);
			}	
 		}//активація grafika

		
	}
}
?>