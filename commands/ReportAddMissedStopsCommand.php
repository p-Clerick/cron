<?php
Yii::import('application.models.*');
class ReportAddMissedStopsCommand extends CConsoleCommand
{
	public function run($dateToRecalc) {
        list($rewiew, $countDate) = getRewiew($dateToRecalc);

 		
 	
 		for ($cd=0; $cd < $countDate; $cd++) {
 			$startTimeReport[$cd]=time();
 			if ($rewiew[$cd]!=null) {//якщо треба перерахувати вручну за якийсь день
 		   		$dayToCalc[$cd]=$rewiew[$cd];//присвоюємо час що ввели вручну
 		   	}//якщо перерахунок вручну
 	    	if ($rewiew[$cd]==null){//робимо вночі кожного дня
 	    		$dayToCalc[$cd]=date("Y-m-d",strtotime ("yesterday"));//присвоюємо час що відповідає вчорашньому дню
 	    	}//if calc yesterday
			//сам розрахунок
 			//вибираемо ті точки, які не зловлено

 			$newFrom = strtotime($dayToCalc[$cd])+(2*3600);
			$newTo   = strtotime($dayToCalc[$cd])+(26*3600);
			$locFli = LocationsFlight::model()->findAll(array(
					'condition'=> 'unixtime >= :f AND unixtime <= :t',
					'params'   =>array(':f'=>$newFrom, ':t'=>$newTo)));
			foreach ($locFli as $lf) {
				$arrayGraphsLocations[$lf->graphs_id][$lf->flights_number][$lf->stations_id]=$lf->unixtime;
			}

	 		$y=ReportCachStops::model()->findAll(array(
				'condition'=> 'date = :date',
				'params'   =>array(':date' => $dayToCalc[$cd]),
				'order'    => 'id'));
			foreach ($y as $k) {
				$arRecord[]=$k->id;
			}
			for ($as=0; $as < count($arRecord) ; $as++) { 
				$idRecord=$arRecord[$as];
			

				$a=ReportCachStops::model()->findAll(array(
					'condition'=> 'id = :id',
					'params'   =>array(':id' => $idRecord),
					'order'    => 'id'));
				foreach ($a as $k) {
					$rows=array(
							'idRecord'=>$k->id,
							'date'=>$k->date,
							'lt'=>$k->latitude,
							'ln'=>$k->longitude,
							'stations_id'=>$k->stations_id,
							'stationsName'=>$k->stations->name,
							'poesName'=>$k->poe->name,
							'routeId'=>$k->routes_id,
							'routeName'=>$k->route->name,
							'graphsId'=>$k->graphs_id,
							'graphsName'=>$k->graph->name,
							'bortsid'=>$k->borts_id,
							'bortsName'=>$k->bort->state_number,
							'fln'=>$k->flights_number,
							'arrival_plan'=>$k->arrival_plan,
							'comment'=>$k->comment,
							'schedules_id'=>$k->amount
						);
				}
				$dateNeedUnixtime=strtotime($rows['date'])+$rows['arrival_plan'];
				$dateNeedUnixtimeFrom=strtotime($rows['date'])+$rows['arrival_plan']-(15*60);
				$dateNeedUnixtimeTo=strtotime($rows['date'])+$rows['arrival_plan']+(15*60);
				$stationsId=$rows['stations_id'];
				$bortsId=$rows['bortsid'];
				$b[$idRecord]=Yii::app()->db->createCommand("SELECT * from stations_locations where stations_id=".$stationsId." AND borts_id=".$bortsId." AND unixtime>=".$dateNeedUnixtimeFrom." AND unixtime<=".$dateNeedUnixtimeTo." order by unixtime")->queryAll();
				if (count($b[$idRecord])==1) {
					foreach ($b[$idRecord] as $key => $value) {
						$t= new LocationsFlights;
								$t->routes_id=$rows['routeId'];
								$t->graphs_id=$rows['graphsId'];
								$t->borts_id=$rows['bortsid'];
								$t->schedules_id=$rows['schedules_id'];
								$t->stations_id=$rows['stations_id'];
								$t->flights_number=$rows['fln'];
								$t->unixtime=$value['unixtime'];
								$t->time_difference=round(($dateNeedUnixtime-$value['unixtime'])/60,0);
								$t->arrival_plan=$rows['arrival_plan'];
								$t->save();
								echo $www=$t->id;echo "___";
						$r=ReportCachStops::model()->findByAttributes(array('id'=>$idRecord));
						$r->comment='('.round(($dateNeedUnixtime-$value['unixtime'])/60,0).')'.strftime('%H:%M:%S',$value['unixtime'])." yes station_voice ".$r->comment;
						$r->save();
					}
				}
				else if (count($b[$idRecord]==0)) {
					$tdfrom=strtotime($rows['date'])+$rows['arrival_plan']-600;
					$tdto=strtotime($rows['date'])+$rows['arrival_plan']+600;
					$difference=0.06;
					$minLong=$rows['ln']-$difference;
					$maxLong=$rows['ln']+$difference;
					$minLat=$rows['lt']-$difference;
					$maxLat=$rows['lt']+$difference;
					$loc=Locations::model()->findAll(array(
							'condition'=> 'unixtime >= :f AND unixtime <= :t AND borts_id = :g AND longitude >= :lnF AND longitude <= :lnT AND latitude >= :latF AND latitude <= :latT',
							'params'   =>array(':f' => $tdfrom,':t'=>$tdto, ':g'=>$rows['bortsid'], ':lnF'=>$minLong, ':lnT'=>$maxLong, ':latF'=>$minLat, ':latT'=>$maxLat),
							'order'    => 'unixtime'));
					foreach ($loc as $k) {
						$arrayLocation[]=array(
							'long'=>$k->longitude,
							'difLong'=>abs(round($rows['ln']-$k->longitude,4)),
							'difLat'=>abs(round($rows['lt']-$k->latitude,4)),
							'lat'=>$k->latitude,
							'unixtime'=>$k->unixtime,
							'unixtimeNormView'=>strftime('%Y-%m-%d %H:%M:%S',$k->unixtime)
						);
					}
					$minDifLn=$difference;
					$minDifLt=$difference;
					if (!isset($arrayLocation)) {
						$r=ReportCachStops::model()->findByAttributes(array('id'=>$idRecord));
						$r->comment='no Locations'." no station_voice ".$r->comment;
						$r->save();
					}
					if (isset($arrayLocation)) {//перевірка на найменше відхилення
						for ($i=0; $i <count($arrayLocation) ; $i++) { 
							if (($arrayLocation[$i]['difLong']<$minDifLn) && ($arrayLocation[$i]['difLat']<$minDifLt)) {
								
								
									$minDifLn=$arrayLocation[$i]['difLong'];
									$minDifLt=$arrayLocation[$i]['difLat'];
									$insert=$arrayLocation[$i];
								
							}
						}
					
						//print_r($insert);
						if (isset($insert)) {
							if (isset($arrayGraphsLocations[$rows['graphsId']][$rows['fln']][$rows['stations_id']])) {
								$r=ReportCachStops::model()->findByAttributes(array('id'=>$idRecord));
								$r->comment='already exist';
								$r->save();
							}
							if (!isset($arrayGraphsLocations[$rows['graphsId']][$rows['fln']][$rows['stations_id']])) {
								$t= new LocationsFlights;
								$t->routes_id=$rows['routeId'];
								$t->graphs_id=$rows['graphsId'];
								$t->borts_id=$rows['bortsid'];
								$t->schedules_id=$rows['schedules_id'];
								$t->stations_id=$rows['stations_id'];
								$t->flights_number=$rows['fln'];
								$t->unixtime=$insert['unixtime'];
								$t->time_difference=round((strtotime($rows['date'])+$rows['arrival_plan']-$insert['unixtime'])/60,0);
								$t->arrival_plan=$rows['arrival_plan'];
								$t->save();
								echo $www=$t->id;echo "___";
								$r=ReportCachStops::model()->findByAttributes(array('id'=>$idRecord));
								$r->comment='('.round((strtotime($rows['date'])+$rows['arrival_plan']-$insert['unixtime'])/60,0).')'.strftime('%H:%M:%S',$insert['unixtime'])." no station_voice ".$r->comment;
								$r->save();
							}	
						}
						if (!isset($insert)) {
							$r=ReportCachStops::model()->findByAttributes(array('id'=>$idRecord));
							$r->comment='no locations near 0.06'." no station_voice ".$r->comment;
							$r->save();
						}
					}
					unset($arrayLocation);
					unset($insert);
				}
			/*	//print_r($rows);
				$tdfrom=strtotime($rows['date'])+$rows['arrival_plan']-600;
				$tdto=strtotime($rows['date'])+$rows['arrival_plan']+600;
				$difference=0.06;
				$minLong=$rows['ln']-$difference;
				$maxLong=$rows['ln']+$difference;
				$minLat=$rows['lt']-$difference;
				$maxLat=$rows['lt']+$difference;
				$loc=Locations::model()->findAll(array(
						'condition'=> 'unixtime >= :f AND unixtime <= :t AND borts_id = :g AND longitude >= :lnF AND longitude <= :lnT AND latitude >= :latF AND latitude <= :latT',
						'params'   =>array(':f' => $tdfrom,':t'=>$tdto, ':g'=>$rows['bortsid'], ':lnF'=>$minLong, ':lnT'=>$maxLong, ':latF'=>$minLat, ':latT'=>$maxLat),
						'order'    => 'unixtime'));
				foreach ($loc as $k) {
					$arrayLocation[]=array(
						'long'=>$k->longitude,
						'difLong'=>abs(round($rows['ln']-$k->longitude,4)),
						'difLat'=>abs(round($rows['lt']-$k->latitude,4)),
						'lat'=>$k->latitude,
						'unixtime'=>$k->unixtime,
						'unixtimeNormView'=>strftime('%Y-%m-%d %H:%M:%S',$k->unixtime)
					);
				}
				$minDifLn=$difference;
				$minDifLt=$difference;
				if (!isset($arrayLocation)) {
					$r=ReportCachStops::model()->findByAttributes(array('id'=>$idRecord));
					$r->comment='no data in table Locations';
					$r->save();
				}
				if (isset($arrayLocation)) {//перевірка на найменше відхилення
					for ($i=0; $i <count($arrayLocation) ; $i++) { 
						if (($arrayLocation[$i]['difLong']<$minDifLn) && ($arrayLocation[$i]['difLat']<$minDifLt)) {
							
							
								$minDifLn=$arrayLocation[$i]['difLong'];
								$minDifLt=$arrayLocation[$i]['difLat'];
								$insert=$arrayLocation[$i];
							
						}
					}
				
					//print_r($insert);
					if (isset($insert)) {
						if (isset($arrayGraphsLocations[$rows['graphsId']][$rows['fln']][$rows['stations_id']])) {
							$r=ReportCachStops::model()->findByAttributes(array('id'=>$idRecord));
							$r->comment='already exist';
							$r->save();
						}
						if (!isset($arrayGraphsLocations[$rows['graphsId']][$rows['fln']][$rows['stations_id']])) {
							$t= new LocationsFlights;
							$t->routes_id=$rows['routeId'];
							$t->graphs_id=$rows['graphsId'];
							$t->borts_id=$rows['bortsid'];
							$t->schedules_id=$rows['schedules_id'];
							$t->stations_id=$rows['stations_id'];
							$t->flights_number=$rows['fln'];
							$t->unixtime=$insert['unixtime'];
							$t->time_difference=round((strtotime($rows['date'])+$rows['arrival_plan']-$insert['unixtime'])/60,0);
							$t->arrival_plan=$rows['arrival_plan'];
							$t->save();
							$r=ReportCachStops::model()->findByAttributes(array('id'=>$idRecord));
							$r->comment='('.round((strtotime($rows['date'])+$rows['arrival_plan']-$insert['unixtime'])/60,0).')'.strftime('%H:%M:%S',$insert['unixtime']);
							$r->save();
						}	
					}
					if (!isset($insert)) {
						$r=ReportCachStops::model()->findByAttributes(array('id'=>$idRecord));
						$r->comment='no locations near 0.06';
						$r->save();
					}
				}
				unset($arrayLocation);
				unset($insert);*/

			}
			unset($arrayGraphsLocations);
			unset($arRecord);
			$success[$cd]='Y';
			$endTimeReport[$cd]=time();
			$cdPlusOne=$cd+1;
			$message[$cd]="calc report on day ".$dayToCalc[$cd]." ".$cdPlusOne." from ".$countDate;
			$newRecordReport = new ExecutionsCommands;
			$newRecordReport->date=date("Y-m-d");
			$newRecordReport->commands_id=14;
			$newRecordReport->start_time=$startTimeReport[$cd];
			$newRecordReport->end_time=$endTimeReport[$cd];
			$newRecordReport->duration=$endTimeReport[$cd]-$startTimeReport[$cd];
			$newRecordReport->success=$success[$cd];
			$newRecordReport->comment=$message[$cd];
			$newRecordReport->save();
		}//for ($cd=0; $cd < $countDate; $cd++)
	}
}
?>