<?php
class RouteCalculationStationsController extends Controller {
	public function actionCreate() {
error_log('start function actionCreate ...'); //20210523
error_log(ini_get('max_execution_time'));
		//отримуємо дані з форми вхідних даних для розрахунку
		$N = Yii::app()->request->getParam('kilavto');             //кількість автобусів
		$Tprm=Yii::app()->request->getParam('pochatokruhuchas');  //час початку руху на маршруті
		$routeMethod=Yii::app()->request->getParam('vydruhu');        //вид руху маршруту
		$routeid=Yii::app()->request->getParam('routeid');
    	$scheduleTypeId=Yii::app()->request->getParam('graph_type');
    	$KTVid=Yii::app()->request->getParam('cotrolPointVid');
		$KTDo=Yii::app()->request->getParam('cotrolPointDo');
		$KTObidu=Yii::app()->request->getParam('cotrolPointObidu');
		$calcMethods=Yii::app()->request->getParam('calcMethod');

 //звірка з видом руху з таблиці
		if ($routeMethod==Yii::app()->session['MoveTypeLine']){$rM=1;}
		if ($routeMethod==Yii::app()->session['MoveTypeRound']){$rM=2;}
		if ($routeMethod==Yii::app()->session['MoveTypeMixed']){$rM=3;}
		if ($calcMethods==Yii::app()->session['CalculationText'].' 1'){$cal=1;}
		if ($calcMethods==Yii::app()->session['CalculationText'].' 2'){$cal=2;}
		if ($calcMethods==Yii::app()->session['CalculationText'].' 3'){$cal=3;}

//вибираємо перевізника		
		$routeCarrier = Route:: model()->findByAttributes(array('id'=>$routeid));
		$carrierId=$routeCarrier->carriers_id;
	
//________________________________________________________новий запис в графс_______
		for ($n=1; $n<=$N; $n++)
		{
			$a = Graphs::model()->findByAttributes(array('routes_id' => $routeid, 'name' => $n));
			if (isset($a)) $grafikidFor=$a->id;
			if (!isset($grafikidFor))
			{
				$newsGraph = new Graphs;
				$newsGraph->routes_id = $routeid;
				$newsGraph->carriers_id = $carrierId;
				$newsGraph->name = $n;
				$newsGraph->save();
			}
		}

		//cтворюємо дату в таблиці сьогоднішню
		$today = date('Y-m-d H:i:s');

	
		//переводимо час початку роботи в секунди
		$time=explode(":", $Tprm);
		$result =  mktime($time[0], $time[1],$time[2]);
		$startTime=$time[0]*60*60+$time[1]*60+$time[2];

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
error_log('countSR: '.$countSR);
		for ($i=0; $i < $countSR; $i++) { 
			if ($KTObidu==$arraySR[$i]['stationId']) {
				$KTObiduNumber=$arraySR[$i]['number'];
			}
			if ($KTVid==$arraySR[$i]['stationId']) {
				$KTVidNumber=$arraySR[$i]['number'];
			}
			if ($KTDo==$arraySR[$i]['stationId']) {
				$KTDoNumber=$arraySR[$i]['number'];
			}
		}
		if ($KTObiduNumber==$countSR) {
			$KTObiduNumber=0;
		}
		if ($KTDoNumber==$countSR) {
			$KTDoNumber=0;
		}
		
		//шукаемо інтервали між зупинками
		for ($i=0; $i < $countDayIntType; $i++){
			for ($a=0; $a < $countSR; $a++) { 
				if ($a!=$countSR-1) {
					$d = DayIntervalStations::model()->findByAttributes(array(
						'stations_id_from'=>$arraySR[$a]['stationId'],
						'stations_id_to'=>$arraySR[$a+1]['stationId'],
						'day_interval_city_id'=>$dayIntType[$i]['id']
					));
					
error_log('$a!=$countSR-1 a: '.$a.' countSR: '.$countSR);
error_log('$d[\'stations_id_from\']: '.$d['stations_id_from']);
error_log('$d[\'stations_id_to\']: '.$d['stations_id_to']);
error_log('$d[\'day_interval_city_id\']: '.$d['day_interval_city_id']);
					$arrayInterval[$i][]=$d->interval;
				}
				if ($a==$countSR-1) {
					$d = DayIntervalStations::model()->findByAttributes(array(
						'stations_id_from'=>$arraySR[$a]['stationId'],
						'stations_id_to'=>$arraySR[0]['stationId'],
						'day_interval_city_id'=>$dayIntType[$i]['id']
					));
error_log('$a==$countSR-1 a: '.$a.' countSR: '.$countSR);
error_log('$d[\'stations_id_from\']: '.$d['stations_id_from']);
error_log('$d[\'stations_id_to\']: '.$d['stations_id_to']);
error_log('$d[\'day_interval_city_id\']: '.$d['day_interval_city_id']);
					$arrayInterval[$i][]=$d->interval;
				}
			}
		}

		//час оборотного рейсу
		$tobreisu=array_sum($arrayInterval[0]);
		//інтервал між авто в секундах
		$T=round($tobreisu/$N,0);
		//вводимо сталі величини для розрахунку
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

		//___________________________________________________________________________________розрахунок 1
		if ($calcMethods == Yii::app()->session['CalculationText'].' 1') {
			for ($n=1; $n<=$N; $n++) {
				$vyyzd=$startTime+$T*($n-1);
				$flightNumber=1;
				for ($i=0; $i<=$countDayIntType; $i++)//до кожного періоду доби
				{
					do {
						for ($a=0; $a <$countSR; $a++) {
							$stationId=$arraySR[$a]['stationId'];
							$stationScenarioId=$arraySR[$a]['scenarioId'];
							$stationNumber=$arraySR[$a]['number'];
							$oborot=array_sum($arrayInterval[$i]);

							$e=$vyyzd-$startTime-$T*($n-1);
error_log('$oborot '.$oborot);
error_log('$vyyzd '.$vyyzd);
error_log('$startTime '.$startTime);
error_log('$T '.$T);
error_log('$n '.$n);
error_log('$e=$vyyzd-$startTime-$T*($n-1) '.$e);
error_log('$timeToDinnerNorma '.$timeToDinnerNorma);
error_log('$flightNumber '.$flightNumber);
error_log('$stationNumber '.$stationNumber);
error_log('$KTVidNumber '.$KTVidNumber);


							if ($e<$timeToDinnerNorma-$oborot)
							{
								if ($flightNumber==1)
								{
									if ($stationNumber<$KTVidNumber)
									{
										$k=1;
										$vyyzd=$vyyzd-$arrayInterval[$i][$a];
									}

									if ($stationNumber==$KTVidNumber)
									{
										$k=2;
										$vyyzd=$vyyzd;
									}
									if ($stationNumber>$KTVidNumber) {
											$k=2;
											$vyyzd=$vyyzd;
										}
									$fll=$flightNumber;
								}
								if ($flightNumber>1)
								{
									$k=2;
									$vyyzd=$vyyzd;
									$fll=$flightNumber;
								}
							}

							if (($e>=$timeToDinnerNorma-$oborot) && ($e<$timeToDinnerNorma+3600/($N+1)*$n))
							{
								$k=2;
								$vyyzd=$vyyzd;
								if (($flightNumber==$fll)&&($stationNumber==$KTObiduNumber+1))
								{
									$vyyzd=$vyyzd+3600/($N+1)*$n-$arrayInterval[$i][$a];
									$d1[$n]=1;
									$dinnerEnd=$vyyzd;
								}
								if (($flightNumber==$fll+1)&&($stationNumber==$KTObiduNumber+1)&&($d1[$n]!=1))
								{
									$vyyzd=$vyyzd+3600/($N+1)*$n-$arrayInterval[$i][$a];
									$dinnerEnd=$vyyzd;
								}
								$fll1=$flightNumber;	
							}

							if (($e>=$timeToDinnerNorma+3600/($N+1)*$n) && ($e<$shiftDurationNorma+3600+$timeToDinnerNorma-$oborot))
							{
								$k=2;
								$vyyzd=$vyyzd;
								if (($flightNumber==$fll1)&&($stationNumber==$KTObiduNumber+1))
								{
									$vyyzd=$vyyzd+3600/($N+1)*($N-$n+1)-$arrayInterval[$i][$a];
									$d2[$n]=1;
									$dinnerEnd=$vyyzd;
								}
								if (($flightNumber==$fll1+1)&&($stationNumber==$KTObiduNumber+1)&&($d2[$n]!=1))
								{
									$vyyzd=$vyyzd+3600/($N+1)*($N-$n+1)-$arrayInterval[$i][$a];
									$dinnerEnd=$vyyzd;
								}
								$fll2=$flightNumber;
							}

							if (($e>=$shiftDurationNorma+3600+$timeToDinnerNorma-$oborot) && ($e<$shiftDurationNorma+3600+$timeToDinnerNorma+3600/($N+1)*$n))
							{
								$k=2;
								$vyyzd=$vyyzd;
								if (($flightNumber==$fll2)&&($stationNumber==$KTObiduNumber+1))
								{
									$vyyzd=$vyyzd+3600/($N+1)*$n-$arrayInterval[$i][$a];
									$d3[$n]=1;
									$dinnerEnd=$vyyzd;
								}
								if (($flightNumber==$fll2+1)&&($stationNumber==$KTObiduNumber+1)&&($d3[$n]!=1))
								{
									$vyyzd=$vyyzd+3600/($N+1)*$n-$arrayInterval[$i][$a];
									$dinnerEnd=$vyyzd;
								}
								$fll3=$flightNumber;
							}

							if (($e>=$shiftDurationNorma+3600+$timeToDinnerNorma+3600/($N+1)*$n) && ($e<$shiftDurationNorma*2+3600*4))	
							{
								$k=2;
								$vyyzd=$vyyzd;
								if (($flightNumber==$fll3)&&($stationNumber==$KTObiduNumber+1))
								{
									$vyyzd=$vyyzd+3600/($N+1)*($N-$n+1)-$arrayInterval[$i][$a];
									$d4[$n]=1;
									$dinnerEnd=$vyyzd;
								}
								if (($flightNumber==$fll3+1)&&($stationNumber==$KTObiduNumber+1)&&($d4[$n]!=1))
								{
									$vyyzd=$vyyzd+3600/($N+1)*($N-$n+1)-$arrayInterval[$i][$a];
									$dinnerEnd=$vyyzd;
								}
							}

							if ($e>=$shiftDurationNorma*2+3600*4)
							{
								$k=2;
								$vyyzd=$vyyzd;
								if ($stationNumber==$KTDoNumber+1)
								{
									break(3);
								}
							}
							if ($k==2) {
								$insertdata[]=array(
									'graphNumber'=>$n, 
									'flightNumber'=>$flightNumber, 
									'stationId'=>$stationId,
									'stationNumber'=>$stationNumber,
									'stationScenarioId'=>$stationScenarioId,
									'time'=>$vyyzd,
									'dinnerEnd'=>$dinnerEnd,
									'oborot'=>$oborot
								);
							}
							$vyyzd=$vyyzd+$arrayInterval[$i][$a];
						}
						$flightNumber=$flightNumber+1;
					}//do
					while ($vyyzd<=$dayIntType[$i]['end']); 
				}
			}
		}


		//___________________________________________________________________________________розрахунок 2
		if ($calcMethods == Yii::app()->session['CalculationText'].' 2') {
			for ($n=1; $n<=$N; $n++) {
				$vyyzd=$startTime+$T*($n-1);
				$flightNumber=1;
				for ($i=0; $i<=$countDayIntType; $i++)//до кожного періоду доби
				{
					do {
						for ($a=0; $a <$countSR; $a++) { 
							$stationId=$arraySR[$a]['stationId'];
							$stationScenarioId=$arraySR[$a]['scenarioId'];
							$stationNumber=$arraySR[$a]['number'];
							$oborot=array_sum($arrayInterval[$i]);

							$e=$vyyzd-$startTime-$T*($n-1);
							if ($n & 1) {
								if ($e<$timeToDinnerNorma) {
									if (($flightNumber==1)&&($e<$timeToDinnerNorma-$oborot)) {
										if ($stationNumber<$KTVidNumber) {
											$k=1;
											$vyyzd=$vyyzd-$arrayInterval[$i][$a];
										}
										if ($stationNumber==$KTVidNumber) {
											$k=2;
											$vyyzd=$vyyzd;
										}
										if ($stationNumber>$KTVidNumber) {
											$k=2;
											$vyyzd=$vyyzd;
										}
										$fll=$flightNumber;
									}
									if (($flightNumber>1)&&($e<$timeToDinnerNorma-$oborot))
									{
										$k=2;
										$vyyzd=$vyyzd;
										$fll=$flightNumber;
									}
									if ($e>=$timeToDinnerNorma-$oborot) {
										$k=2;
										$vyyzd=$vyyzd;
										if (($flightNumber==$fll) && ($stationNumber==$KTObiduNumber+1))
										{
											$vyyzd=$vyyzd+$dinnerDurationNorma-$arrayInterval[$i][$a];
											$d1=1;
											$dinnerEnd=$vyyzd;
										}
										if (($flightNumber==$fll+1) && ($stationNumber==$KTObiduNumber+1)&&($d1!=1))
										{
											$vyyzd=$vyyzd+$dinnerDurationNorma-$arrayInterval[$i][$a];
											$dinnerEnd=$vyyzd;
										}
									}
								}
								if (($e>=$timeToDinnerNorma)&&($e<$shiftDurationNorma+$timeToDinnerNorma+$dinnerDurationNorma-$oborot))
								{
									$k=2;
									$vyyzd=$vyyzd;
									$fll2=$flightNumber;
								}//menshe 3 hod-obreys dlya 2 zminu
								if (($e>=$shiftDurationNorma+$timeToDinnerNorma+$dinnerDurationNorma-$oborot) && ($e<$shiftDurationNorma*2+$dinnerDurationNorma*2+2*3600))
								{
									$k=2;
									$vyyzd=$vyyzd;
									if (($flightNumber==$fll2)&&($stationNumber==$KTObiduNumber+1))
									{
										$vyyzd=$vyyzd+$dinnerDurationNorma-$arrayInterval[$i][$a];
										$d2=1;
										$dinnerEnd=$vyyzd;
									}
									if (($flightNumber==$fll2+1)&&($stationNumber==$KTObiduNumber+1)&&($d2!=1))
									{
										$vyyzd=$vyyzd+$dinnerDurationNorma-$arrayInterval[$i][$a];
										$dinnerEnd=$vyyzd;
									}
								}//menshe 3 hod dlya 2 zminu
								if ($e>=$shiftDurationNorma*2+$dinnerDurationNorma*2+2*3600)
								{
									$k=2;
									$vyyzd=$vyyzd;
									if ($stationNumber==$KTDoNumber+1)
									{
										break(3);
									}
								}
							} else {
								if ($e<$timeToDinnerNorma+$oborot) {
									if (($flightNumber==1)&&($e<$timeToDinnerNorma)) {
										if ($stationNumber<$KTVidNumber) {
											$k=1;
											$vyyzd=$vyyzd-$arrayInterval[$i][$a];
										}
										if ($stationNumber==$KTVidNumber) {
											$k=2;
											$vyyzd=$vyyzd;
										}
										if ($stationNumber>$KTVidNumber) {
											$k=2;
											$vyyzd=$vyyzd;
										}
										$fll=$flightNumber;
									}
									if (($flightNumber>1)&&($e<$timeToDinnerNorma))
									{
										$k=2;
										$vyyzd=$vyyzd;
										$fll=$flightNumber;
									}
									if ($e>=$timeToDinnerNorma) {
										$k=2;
										$vyyzd=$vyyzd;
										if (($flightNumber==$fll)&&($stationNumber==$KTObiduNumber+1))
										{
											$vyyzd=$vyyzd+$dinnerDurationNorma-$arrayInterval[$i][$a];
											$d3=1;
											$dinnerEnd=$vyyzd;
										}
										if (($flightNumber==$fll+1)&&($stationNumber==$KTObiduNumber+1)&&($d3!=1))
										{
											$vyyzd=$vyyzd+$dinnerDurationNorma-$arrayInterval[$i][$a];
											$dinnerEnd=$vyyzd;
										}
									}
								}
								if (($e>=$timeToDinnerNorma+$oborot)&&($e<$shiftDurationNorma+$timeToDinnerNorma+$dinnerDurationNorma))
								{
									$k=2;
									$vyyzd=$vyyzd;
									$fll2=$flightNumber;
								}//menshe 3 hod-obreys dlya 2 zminu
								if (($e>=$shiftDurationNorma+$timeToDinnerNorma+$dinnerDurationNorma) && ($e<$shiftDurationNorma*2+$dinnerDurationNorma*2+2*3600+$oborot))
								{
									$k=2;
									$vyyzd=$vyyzd;
									if (($flightNumber==$fll2)&&($stationNumber==$KTObiduNumber+1))
									{
										$vyyzd=$vyyzd+$dinnerDurationNorma-$arrayInterval[$i][$a];
										$d4=1;
										$dinnerEnd=$vyyzd;
									}
									if (($flightNumber==$fll2+1)&&($stationNumber==$KTObiduNumber+1)&&($d4!=1))
									{
										$vyyzd=$vyyzd+$dinnerDurationNorma-$arrayInterval[$i][$a];
										$dinnerEnd=$vyyzd;
									}
								}//menshe 3 hod dlya 2 zminu
								if ($e>=$shiftDurationNorma*2+$dinnerDurationNorma*2+2*3600+$oborot)
								{
									$k=2;
									$vyyzd=$vyyzd;
									if ($stationNumber==$KTDoNumber+1)
									{
										break(3);
									}
								}
							}
							
							
							
							
							if ($k==2)
							{
								$insertdata[]=array(
									'graphNumber'=>$n, 
									'flightNumber'=>$flightNumber, 
									'stationId'=>$stationId,
									'stationNumber'=>$stationNumber,
									'stationScenarioId'=>$stationScenarioId,
									'time'=>$vyyzd,
									'dinnerEnd'=>$dinnerEnd,
									'oborot'=>$oborot
								);
							}
							$vyyzd=$vyyzd+$arrayInterval[$i][$a];
							
						}//для кожної точки
						$flightNumber=$flightNumber+1;
					}//do
					while ($vyyzd<=$dayIntType[$i]['end']); 
				}//до кожного періоду доби
			}//for ($n=1; $n<=$N; $n++)
		}//if ($calcMethods == 'розрахунок 2')

		//___________________________________________________________________________________розрахунок 3
		if ($calcMethods == Yii::app()->session['CalculationText'].' 3') {
			for ($n=1; $n<=$N; $n++) {
				$vyyzd=$startTime+$T*($n-1);
				$flightNumber=1;
				for ($i=0; $i<=$countDayIntType; $i++)//до кожного періоду доби
				{
					do {
						for ($a=0; $a <$countSR; $a++) { 
							$stationId=$arraySR[$a]['stationId'];
							$stationScenarioId=$arraySR[$a]['scenarioId'];
							$stationNumber=$arraySR[$a]['number'];
							$oborot=array_sum($arrayInterval[$i]);
							
							$e=$vyyzd-$startTime-$T*($n-1);
							if ($e<$shiftDurationNorma*2+3*3600) {
							//if ($e<$shiftDurationNorma*2) {	
								if ($flightNumber==1) {
									if ($stationNumber<$KTVidNumber) {
											$k=1;
											$vyyzd=$vyyzd-$arrayInterval[$i][$a];
										}
										if ($stationNumber==$KTVidNumber) {
											$k=2;
											$vyyzd=$vyyzd;
										}
										if ($stationNumber>$KTVidNumber) {
											$k=2;
											$vyyzd=$vyyzd;
										}
								}//if ($flightNumber==1)
								if ($flightNumber>1)
								{
									$k=2;
									$vyyzd=$vyyzd;
								}
							}
							if ($e>=$shiftDurationNorma*2+3*3600)
							//if ($e>=$shiftDurationNorma*2)	
							{
								$k=2;
								$vyyzd=$vyyzd;
								if ($stationNumber==$KTDoNumber+1)
								{
									break(3);
								}
							}// закриваюча до якщо наїзд більше тривалості двох змін
							if ($k==2)
							{
								$insertdata[]=array(
									'graphNumber'=>$n, 
									'flightNumber'=>$flightNumber, 
									'stationId'=>$stationId,
									'stationNumber'=>$stationNumber,
									'stationScenarioId'=>$stationScenarioId,
									'time'=>$vyyzd,
									'oborot'=>$oborot,
									'dinnerEnd'=>"ne"
								);
							}
							$vyyzd=$vyyzd+$arrayInterval[$i][$a];
							
						}//для кожної точки
						$flightNumber=$flightNumber+1;
					}//do
					while ($vyyzd<=$dayIntType[$i]['end']); 
				}//до кожного періоду доби
			}//for ($n=1; $n<=$N; $n++)
		}//if ($calcMethods == 'розрахунок 3')


		//вставляємо дані в таблицю route_calc_init_data
		$historySave= new RouteHistoryIdAll;
		$historySave->routes_id=$routeid;
		$historySave->amount=$N;
		$historySave->start_time=$Tprm;
		$historySave->move_method=$rM;
		$historySave->calc_method=$cal;
		$historySave->schedules_type_id=$scheduleTypeId;
		$historySave->start_station_id=$KTVid;
		$historySave->end_station_id=$KTDo;
		$historySave->dinner_station_id=$KTObidu;
		$historySave->date_create=$today;
		$historySave->save();
		$idHis=$historySave->Id;
		$countData=count($insertdata);

		//вставляємо обіди
		for ($i=0; $i < $countData; $i++) { 
			if ($insertdata[$i]['dinnerEnd']==$insertdata[$i]['time']) {
				$insertdata[$i-1]['ds']=$insertdata[$i-1]['time'];
				$insertdata[$i-1]['de']=$insertdata[$i]['time'];
			}
		}

		//вставляємо перезміни
		for ($i=1; $i < $countData; $i++) { 
			if ($insertdata[$i]['graphNumber']==1) {
				if ($insertdata[$i]['time']<=9*3600+$startTime) {
					if ($insertdata[$i]['stationNumber']==$countSR) {
						$workChangeFlight=$insertdata[$i]['flightNumber'];
						$workChangeStationNumber=$countSR;
					}
				}
			}
		}
		for ($i=1; $i < $countData; $i++) { 
			if ($insertdata[$i]['flightNumber']==$workChangeFlight) {
				if ($insertdata[$i]['stationNumber']==$workChangeStationNumber) {
					$insertdata[$i]['ws']=$insertdata[$i]['time'];
				} 
			}
		}
		//$result = array('success' => $countData);
		//echo CJSON::encode($result); //20210523
			

error_log('countData: '.$countData); //20210523
		//вставляємо дані в таблицю route_calc_schedules		
		for ($i=0; $i<$countData; $i++)
		{
$frtime = microtime(true);

			$t=floor($insertdata[$i]['time']);
error_log('t: '.$t); //20210523
			if ($t>24*3600){$t=$t-24*3600;}
error_log('if t>24: '.$t); //20210523
			if (isset($insertdata[$i]['ds'])) {
				$ds=floor($insertdata[$i]['ds']);
				$de=floor($insertdata[$i]['de']);
				if ($ds>24*3600){$ds=$ds-24*3600;}
				if ($de>24*3600){$de=$de-24*3600;}
			}
			if (!isset($insertdata[$i]['ds'])) {
				$ds=null;
				$de=null;
			}
			if (isset($insertdata[$i]['ws'])) {
				$ws=floor($insertdata[$i]['ws']);
				if ($ws>24*3600){$ws=$ws-24*3600;}
			}
			if (!isset($insertdata[$i]['ws'])) {
				$ws=null;
			}
error_log('ws: '.$ws); //20210523
error_log('ds: '.$ds); //20210523
error_log('de: '.$de); //20210523
			$routeTimeSave = new RouteTimeTable;
			$routeTimeSave->routes_history_id=$idHis;
			$routeTimeSave->graphs_number=$insertdata[$i]['graphNumber'];
			$routeTimeSave->flights_number=$insertdata[$i]['flightNumber'];
			$routeTimeSave->time=$t;
			$routeTimeSave->stations_id=$insertdata[$i]['stationId'];
			$routeTimeSave->stations_scenario_id=$insertdata[$i]['stationScenarioId'];
			$routeTimeSave->number=$insertdata[$i]['stationNumber'];
			$routeTimeSave->dinner_start=$ds;
			$routeTimeSave->dinner_end=$de;
			$routeTimeSave->workshift_start=$ws;
			$routeTimeSave->workshift_end=$ws;
			$routeTimeSave->duration_flight=$insertdata[$i]['oborot'];
			$routeTimeSave->save();
		}

		

		for ($i=0; $i < $N; $i++) { 
error_log('i vs N: '.$i.' to '.$N); //20210523			
			$t = new RouteGraphOrderTimeTable;
			$t->routes_id=$routeid;
			$t->historys_id=$idHis;
			$t->graphs_number=$i+1;
			$t->attendance=15*60;
			$t->depotTo=15*60;
			$t->depotAfter=15*60;
			$t->typeWork=1;
			$t->save();
error_log('t: '.$t['routes_id']);
		}
		$ttime = microtime(true);
			$elapsed = $ttime - $frtime;
//file_put_contents("performance.txt", $elapsed);
		
		$result = array('success' => true);
		echo CJSON::encode($result);
error_log('end function actionCreate ...'); //20210523
	}
	public function actionRead($id)
	{
error_log('start function actionRead ...'); //20210523
		$routeid = $_GET['routeid'];
		$recordCarrier = $_GET['record'];
		$findrecord = $_GET['findrecord'];
		$scheduleTypeId = $_GET['scheduleTypeId'];
		$formLoad = $_GET['formLoad'];


		if ($formLoad==1)//знаходимо дані для заповнення вікна початковими параметрами
		{
			$a = RouteHistoryIdAll::model()->findAll(array(
				'condition'=> 'routes_id = :rid',
				'params'   =>array(':rid' => $routeid),
				'order'    => 'Id'));
			foreach ($a as $aa)
			{
				$N=$aa->amount;
				$calcMethod=$aa->calc_method;
				$routeMethod=$aa->move_method;
				$startTime=$aa->start_time;
				$graphType=$aa->schedules_type_id;
				$KTVid=$aa->start_station_id;
				$KTDo=$aa->end_station_id;
				$KTObidu=$aa->dinner_station_id;
			}
			
			if ($routeMethod==1){$routeMethod=Yii::app()->session['MoveTypeLine'];}
			if ($routeMethod==2){$routeMethod=Yii::app()->session['MoveTypeRound'];}
			if ($routeMethod==3){$routeMethod=Yii::app()->session['MoveTypeMixed'];}
			if ($calcMethod==1){$calcMethod=Yii::app()->session['CalculationText'].' 1';}
			if ($calcMethod==2){$calcMethod=Yii::app()->session['CalculationText'].' 2';}
			if ($calcMethod==3){$calcMethod=Yii::app()->session['CalculationText'].' 3';}

			$result = array(
				'success' => true,
				'data' => array(
					'kilavto' => $N,
					'calcMethod' => $calcMethod,
					'vydruhu' => $routeMethod,
					'pochatokruhuchas' => $startTime,
					'graph_type' => $scheduleTypeId,
					'cotrolPointVid' => $KTVid,
					'cotrolPointDo' => $KTDo,
					'cotrolPointObidu' => $KTObidu)
				);
			echo CJSON::encode($result);
		}//знаходимо дані для заповнення вікна початковими параметрами

//встановлюємо перевізника для графіків і роуту		
		if ($recordCarrier!=0)
		{
			$a = Route::model()->findByAttributes(array('id'=>$routeid));
			$a->carriers_id=$recordCarrier;
			$a->save();
			$b = Graphs::model()->findAll(array(
				'condition'=> 'routes_id = :rid',
				'params'   =>array(':rid' => $routeid),
				'order'    => 'id'));
			foreach ($b as $bb)
			{
				$bb->carriers_id=$recordCarrier;
				$bb->save();
			}
			$result = array(
				'success' => true,
				'carrier' => $recordCarrier
				);
			echo CJSON::encode($result);
		}
//вибираємо перевізника для повідомлення при встановленні нового перевізника		
		if ($findrecord==1)
		{
			$a = Route::model()->findByAttributes(array('id'=>$routeid));
			$b = $a->carriers_id;
			$result = array(
				'success' => true,
				'carrier' => $b
				);
			echo CJSON::encode($result);
		}
	
error_log('end function actionRead ...'); //21010523
	}
}
?>
