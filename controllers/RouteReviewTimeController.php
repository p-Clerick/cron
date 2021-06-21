<?php
class RouteReviewTimeController extends Controller
{
	public function actionRead($id)//на посилання з гет заповнення таблиці з часами появи усіх
	{
        $profArray = array();
        $dinnerArrayStart = array();
        $dinnerArrayEnd = array();
        $shiftArray = array();

		$routeid=Yii::app()->request->getParam('routeid');
		$idHis=Yii::app()->request->getParam('idHis');
		$TypeView=Yii::app()->request->getParam('TypeView');

		//сворюємо мметадані
		$metaData = array(
			'idProperty' => 'id',
			'root' => 'rows',
			'totalProperty' => 'results',
			'successProperty' => 'success',
			);	
		$fields[] = array(
			'name' => 'id');
		$fields[] = array(
			'name' => 'sc',
			'type' => 'int');
		$fields[] = array(
			'name' => 'fl',
			'type' => 'int');
		//шукаемо кт
		$a = RouteTimeTable::model()->findAll(array(
			'condition'=> 'routes_history_id = :rhid',
			'params'   => array(':rhid' => $idHis),
			'order'    => 'Id'));
		foreach ($a as $aa)
		{
			$stationScenarioid[]=$aa->stations_scenario_id;
			$stationid[]=$aa->stations_id;
			$stationNumber[]=$aa->number;
		}
		
		$cstid=count($stationid);
		$maxAmountStations=0;
		for ($i=0; $i<$cstid; $i++)
		{
			$arrayStation[$stationNumber[$i]-1]=$stationid[$i];
			if ($stationNumber[$i]>$maxAmountStations) 
			{
				$maxAmountStations=$stationNumber[$i];
			}
		}
		
		ksort($arrayStation);
		foreach ($arrayStation as $key => $value) 
		{
			$fields[] = array(
				'name' => $value);
		}
		

		//вибираємо дані з історій
	/*	$b = RouteTimeTable::model()->findAll(array(
			'condition'=> 'routes_history_id = :rhid',
			'params'   => array(':rhid' => $idHis),
			'order'    => 'Id'));*/
		foreach ($a as $bb)
		{
			$n=$bb->graphs_number;
			$fl=$bb->flights_number;
			$time=$bb->time;
			$t= new Time($time);
			$stationid=$bb->stations_id;
			$stationScenarioid=$bb->stations_scenario_id;
			$stationNumber=$bb->number;
			$arrayTime[]=array(
				$n,
				$fl,
				$stationid,
				$stationScenarioid,
				$stationNumber,
				$t->getFormattedTime()
			);
			$ds=$bb->dinner_start;
			$de=$bb->dinner_end;
			$ws=$bb->workshift_start;
			$prof=$bb->prevention;
			if (isset($ds))
			{
				$ds = new Time($ds);
				$dinnerArrayStart[]=array(
					'start'=>$ds->getFormattedTime(),
					'stovpets'=>$stationNumber+1, 
					'sc'=>$n, 
					'fl'=>$fl);
			}
			if (isset($de))
			{
				if ($stationNumber==$maxAmountStations) {
					$stovpets=2;
					$fl=$fl+1;
				}
				if ($stationNumber!=$maxAmountStations) {
					$stovpets=$stationNumber+2;
				}
				$de = new Time($de);
				$dinnerArrayEnd[]=array(
					'end'=>$de->getFormattedTime(),
					'stovpets'=>$stovpets, 
					'sc'=>$n, 
					'fl'=>$fl);
			}
			if (isset($ws))
			{
				$ws = new Time($ws);
				$shiftArray[]=array(
					'start'=>$ws->getFormattedTime(),
					'stovpets'=>$stationNumber+1, 
					'sc'=>$n, 
					'fl'=>$fl);
			}
			if (isset($prof))
			{
				$profT = new Time($time);
				$profArray[]=array(
					'start'=>$profT->getFormattedTime(),
					'stovpets'=>$stationNumber+1, 
					'sc'=>$n, 
					'fl'=>$fl);
			}
		}
//об*єднуємо записи у рейси для одного рядка		
		$cAT=count($arrayTime);
		$k=0;
		for ($i=0; $i<$cAT; $i++)
		{
			if ($i == 0)
			{
				$rows[0]=array(
					'id' => 0,
					'sc' => $arrayTime[$i][0], 
					'fl' => $arrayTime[$i][1], 
					$arrayTime[$i][2]=>$arrayTime[$i][5]);
			}
			if ($i!=0)
			{
				if ($arrayTime[$i][1]==$arrayTime[$i-1][1])
				{
					$f=count($rows);
					$rows[$f-1][$arrayTime[$i][2]]=$arrayTime[$i][5];
				}
				else
				{
					$k++;
					$rows[]=array(
						'id'=> $k,
						'sc' => $arrayTime[$i][0], 
						'fl' => $arrayTime[$i][1], 
						$arrayTime[$i][2]=>$arrayTime[$i][5]);
					
				}
			}
		}
		//print_r($rows);
		$countRows=count($rows);
		$countDinnersStart=count($dinnerArrayStart);
		$countDinnersEnd=count($dinnerArrayEnd);
		$countShifts=count($shiftArray);
		$countProf=count($profArray);
		for ($i=0; $i<$countRows; $i++)
		{
			for ($a=0; $a<$countDinnersStart; $a++)
			{
				if ($rows[$i]['sc']==$dinnerArrayStart[$a]['sc'])
				{
					if ($rows[$i]['fl']==$dinnerArrayStart[$a]['fl'])
					{
						$dinnerArrayStart[$a]['recordRowsId']=$rows[$i]['id'];
					}
				}
			}
			for ($a=0; $a<$countDinnersEnd; $a++)
			{
				if ($rows[$i]['sc']==$dinnerArrayEnd[$a]['sc'])
				{
					if ($rows[$i]['fl']==$dinnerArrayEnd[$a]['fl'])
					{
						$dinnerArrayEnd[$a]['recordRowsId']=$rows[$i]['id'];
					}
				}
			}
			for ($a=0; $a<$countShifts; $a++)
			{
				if ($rows[$i]['sc']==$shiftArray[$a]['sc'])
				{
					if ($rows[$i]['fl']==$shiftArray[$a]['fl'])
					{
						$shiftArray[$a]['recordRowsId']=$rows[$i]['id'];
					}
				}
			}
			for ($a=0; $a<$countProf; $a++)
			{
				if ($rows[$i]['sc']==$profArray[$a]['sc'])
				{
					if ($rows[$i]['fl']==$profArray[$a]['fl'])
					{
						$profArray[$a]['recordRowsId']=$rows[$i]['id'];
					}
				}
			}
		}
		
		if (($TypeView!=Yii::app()->session['AllText']) && ($TypeView!=0))
		{
			$newType=explode(" ", $TypeView);
			$Type=$newType[0];
			for ($i=0; $i<$countRows; $i++)
			{
				if ($rows[$i]['sc']==$Type)
				{
					$rowsToView[]=$rows[$i];
				}
			}
			unset($rows);
			$countToView=count($rowsToView);
			for ($i=0; $i<$countToView; $i++)
			{
				$rows[]=$rowsToView[$i];
			}
		}
		if (!$rows)
		{
			$result = array('success' => false);
		}
		

		$result = array('success' => true, 'rows' => array(), );
		$metaData['fields'] = $fields;
		$result['metaData'] = $metaData;		
		$result['rows'] = $rows;
		$result['dinnersStart'] = $dinnerArrayStart;
		$result['dinnersEnd'] = $dinnerArrayEnd;
		$result['shifts'] = $shiftArray;
		$result['prof'] = $profArray;

		echo CJSON::encode($result);
	}

    /**
     * @param $id
     */
    public function actionCreate($id)//на посилання з posт //редагування даних
	{
		//отримуємо вхідні дані
		$routeid=Yii::app()->request->getParam('routeid');//
		$idHis=Yii::app()->request->getParam('idHis');//ід історії
		$action=Yii::app()->request->getParam('action');
		/*
		1 - видалення розкладу
		2 - додати графік
		3 - cтворити копыю
		4 - add shiftchange
		5 - видалити перезмінку
		6 - додати обід
		7 - видалити обід
		8 - видалити клітинку
		9 - видалити значення до
		10 - видалити значення після
		11 - редагування з перерахунком графіка
		12 - редагування клітинки без перерахунку графіка
		13 - клонувати
		14 - додати профілактику
		15 - видалити профілактику
		16 - додати рейс без перерахунку графіків
		17 - редагування з зперерахуноком рейсу згідно періоду доби
		*/
		$graphNumberToChange=Yii::app()->request->getParam('grchange');//графік зміни
		$flightnumberToChange=Yii::app()->request->getParam('flightchange');// рейс зміни
		$stationChange=Yii::app()->request->getParam('fieldchange');//pcsid зміни
		$newValueChange=Yii::app()->request->getParam('newValuechange');//нове значення часу
		$oldValueChange=Yii::app()->request->getParam('oldValuechange');//старе значення часу
		$addDinDur=Yii::app()->request->getParam('addDinDur');//для додати обід
		/////////////////////////////////////////////////////////////////



		if ($action==1)//видалити розклад 
		{
			$t=RouteHistoryIdAll::model()->findByAttributes(array('Id'=>$idHis));
			$actstatus=$t->date_activity;
			if ($actstatus==null)
			{
				$gh = RouteTimeTable::model()->findAll(array(
					'condition' => 'routes_history_id = :rhid',
					'params'    => array(':rhid' => $idHis),
					'order'     => 'Id'));
				foreach ($gh as $ghgh) 
				{
					$ghgh->delete();
				}
				$t->delete();
				$delOrderTime = RouteGraphOrderTimeTable::model()->findAll(array(
					'condition' => 'historys_id = :rhid',
					'params'    => array(':rhid' => $idHis),
					'order'     => 'Id'));
				foreach ($delOrderTime as $k) {
					$k->delete();
				}
				$doing=Yii::app()->session['CalcDeleted'] ;
				$result = array('success' => true, 'rows' => $doing  );
			}
			if ($actstatus!=null)
			{
				$doing=Yii::app()->session['CalcActivedDeletingNotPossible'];
				$result = array('success' => false, 'rows' => $doing  );
			}
			echo CJSON::encode($result);
		}


/////////////////////////////////////////////////////////////////////////////////////


		if ($action==2) 
		{ //додати графік
			//додаємо кільість в таблицю з історією ід
			$a = RouteHistoryIdAll::model()->findByAttributes(array('Id'=>$idHis));
			$oldN=$a->amount;
			$newN=$oldN+1;
			$scheduleTypeId=$a->schedules_type_id;
			$a->amount=$newN;
			$a->save();
			//перевіряємо наявність графіку в таблиці графс
			$b = Graphs::model()->findByAttributes(array('routes_id'=>$routeid, 'name' => $newN));
			if (!isset($b))
			{
				$c = Graphs::model()->findByAttributes(array('routes_id'=>$routeid, 'name' => $oldN)); 
				$carrierId=$c->carriers_id;
				$d = new Graphs;
				$d->routes_id=$routeid;
				$d->carriers_id=$carrierId;
				$d->name=$newN;
				$d->save();
			}
			$startTime=6*3600;
			//вибираємо ід дейінтервал для нашого роута ід
			$dayIntAll = DayIntervalRoute::model()->findAll(array(
				'condition' => 'routes_id = :rouid',
				'params' => array(':rouid' => $routeid),
				'order' => 'id'));
			foreach ($dayIntAll as $d) {
				$arrayDayIntAll[]=$d->day_interval_city_id;
			}
			//вибираємо дейінтервал до робочого чи вихідного
			foreach ($arrayDayIntAll as $key => $value) {
				$d = DayIntervalCity::model()->findByAttributes(array(
					'id'=>$value,
					'schedules_type_id'=>$scheduleTypeId
				));
				if (isset($d)) {
					$dayIntType[]=array(
						'id'=>$value,
						'start'=>$d->start_time,
						'end'=>$d->end_time
					);
				}
			}
			$countDayIntType=count($dayIntType);//кількість потрібних періодів доби
			if ($countDayIntType==0) {
				$result = array('success' => false, 'msg' => Yii::app()->session['RouteHaveNotDayIntervals']);
				echo CJSON::encode($result);
			}	
			if ($countDayIntType!=0) {
			//вибираємо інтервали в секундах між зупинкаим
			//шукаемо зупинки маршруту
				$c=StationsScenario::model()->findAll(array(
					'condition'=> 'routes_id = :rid',
					'params'   =>array(':rid' => $routeid),
					'order'    => 'number'));
				foreach ($c as $cc) {
					$arraySR[]=array(
						'scenarioId'=>$cc->id,
						'stationId'=>$cc->stations_id,
						'number'=>$cc->number
					);
				}
				$countSR=count($arraySR);//кількість зупинок маршруту
				//шукаемо інтервали між зупинками
				for ($i=0; $i < $countDayIntType; $i++){
					for ($a=0; $a < $countSR; $a++) { 
						if ($a!=$countSR-1) {
							$d = DayIntervalStations::model()->findByAttributes(array(
								'stations_id_from'=>$arraySR[$a]['stationId'],
								'stations_id_to'=>$arraySR[$a+1]['stationId'],
								'day_interval_city_id'=>$dayIntType[$i]['id']
							));
							$arrayInterval[$i][]=$d->interval;
						}
						if ($a==$countSR-1) {
							$d = DayIntervalStations::model()->findByAttributes(array(
								'stations_id_from'=>$arraySR[$a]['stationId'],
								'stations_id_to'=>$arraySR[0]['stationId'],
								'day_interval_city_id'=>$dayIntType[$i]['id']
							));
							$arrayInterval[$i][]=$d->interval;
						}
					}
				}
				$timeToShiftChange = 0;
				$dinnerDurationNorma = 45*60;             //45 хвилин тривалість обіду
				$shiftDurationNorma=28800;     //8 годин   робочого наїзду
				$timeToDinnerNorma=4*60*60;
				//вводимо останній період доби, якщо перебор
				$dayIntType[$countDayIntType]=array(
							'start'=>$dayIntType[$countDayIntType-1]['end'],
							'end'=>$dayIntType[$countDayIntType-1]['end']+10*3600
				);
				$arrayInterval[$countDayIntType]=$arrayInterval[$countDayIntType-1];
				$vyyzd=$startTime;
				$flightNumber=1;
				for ($i=0; $i<=$countDayIntType; $i++)//до кожного періоду доби
				{
					do {
						for ($a=0; $a <$countSR; $a++) {
							$stationId=$arraySR[$a]['stationId'];
							$stationScenarioId=$arraySR[$a]['scenarioId'];
							$stationNumber=$arraySR[$a]['number'];
							$oborot=array_sum($arrayInterval[$i]);
							$e=$vyyzd-$startTime;
							if ($e<$shiftDurationNorma*2+3*3600) {
								$vyyzd=$vyyzd;
							}
							if ($e>$shiftDurationNorma*2+3*3600)
							{
								$vyyzd=$vyyzd;
								if ($stationNumber==$countSR)
								{
									break(2);
								}
							}// закриваюча до якщо наїзд більше тривалості двох змін
							$insertdata[]=array(
								'flightNumber'=>$flightNumber, 
								'stationId'=>$stationId,
								'stationNumber'=>$stationNumber,
								'stationScenarioId'=>$stationScenarioId, 
								'time'=>$vyyzd,
								'oborot'=>$oborot
							);
							$vyyzd=$vyyzd+$arrayInterval[$i][$a];
						}
						$flightNumber=$flightNumber+1;
					}//do
					while ($vyyzd<=$dayIntType[$i]['end']); 
				}//до кожного періоду доби
				$countData=count($insertdata);
				for ($i=0; $i < $countData; $i++) { 
					$t=floor($insertdata[$i]['time']);
					if ($t>=24*3600){$t=$t-24*3600;}
					$routeTimeSave = new RouteTimeTable;
					$routeTimeSave->routes_history_id=$idHis;
					$routeTimeSave->graphs_number=$newN;
					$routeTimeSave->flights_number=$insertdata[$i]['flightNumber'];
					$routeTimeSave->time=$t;
					$routeTimeSave->stations_id=$insertdata[$i]['stationId'];
					$routeTimeSave->stations_scenario_id=$insertdata[$i]['stationScenarioId'];
					$routeTimeSave->number=$insertdata[$i]['stationNumber'];
					$routeTimeSave->duration_flight=$insertdata[$i]['oborot'];
					$routeTimeSave->save();
				}
				$t = new RouteGraphOrderTimeTable;
				$t->routes_id=$routeid;
				$t->historys_id=$idHis;
				$t->graphs_number=$newN;
				$t->attendance=15*60;
				$t->depotTo=15*60;
				$t->depotAfter=15*60;
				$t->typeWork=1;
				$t->save(); 		
				$result = array('success' => true);
				echo CJSON::encode($result);
			}
		} //додати графік


		////////////////////////////////////////////////////////////////////////


		if ($action==3)//копыювати якщо був активний Х
		{
			//копіюємо записи з ід
			$t=RouteHistoryIdAll::model()->findByAttributes(array('Id'=>$idHis));
			$oldDateCreate=$t->date_create;

			$y=new RouteHistoryIdAll;
			$y->routes_id=$t->routes_id;
			$y->amount=$t->amount;
			$y->start_time=$t->start_time;
			$y->move_method=$t->move_method;
			$y->calc_method=$t->calc_method;
			$y->schedules_type_id=$t->schedules_type_id;
			$y->start_station_id=$t->start_station_id;
			$y->end_station_id=$t->end_station_id;
			$y->dinner_station_id=$t->dinner_station_id;
			$newToday=date('Y-m-d H:i:s');
			$y->date_create=$newToday;
			$y->comment=Yii::app()->session['CopyScheduleCreated'] ." ".$oldDateCreate;
			$y->save();
			$copyHisId=$y->Id;
			$r=RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rid',
				'params'    => array(':rid' => $idHis),
				'order'     => 'Id'));
			foreach ($r as $rr)
			{
				$q1=$rr->Id;
				$q2=$rr->graphs_number;
				$q3=$rr->flights_number;
				$q4=$rr->time;
				$q5=$rr->number;
				$q6=$rr->stations_id;
				$q7=$rr->stations_scenario_id;
				$q8=$rr->dinner_start;
				$q9=$rr->dinner_end;
				$q10=$rr->workshift_start;
				$q11=$rr->workshift_end;
				$q12=$rr->prevention;
				$q13=$rr->duration_flight;
				$arrayNew[]=array($q1,$q2,$q3,$q4,$q5,$q6,$q7,$q8,$q9,$q10,$q11,$q12,$q13);
				
			}
			$countNewAr=count($arrayNew);
			//знаходимо нове значення ід
			for ($i=0; $i < $countNewAr; $i++) { 
				if ($i==0) {
					$arrayNew[$i]['riznId']=40;
				}
				else {
					if ($arrayNew[$i][1]!=$arrayNew[$i-1][1]) {
						$arrayNew[$i]['riznId']=20;
					}
					else {
						$arrayNew[$i]['riznId']=$arrayNew[$i][0]-$arrayNew[$i-1][0];
					}
				}
			}
			//print_r($arrayNew);
			for ($i=0; $i < $countNewAr; $i++) {
				$u = new RouteTimeTable;

				$u->routes_history_id=$copyHisId;
				$u->graphs_number=$arrayNew[$i][1];
				$u->flights_number=$arrayNew[$i][2];
				$u->time=$arrayNew[$i][3];
				$u->number=$arrayNew[$i][4];
				$u->stations_id=$arrayNew[$i][5];
				$u->stations_scenario_id=$arrayNew[$i][6];
				$u->dinner_start=$arrayNew[$i][7];
				$u->dinner_end=$arrayNew[$i][8];
				$u->workshift_start=$arrayNew[$i][9];
				$u->workshift_end=$arrayNew[$i][10];
				$u->prevention=$arrayNew[$i][11];
				$u->duration_flight=$arrayNew[$i][12];
				$u->save();
				$r=$u->Id;
				$u->Id=$r+$arrayNew[$i]['riznId']-1;
				$u->save();
			}
				
			//знаходимо кількість записів
			$q=RouteHistoryIdAll::model()->findAll(array(
				'condition' => 'routes_id = :rid',
				'params'    => array(':rid' => $routeid),
				'order'     => 'Id'));
			$fgh=count($q);
			$copy=array('id'=>$fgh);

			$e = RouteGraphOrderTimeTable::model()->findAll(array(
				'condition' => 'historys_id = :rid',
				'params'    => array(':rid' => $idHis),
				'order'     => 'Id'));
			foreach ($e as $k) {
				$w=new RouteGraphOrderTimeTable;
				$w->routes_id=$routeid;
				$w->historys_id=$copyHisId;
				$w->graphs_number=$k->graphs_number;
				$w->attendance=$k->attendance;
				$w->depotTo=$k->depotTo;
				$w->depotAfter=$k->depotAfter;
				$w->typeWork=$k->typeWork;
				$w->save();
			}

			$result = array('success' => true, 'rows' => $copy  );
			echo CJSON::encode($result);
				
		} //копыювати якщо був активний


////////////////////////////////////////////////////////////////////////////

		if ($action==4)//додати перезміну
		{
			
				$time1=explode(":", $newValueChange);
				$res =  mktime($time1[0], $time1[1],$time1[2]);
				$newTimeChange=$time1[0]*60*60+$time1[1]*60+$time1[2];
				
				$a = RouteTimeTable::model()->findAll(array(
					'condition' => 'routes_history_id = :rhid AND graphs_number = :gn',
					'params'    => array(':rhid' => $idHis, ':gn'=>$graphNumberToChange),
					'order'     => 'Id'));
				foreach ($a as $aa) {
					$timeOldTable=$aa->time;
					$stationid=$aa->stations_id;
					$ws=$aa->workshift_start;
					$we=$aa->workshift_end;
					if (isset($ws)) 
					{
						$aa->workshift_start=null;
						$aa->workshift_end=null;
					}
					if (($timeOldTable==$newTimeChange) && ($stationid==$stationChange))
					{
						$aa->workshift_start=$newTimeChange;
						$aa->workshift_end=$newTimeChange;
					}
					$aa->save();
				}
				
				$result = array('success' => true );
				echo CJSON::encode($result);
				
		} //додати перезміну


///////////////////////////////////////////////////////////////////////////////


		if ($action==5)//видаляємо перезмінку
		{
			
				$time1=explode(":", $newValueChange);
				$res =  mktime($time1[0], $time1[1],$time1[2]);
				$newTimeChange=$time1[0]*60*60+$time1[1]*60+$time1[2];
				$a = RouteTimeTable::model()->findByAttributes(array(
					'routes_history_id' => $idHis,
					'graphs_number'     => $graphNumberToChange,
					'flights_number' => $flightnumberToChange,
					'stations_id' => $stationChange,
					'time' => $newTimeChange));
				$a->workshift_start=null;
				$a->workshift_end=null;
				$a->save();

				$result = array('success' => true );
				echo CJSON::encode($result);
				
		}//видаляємо перезмінку


		///////////////////////////////////////////////////////////////////////


		if ($action==6)//додавання обыду
		{
			
				$time1=explode(":", $newValueChange);
				$res =  mktime($time1[0], $time1[1],$time1[2]);
				$newTimeChange=$time1[0]*60*60+$time1[1]*60+$time1[2];

				//розділяємо обід якщо його значення дробове
				$durDinInSecond=$addDinDur*60;

				//визначаемо рейс зміни
				$reys = RouteTimeTable::model()->findAll(array(
					'condition' => 'routes_history_id = :rhid AND graphs_number = :gn AND time =:t AND stations_id = :st',
					'params'    => array(
						':rhid' => $idHis, 
						':gn'=>$graphNumberToChange,
						':t'=>$newTimeChange,
						':st'=>$stationChange),
					'order'     => 'Id'));
				foreach ($reys as $r) 
				{
					$stationNumberChange=$r->number;
				}

				$a = RouteTimeTable::model()->findAll(array(
					'condition' => 'routes_history_id = :rhid AND graphs_number = :gn',
					'params'    => array(':rhid' => $idHis, ':gn'=>$graphNumberToChange),
					'order'     => 'Id'));
				foreach ($a as $aa)
				{
					$timeOldTable=$aa->time;
					$flight=$aa->flights_number;
					$stationId=$aa->stations_id;
					$stationNumber=$aa->number;
					$ds=$aa->dinner_start;
					$de=$aa->dinner_end;
					$ws=$aa->workshift_start;
					$we=$aa->workshift_end;
					if ($flight<$flightnumberToChange) {
						$timeNewTable=$timeOldTable;
						$dinnerStart=$ds;
						$dinnerEnd=$de;
						$workshiftStart=$ws;
						$workshiftEnd=$we;
					}
					if ($flight==$flightnumberToChange) {
						if ($stationNumber<$stationNumberChange) {
							$timeNewTable=$timeOldTable;
							$dinnerStart=$ds;
							$dinnerEnd=$de;
							$workshiftStart=$ws;
							$workshiftEnd=$we;
						}
						if ($stationNumber==$stationNumberChange) {
							$timeNewTable=$timeOldTable;
							$forMinusTime=$timeOldTable;
							$dinnerStart=$timeNewTable;
							$dinnerEnd=$timeNewTable+$durDinInSecond;
							$workshiftStart=$ws;
							$workshiftEnd=$we;
						}
						if ($stationNumber>$stationNumberChange) {
							$sumrizn[]=$timeOldTable-$forMinusTime;
							$timeNewTable=$timeOldTable+$durDinInSecond-$sumrizn[0];
							if (isset($ds)) {
								$dinnerStart=$timeNewTable;
								$dinnerEnd=$timeNewTable+$de-$ds;
							}
							if (!isset($ds)) {
								$dinnerStart=$ds;
								$dinnerEnd=$de;
							}
							if (isset($ws)) {
								$workshiftStart=$timeNewTable;
								$workshiftEnd=$timeNewTable;
							}
							if (!isset($ws)) {
								$workshiftStart=$ws;
								$workshiftEnd=$we;
							}
						}
					}
					if ($flight>$flightnumberToChange) {
						$sumrizn[]=$timeOldTable-$forMinusTime;
						$timeNewTable=$timeOldTable+$durDinInSecond-$sumrizn[0];
						if (isset($ds)) {
								$dinnerStart=$timeNewTable;
								$dinnerEnd=$timeNewTable+$de-$ds;
							}
							if (!isset($ds)) {
								$dinnerStart=$ds;
								$dinnerEnd=$de;
							}
							if (isset($ws)) {
								$workshiftStart=$timeNewTable;
								$workshiftEnd=$timeNewTable;
							}
							if (!isset($ws)) {
								$workshiftStart=$ws;
								$workshiftEnd=$we;
							}
					}
					if ($timeNewTable>24*3600) {$timeNewTable=$timeNewTable-24*3600;}
					if ($timeNewTable<0) {$timeNewTable=$timeNewTable+24*3600;}

					if ($dinnerStart>24*3600) {$dinnerStart=$dinnerStart-24*3600;}
					if ($dinnerStart<0) {$dinnerStart=$dinnerStart+24*3600;}

					if ($dinnerEnd>24*3600) {$dinnerEnd=$dinnerEnd-24*3600;}
					if ($dinnerEnd<0) {$dinnerEnd=$dinnerEnd+24*3600;}

					if ($workshiftStart>24*3600) {$workshiftStart=$workshiftStart-24*3600;}
					if ($workshiftStart<0) {$workshiftStart=$workshiftStart+24*3600;}

					if ($workshiftEnd>24*3600) {$workshiftEnd=$workshiftEnd-24*3600;}
					if ($workshiftEnd<0) {$workshiftEnd=$workshiftEnd+24*3600;}

					
					

					$aa->time=$timeNewTable;
					$aa->dinner_start=$dinnerStart;
					$aa->dinner_end=$dinnerEnd;
					$aa->workshift_start=$workshiftStart;
					$aa->workshift_end=$workshiftEnd;
					$aa->save();
				}

				$text="add dinner";
				$result = array('success' => true, 'rows' => $text  );
				echo CJSON::encode($result);
						
		}//додавання обыду


		///////////////////////////////////////////////////////////////////////////


		if ($action==7) //delete dinner
		{
			
				$time1=explode(":", $newValueChange);
				$res =  mktime($time1[0], $time1[1],$time1[2]);
				$newTimeChange=$time1[0]*60*60+$time1[1]*60+$time1[2];

				//визначаемо рейс зміни
				$reys = RouteTimeTable::model()->findAll(array(
					'condition' => 'routes_history_id = :rhid AND graphs_number = :gn AND time =:t AND stations_id = :st',
					'params'    => array(
						':rhid' => $idHis, 
						':gn'=>$graphNumberToChange,
						':t'=>$newTimeChange,
						':st'=>$stationChange),
					'order'     => 'Id'));
				foreach ($reys as $r) 
				{
					$stationNumberChange=$r->number;
				}
				
				$a = RouteTimeTable::model()->findAll(array(
					'condition' => 'routes_history_id = :rhid AND graphs_number = :gn',
					'params'    => array(':rhid' => $idHis, ':gn'=>$graphNumberToChange),
					'order'     => 'Id'));
				foreach ($a as $aa)
				{
					$timeOldTable=$aa->time;
					$flight=$aa->flights_number;
					$stationid=$aa->stations_id;
					$stationNumber=$aa->number;
					$ds=$aa->dinner_start;
					$de=$aa->dinner_end;
					$ws=$aa->workshift_start;
					$we=$aa->workshift_end;
					
					if ($flight<$flightnumberToChange) {
						$timeNewTable=$timeOldTable;
						$dinnerStart=$ds;
						$dinnerEnd=$de;
						$workshiftStart=$ws;
						$workshiftEnd=$we;
					}
					if ($flight==$flightnumberToChange) {
						if ($stationNumber<$stationNumberChange) {
							$timeNewTable=$timeOldTable;
							$dinnerStart=$ds;
							$dinnerEnd=$de;
							$workshiftStart=$ws;
							$workshiftEnd=$we;
						}
						if ($stationNumber==$stationNumberChange) {
							$timeNewTable=$timeOldTable;
							$dinnerStart=null;
							$dinnerEnd=null;
							$oldDurDin=$de-$ds;
							$workshiftStart=$ws;
							$workshiftEnd=$we;
						}
						if ($stationNumber>$stationNumberChange) {
							$timeNewTable=$timeOldTable-$oldDurDin+3*60;
							if (isset($ds)) {
								$dinnerStart=$ds-$oldDurDin+3*60;
								$dinnerEnd=$de-$oldDurDin+3*60;
							}
							if (!isset($ds)) {
								$dinnerStart=$ds;
								$dinnerEnd=$de;
							}
							if (isset($ws)) {
								$workshiftStart=$ws-$oldDurDin+3*60;
								$workshiftEnd=$we-$oldDurDin+3*60;
							}
							if (!isset($ws)) {
								$workshiftStart=$ws;
								$workshiftEnd=$we;
							}
						}
					}	
					if ($flight>$flightnumberToChange) {
						$timeNewTable=$timeOldTable-$oldDurDin+3*60;
						if (isset($ds)) {
							$dinnerStart=$ds-$oldDurDin+3*60;
							$dinnerEnd=$de-$oldDurDin+3*60;
						}
						if (!isset($ds)) {
							$dinnerStart=$ds;
							$dinnerEnd=$de;
						}
						if (isset($ws)) {
							$workshiftStart=$ws-$oldDurDin+3*60;
							$workshiftEnd=$we-$oldDurDin+3*60;
						}
						if (!isset($ws)) {
							$workshiftStart=$ws;
							$workshiftEnd=$we;
						}
					}
				

					if ($timeNewTable>24*3600) {$timeNewTable=$timeNewTable-24*3600;}
					if ($timeNewTable<0) {$timeNewTable=$timeNewTable+24*3600;}

					if ($dinnerStart>24*3600) {$dinnerStart=$dinnerStart-24*3600;}
					if ($dinnerStart<0) {$dinnerStart=$dinnerStart+24*3600;}

					if ($dinnerEnd>24*3600) {$dinnerEnd=$dinnerEnd-24*3600;}
					if ($dinnerEnd<0) {$dinnerEnd=$dinnerEnd+24*3600;}

					if ($workshiftStart>24*3600) {$workshiftStart=$workshiftStart-24*3600;}
					if ($workshiftStart<0) {$workshiftStart=$workshiftStart+24*3600;}

					if ($workshiftEnd>24*3600) {$workshiftEnd=$workshiftEnd-24*3600;}
					if ($workshiftEnd<0) {$workshiftEnd=$workshiftEnd+24*3600;}

					$aa->time=$timeNewTable;
					$aa->dinner_start=$dinnerStart;
					$aa->dinner_end=$dinnerEnd;
					$aa->workshift_start=$workshiftStart;
					$aa->workshift_end=$workshiftEnd;
					$aa->save();
				}

				$result = array('success' => true);
				echo CJSON::encode($result);
				
		}//delete dinner


		////////////////////////////////////////////////////////////////////////


		if ($action == 8)//видалити клітинку
		{
			
				$time1=explode(":", $oldValueChange);
				$res =  mktime($time1[0], $time1[1],$time1[2]);
				$oldTimeChange=$time1[0]*60*60+$time1[1]*60+$time1[2];

				$e=RouteTimeTable::model()->findByAttributes(array(
					'routes_history_id'=>$idHis, 
					'graphs_number' => $graphNumberToChange, 
					'flights_number'=>$flightnumberToChange, 
					'stations_id'=>$stationChange));
				$e->delete();

				$result = array('success' => true);
				echo CJSON::encode($result);
				
		}//видалити клітинку


		//////////////////////////////////////////////////////////////////////////


		if ($action == 9)//видалити значення до
		{
			
				$time1=explode(":", $oldValueChange);
				$res =  mktime($time1[0], $time1[1],$time1[2]);
				$oldTimeChange=$time1[0]*60*60+$time1[1]*60+$time1[2];

				//визначаемо рейс зміни
				$reys = RouteTimeTable::model()->findAll(array(
					'condition' => 'routes_history_id = :rhid AND graphs_number = :gn AND time =:t AND stations_id = :st',
					'params'    => array(
						':rhid' => $idHis, 
						':gn'=>$graphNumberToChange,
						':t'=>$oldTimeChange,
						':st'=>$stationChange),
					'order'     => 'Id'));
				foreach ($reys as $r) 
				{
					$stationNumberChange=$r->number;
				}

				$e=RouteTimeTable::model()->findAll(array(
					'condition' => 'routes_history_id = :rid AND graphs_number = :gn',
					'params'    => array(':rid' => $idHis, ':gn'=>$graphNumberToChange),
					'order'     => 'Id'));
				foreach ($e as $ee)
				{
					$stationNumber = $ee->number;
					$fl=$ee->flights_number;
					if ($fl<$flightnumberToChange)
					{
						$ee->delete();
					}
					if ($fl==$flightnumberToChange)
					{
						if ($stationNumber<$stationNumberChange)
						{
							$ee->delete();
						}
					}
				}

				$result = array('success' => true,  );
				echo CJSON::encode($result);
				
		}//видалити значення до


		/////////////////////////////////////////////////////////////////////////////


		if ($action == 10)//видалити значення після
		{
			
				$time1=explode(":", $oldValueChange);
				$res =  mktime($time1[0], $time1[1],$time1[2]);
				$oldTimeChange=$time1[0]*60*60+$time1[1]*60+$time1[2];

				//визначаемо рейс зміни
				$reys = RouteTimeTable::model()->findAll(array(
					'condition' => 'routes_history_id = :rhid AND graphs_number = :gn AND time =:t AND stations_id = :st',
					'params'    => array(
						':rhid' => $idHis, 
						':gn'=>$graphNumberToChange,
						':t'=>$oldTimeChange,
						':st'=>$stationChange),
					'order'     => 'Id'));
				foreach ($reys as $r) 
				{
					$stationNumberChange=$r->number;
				}

				$e=RouteTimeTable::model()->findAll(array(
					'condition' => 'routes_history_id = :rid AND graphs_number = :gn',
					'params'    => array(':rid' => $idHis, ':gn'=>$graphNumberToChange),
					'order'     => 'Id'));
				foreach ($e as $ee)
				{
					$stationNumber = $ee->number;
					$fl=$ee->flights_number;
					if ($fl>$flightnumberToChange)
					{
						$ee->delete();
					}
					if ($fl==$flightnumberToChange)
					{
						if ($stationNumber>$stationNumberChange)
						{
							$ee->delete();
						}
					}
				}

				$result = array('success' => true,  );
				echo CJSON::encode($result);
				
		}//видалити значення після


		////////////////////////////////////////////////////////////////////////////


		if ($action == 11) //редагувати клітинку з перерахунком графіка
		{	
			
				if ($newValueChange!=null) {
					//переводимо час в секунди newValue
					$time1=explode(":", $newValueChange);
					$res =  mktime($time1[0], $time1[1],$time1[2]);
					$newTimeChange=$time1[0]*60*60+$time1[1]*60+$time1[2];
				}
				if ($oldValueChange!=null) {
					//переводимо час в секунди oldValue
					$time2=explode(":", $oldValueChange);
					$res =  mktime($time2[0], $time2[1],$time2[2]);
					$oldTimeChange=$time2[0]*60*60+$time2[1]*60+$time2[2];
				}
				
				if ($oldValueChange!=null) {
					//визначаемо рейс зміни
					$reys = RouteTimeTable::model()->findAll(array(
						'condition' => 'routes_history_id = :rhid AND graphs_number = :gn AND time =:t AND stations_id = :st',
						'params'    => array(
							':rhid' => $idHis, 
							':gn'=>$graphNumberToChange,
							':t'=>$oldTimeChange,
							':st'=>$stationChange),
						'order'     => 'Id'));
					foreach ($reys as $r) 
					{
						$stationNumberChange=$r->number;
					}
					$d = RouteTimeTable::model()->findAll(array(
						'condition' => 'routes_history_id = :rid AND graphs_number = :gn',
						'params'    => array(':rid' => $idHis, ':gn'=>$graphNumberToChange),
						'order'     => 'Id'));
					foreach ($d as $dd)
					{
						$stationNumber=$dd->number;
						$fli=$dd->flights_number;
						$timeOldFromTab=$dd->time;
						$sD=$dd->dinner_start;
						$eD=$dd->dinner_end;
						$riznDinner=$eD-$sD;
						$ws=$dd->workshift_start;
						if ($fli < $flightnumberToChange)
						{
							$timeNewTab=$timeOldFromTab;
						}
						if ($fli > $flightnumberToChange)
						{
							$timeNewTab=$timeOldFromTab+($newTimeChange-$oldTimeChange);
						}
						if ($fli == $flightnumberToChange)
						{
							if ($stationNumber< $stationNumberChange)
							{
								$timeNewTab=$timeOldFromTab;
							}
							if ($stationNumber >= $stationNumberChange)
							{
							$timeNewTab=$timeOldFromTab+($newTimeChange-$oldTimeChange);
							}
						}
						$t=$timeNewTab;
						if ($t>24*3600){$t=$t-24*3600;}
						if ($t<=0){$t=$t+24*3600;}
						$dd->time=$t;
						if (isset($eD))
						{
							$dd->dinner_end=$t+$riznDinner;
							$dd->dinner_start=$t;
						}
						if (isset($ws))
						{
							$dd->workshift_start=$t;
							$dd->workshift_end=$t;
						}
						$dd->save();
					}

					$result = array('success' => true,  );
					echo CJSON::encode($result);
				}
				if ($oldValueChange==null) {
					$d = RouteTimeTable::model()->findAll(array(
						'condition' => 'routes_history_id = :rid AND graphs_number = :gn',
						'params'    => array(':rid' => $idHis, ':gn'=>$graphNumberToChange),
						'order'     => 'Id'));
					foreach ($d as $dd)
					{
						$stationid=$dd->stations_id;
						$numberSt=$dd->number;
						$scen=$dd->stations_scenario_id;
						$fli=$dd->flights_number;
						$timeOldFromTab=$dd->time;
						$sD=$dd->dinner_start;
						$eD=$dd->dinner_end;
						$riznDinner=$eD-$sD;
						$ws=$dd->workshift_start;
						$id=$dd->Id;
						if ($fli == $flightnumberToChange)
						{
							$arrChange[]=array($stationid,$numberSt,$id);
						}
						if ($stationid==$stationChange) {
							$numberSTCh=$numberSt;
							$scanCh=$scen;
						}
					}
					$countACh=count($arrChange);
					for ($i=0; $i < $countACh; $i++) { 
						if ($arrChange[$i][1]<$numberSTCh) {
							$minId=$arrChange[$i][2];
							$minNum=$arrChange[$i][1];
						}
						if ($arrChange[$i][1]>$numberSTCh) {
							$maxId[]=$arrChange[$i][2];
							$maxNum[]=$arrChange[$i][1];
						}
					}

					if (isset($minNum)) {
						$rizn=$numberSTCh-$minNum;
						$r= new RouteTimeTable;
						$r->routes_history_id=$idHis;
						$r->graphs_number=$graphNumberToChange;
						$r->flights_number=$flightnumberToChange;
						$r->time=$newTimeChange;
						$r->stations_id=$stationChange;
						$r->stations_scenario_id=$scanCh;
						$r->number=$numberSTCh;
						$r->Id=$minId+$rizn;
						$r->save();
					}
					else if (!isset($minNum)) {
						if (isset($maxNum[0])) {
							$rizn=$maxNum[0]-$numberSTCh;
							$r= new RouteTimeTable;
							$r->routes_history_id=$idHis;
							$r->graphs_number=$graphNumberToChange;
							$r->flights_number=$flightnumberToChange;
							$r->time=$newTimeChange;
							$r->stations_id=$stationChange;
							$r->stations_scenario_id=$scanCh;
							$r->number=$numberSTCh;
							$r->Id=$maxId[0]-$rizn;
							$r->save();
						}
					}
					$result = array('success' => true,  );
					echo CJSON::encode($result);
				}
				
		} //редагування + або - по конкретній точці з подальшим перерахунком усього графіка


		///////////////////////////////////////////////////////////////////



		if ($action == 12) //редагувати клітинку без перерахунку графіка
		{
			if ($newValueChange!=null) {
				//переводимо час в секунди newValue
				$time1=explode(":", $newValueChange);
				$res =  mktime($time1[0], $time1[1],$time1[2]);
				$newTimeChange=$time1[0]*60*60+$time1[1]*60+$time1[2];
			}
			if ($oldValueChange!=null) {
				//переводимо час в секунди oldValue
				$time2=explode(":", $oldValueChange);
				$res =  mktime($time2[0], $time2[1],$time2[2]);
				$oldTimeChange=$time2[0]*60*60+$time2[1]*60+$time2[2];
			}
			if ($oldValueChange!=null) {
				
				$d = RouteTimeTable::model()->findAll(array(
					'condition' => 'routes_history_id = :rid AND graphs_number = :gn',
					'params'    => array(':rid' => $idHis, ':gn'=>$graphNumberToChange),
					'order'     => 'Id'));
				foreach ($d as $dd)
				{
					$stationid=$dd->stations_id;
					$fli=$dd->flights_number;
					$timeOldFromTab=$dd->time;
					$sD=$dd->dinner_start;
					$eD=$dd->dinner_end;
					$riznDinner=$eD-$sD;
					$ws=$dd->workshift_start;
					if ($fli == $flightnumberToChange)
					{
						if ($stationid == $stationChange)
						{
							$dd->time=$newTimeChange;
							if (isset($sD)) {
								$dd->dinner_start=$newTimeChange;
							}
							if (isset($ws)) {
								$dd->workshift_start=$newTimeChange;
								$dd->workshift_end=$newTimeChange;
							}
							$dd->save();
						}
					}
				}

				$result = array('success' => true,  );
				echo CJSON::encode($result);
			}
			if ($oldValueChange==null) {
					
				$d = RouteTimeTable::model()->findAll(array(
					'condition' => 'routes_history_id = :rid AND graphs_number = :gn',
					'params'    => array(':rid' => $idHis, ':gn'=>$graphNumberToChange),
					'order'     => 'Id'));
				foreach ($d as $dd)
				{
					$stationid=$dd->stations_id;
					$numberSt=$dd->number;
					$scen=$dd->stations_scenario_id;
					$fli=$dd->flights_number;
					$timeOldFromTab=$dd->time;
					$sD=$dd->dinner_start;
					$eD=$dd->dinner_end;
					$riznDinner=$eD-$sD;
					$ws=$dd->workshift_start;
					$id=$dd->Id;
					if ($fli == $flightnumberToChange)
					{
						$arrChange[]=array($stationid,$numberSt,$id);
					}
					if ($stationid==$stationChange) {
						$numberSTCh=$numberSt;
						$scanCh=$scen;
					}
				}
				$countACh=count($arrChange);
				for ($i=0; $i < $countACh; $i++) { 
					if ($arrChange[$i][1]<$numberSTCh) {
						$minId=$arrChange[$i][2];
						$minNum=$arrChange[$i][1];
					}
					if ($arrChange[$i][1]>$numberSTCh) {
						$maxId[]=$arrChange[$i][2];
						$maxNum[]=$arrChange[$i][1];
					}
				}

				if (isset($minNum)) {
					$rizn=$numberSTCh-$minNum;
					$r= new RouteTimeTable;
					$r->routes_history_id=$idHis;
					$r->graphs_number=$graphNumberToChange;
					$r->flights_number=$flightnumberToChange;
					$r->time=$newTimeChange;
					$r->stations_id=$stationChange;
					$r->stations_scenario_id=$scanCh;
					$r->number=$numberSTCh;
					$r->Id=$minId+$rizn;
					$r->save();
				}
				else if (!isset($minNum)) {
					if (isset($maxNum[0])) {
						$rizn=$maxNum[0]-$numberSTCh;
						$r= new RouteTimeTable;
						$r->routes_history_id=$idHis;
						$r->graphs_number=$graphNumberToChange;
						$r->flights_number=$flightnumberToChange;
						$r->time=$newTimeChange;
						$r->stations_id=$stationChange;
						$r->stations_scenario_id=$scanCh;
						$r->number=$numberSTCh;
						$r->Id=$maxId[0]-$rizn;
						$r->save();
					}
				}
				$result = array('success' => true,  );
				echo CJSON::encode($result);
			}
		}//редагування + або - по конкретній точці без перерахунку усього графіка


		//////////////////////////////////////////////////////////////////////////


		if ($action ==13)//клонування розкладу з заміною робочого дня на вихідний і навпаки
		{
			if ($addDinDur==null) {
				$addDinDur=1;
			}
			//копіюємо записи з ід
			$t=RouteHistoryIdAll::model()->findByAttributes(array('Id'=>$idHis));
			$oldDateCreate=$t->date_create;
			$y=new RouteHistoryIdAll;
			$y->routes_id=$t->routes_id;
			$y->amount=$t->amount;
			$y->start_time=$t->start_time;
			$y->move_method=$t->move_method;
			$y->calc_method=$t->calc_method;
			$y->schedules_type_id=$addDinDur;
			$y->start_station_id=$t->start_station_id;
			$y->end_station_id=$t->end_station_id;
			$y->dinner_station_id=$t->dinner_station_id;
			$newToday=date('Y-m-d H:i:s');
			$y->date_create=$newToday;
			$y->comment=Yii::app()->session['CopyScheduleCreated'] ." ".$oldDateCreate;
			$y->save();
			$copyHisId=$y->Id;

			$r=RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rid',
				'params'    => array(':rid' => $idHis),
				'order'     => 'Id'));
			foreach ($r as $rr)
			{
				$q1=$rr->Id;
				$q2=$rr->graphs_number;
				$q3=$rr->flights_number;
				$q4=$rr->time;
				$q5=$rr->number;
				$q6=$rr->stations_id;
				$q7=$rr->stations_scenario_id;
				$q8=$rr->dinner_start;
				$q9=$rr->dinner_end;
				$q10=$rr->workshift_start;
				$q11=$rr->workshift_end;
				$q12=$rr->prevention;
				$q13=$rr->duration_flight;
				$arrayNew[]=array($q1,$q2,$q3,$q4,$q5,$q6,$q7,$q8,$q9,$q10,$q11,$q12,$q13);
				
			}
			$countNewAr=count($arrayNew);
			//знаходимо нове значення ід
			for ($i=0; $i < $countNewAr; $i++) { 
				if ($i==0) {
					$arrayNew[$i]['riznId']=40;
				}
				else {
					if ($arrayNew[$i][1]!=$arrayNew[$i-1][1]) {
						$arrayNew[$i]['riznId']=40;
					}
					else {
						$arrayNew[$i]['riznId']=$arrayNew[$i][0]-$arrayNew[$i-1][0];
					}
				}
			}
			//print_r($arrayNew);
			for ($i=0; $i < $countNewAr; $i++) {
				$u = new RouteTimeTable;

				$u->routes_history_id=$copyHisId;
				$u->graphs_number=$arrayNew[$i][1];
				$u->flights_number=$arrayNew[$i][2];
				$u->time=$arrayNew[$i][3];
				$u->number=$arrayNew[$i][4];
				$u->stations_id=$arrayNew[$i][5];
				$u->stations_scenario_id=$arrayNew[$i][6];
				$u->dinner_start=$arrayNew[$i][7];
				$u->dinner_end=$arrayNew[$i][8];
				$u->workshift_start=$arrayNew[$i][9];
				$u->workshift_end=$arrayNew[$i][10];
				$u->prevention=$arrayNew[$i][11];
				$u->duration_flight=$arrayNew[$i][12];
				$u->save();
				$r=$u->Id;
				$u->Id=$r+$arrayNew[$i]['riznId']-1;
				$u->save();
			}
					
			//знаходимо кількість записів
			$q=RouteHistoryIdAll::model()->findAll(array(
				'condition' => 'routes_id = :rid',
				'params'    => array(':rid' => $routeid),
				'order'     => 'Id'));
			$fgh=count($q);
			$copy=array('id'=>$fgh);

			$e = RouteGraphOrderTimeTable::model()->findAll(array(
				'condition' => 'historys_id = :rid',
				'params'    => array(':rid' => $idHis),
				'order'     => 'Id'));
			foreach ($e as $k) {
				$w=new RouteGraphOrderTimeTable;
				$w->routes_id=$routeid;
				$w->historys_id=$copyHisId;
				$w->graphs_number=$k->graphs_number;
				$w->attendance=$k->attendance;
				$w->depotTo=$k->depotTo;
				$w->depotAfter=$k->depotAfter;
				$w->typeWork=$k->typeWork;
				$w->save();
			}
				
			$result = array('success' => true, 'rows' => $copy  );
			echo CJSON::encode($result);
		} //клонування розкладу


////////////////////////////////////////////////////////////////////////

		if ($action==14)//додати профілактику
		{
			$time1=explode(":", $newValueChange);
			$res =  mktime($time1[0], $time1[1],$time1[2]);
			$newTimeChange=$time1[0]*60*60+$time1[1]*60+$time1[2];
				
			$a = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn',
				'params'    => array(':rhid' => $idHis, ':gn'=>$graphNumberToChange),
				'order'     => 'Id'));
			foreach ($a as $aa) {
				$timeOldTable=$aa->time;
				$stationId=$aa->stations_id;
				$prof=$aa->prevention;
				if (($timeOldTable==$newTimeChange) && ($stationId==$stationChange))
				{
					$aa->prevention=1;
				}
				$aa->save();
			}
				
			$result = array('success' => true );
			echo CJSON::encode($result);
		}
		//додати профілактику


		///////////////////////////////////////////////////////////////////////////


		if ($action==15)//видалити профілактику
		{
			$time1=explode(":", $newValueChange);
			$res =  mktime($time1[0], $time1[1],$time1[2]);
			$newTimeChange=$time1[0]*60*60+$time1[1]*60+$time1[2];
				
			$a = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn',
				'params'    => array(':rhid' => $idHis, ':gn'=>$graphNumberToChange),
				'order'     => 'Id'));
			foreach ($a as $aa) {
				$timeOldTable=$aa->time;
				$stationid=$aa->stations_id;
				$prof=$aa->prevention;
				if (($timeOldTable==$newTimeChange) && ($stationid==$stationChange))
				{
					$aa->prevention=null;
				}
				$aa->save();
			}	
			$result = array('success' => true );
			echo CJSON::encode($result);
		}
		//редагування з зперерахуноком рейсу згідно періоду доби
               /*routeid:7
                idHis:1089
                action:17
                grchange:1
                flightchange:6
                fieldchange:211
                newValuechange:14:35:00
                oldValuechange:14:33:00
                addDinDur:0*/
        if ($action==17){
            $dayIntervalId=Yii::app()->request->getParam('dayIntervalId');
            if ($newValueChange!=null) {
                //переводимо час в секунди newValue
                $time1=explode(":", $newValueChange);
                $res =  mktime($time1[0], $time1[1],$time1[2]);
                $newTimeChange=$time1[0]*60*60+$time1[1]*60+$time1[2];
            }
            if ($oldValueChange!=null) {
                //переводимо час в секунди oldValue
                $time2=explode(":", $oldValueChange);
                $res =  mktime($time2[0], $time2[1],$time2[2]);
                $oldTimeChange=$time2[0]*60*60+$time2[1]*60+$time2[2];
            }

            if ($oldValueChange!=null) {
                //визначаемо рейс зміни
                $flight = RouteTimeTable::model()->findAll(array(
                    'condition' => 'routes_history_id = :rhid AND graphs_number = :gn AND time =:t AND stations_id = :st',
                    'params' => array(
                        ':rhid' => $idHis,
                        ':gn' => $graphNumberToChange,
                        ':t' => $oldTimeChange,
                        ':st' => $stationChange),
                    'order' => 'Id'));
                $arrayFlightStation = RouteTimeTable::model()->findAll(array(
                    'condition' => 'routes_history_id = :rhid AND graphs_number = :gn and flights_number = :fnum and t.number >= :num',
                    'params' => array(
                        ':rhid' => $idHis,
                        ':gn' => $graphNumberToChange,
                        ':fnum' => $flight[0]->flights_number,
                        ':num' => $flight[0]->number
                    ),
                    'order' => 'Id'
                ));
                $arrayDayInterval = DayIntervalStations::model()->findAll(array(
                    'condition' => 'day_interval_city_id =:dicity',
                    'params' => array(':dicity' => $dayIntervalId)
                ));

                foreach ($arrayFlightStation as $recordFlightStation){
                    foreach($arrayDayInterval as $recordDayInterval){
                        if ($recordFlightStation->stations_id == $recordDayInterval->stations_id_from){
                            if ($recordFlightStation->number == $flight[0]->number){
                                $recordFlightStation->time = $newTimeChange;
                                $recordFlightStation->save();
                                $lastTime = $newTimeChange;
                                $addTime = $recordDayInterval->interval;
                                continue;
                            }
                            else{
                                $recordFlightStation->time = $lastTime + $addTime;
                                $recordFlightStation->save();
                                $lastTime = $lastTime + $addTime;
                                $addTime = $recordDayInterval->interval;
                                continue;
                            }
                        }
                        else{

                        }
                    }
                }
                $res = 0;
            }
        }
        //редагування з зперерахуноком рейсу згідно періоду доби
	}
}
?>