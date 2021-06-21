<?php
class DoNotCachStopsController extends Controller  {
	public function actionRead()//на посилання з гет
	{
		$level=Yii::app()->request->getParam('level');
		$nodeId=Yii::app()->request->getParam('recordIdLevel');
		$fromDate=Yii::app()->request->getParam('fromDate');
		$toDate=Yii::app()->request->getParam('toDate');
		if ($level==1) {
			$numnpp=1;
			$a=ReportCachStops::model()->with('poe','route','graph','bort','stations')->findAll(array(
				'condition'=> 'date >= :f AND date <= :t',
				'params'   =>array(':f'=>$fromDate, ':t'=>$toDate),
				'order'    => 't.id'));
			foreach ($a as $k) {
				$timeTable= new Time($k->arrival_plan);
				$rows[]=array(
					'idRecord'=>$k->id,
					'npp'=>$numnpp,
					'date'=>$k->date,
					'lt'=>$k->latitude,
					'ln'=>$k->longitude,
					'stationsName'=>$k->stations->name,
					'poesName'=>$k->poe->name,
					'routeName'=>$k->route->name,
					'graphsName'=>$k->graph->name,
					'bortsName'=>$k->bort->state_number,
					'fln'=>$k->flights_number,
					'arrival_plan'=>$timeTable->getFormattedTime(),
					'comment'=>$k->comment
				);
				$numnpp=$numnpp+1;
			}
		}
		if ($level==2) {
			$numnpp=1;
			$a=ReportCachStops::model()->with('poe','route','graph','bort','stations')->findAll(array(
				'condition'=> 't.routes_id = :rhid AND date >= :f AND date <= :t',
				'params'   =>array(':rhid' => $nodeId,':f'=>$fromDate, ':t'=>$toDate, ),
				'order'    => 't.id'));
			foreach ($a as $k) {
				$timeTable= new Time($k->arrival_plan);
				$rows[]=array(
					'idRecord'=>$k->id,
					'npp'=>$numnpp,
					'date'=>$k->date,
					'lt'=>$k->latitude,
					'ln'=>$k->longitude,
					'stationsName'=>$k->stations->name,
					'poesName'=>$k->poe->name,
					'routeName'=>$k->route->name,
					'graphsName'=>$k->graph->name,
					'bortsName'=>$k->bort->state_number,
					'fln'=>$k->flights_number,
					'arrival_plan'=>$timeTable->getFormattedTime(),
					'comment'=>$k->comment
				);
				$numnpp=$numnpp+1;
			}
		}
		if ($level==3) {
			$numnpp=1;
			$a=ReportCachStops::model()->with('poe','route','graph','bort','stations')->findAll(array(
				'condition'=> 't.graphs_id = :rhid AND date >= :f AND date <= :t',
				'params'   =>array(':rhid' => $nodeId,':f'=>$fromDate, ':t'=>$toDate, ),
				'order'    => 't.id'));
			foreach ($a as $k) {
				$timeTable= new Time($k->arrival_plan);
				$rows[]=array(
					'idRecord'=>$k->id,
					'npp'=>$numnpp,
					'date'=>$k->date,
					'lt'=>$k->latitude,
					'ln'=>$k->longitude,
					'stationsName'=>$k->stations->name,
					'poesName'=>$k->poe->name,
					'routeName'=>$k->route->name,
					'graphsName'=>$k->graph->name,
					'bortsName'=>$k->bort->state_number,
					'fln'=>$k->flights_number,
					'arrival_plan'=>$timeTable->getFormattedTime(),
					'comment'=>$k->comment
				);
				$numnpp=$numnpp+1;
			}
		}
		$countRows=count($rows);
		$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows); 
		echo CJSON::encode($result);
	}
	public function actionCreate()//на посилання з гет
	{
		//$idRecord=Yii::app()->request->getParam('idRecord');
		$level    = Yii::app()->request->getParam('level');
		$nodeId   = Yii::app()->request->getParam('recordIdLevel');
		$fromDate = Yii::app()->request->getParam('fromDate');
		if ($level==1) {

		}
		if ($level==2) {
			$newFrom = strtotime($fromDate)+(2*3600);
			$newTo   = strtotime($fromDate)+(26*3600);
			$locFli = LocationsFlight::model()->findAll(array(
					'condition'=> 'unixtime >= :f AND unixtime <= :t AND routes_id = :rid',
					'params'   =>array(':f'=>$newFrom, ':t'=>$newTo, ':rid'=>$nodeId)));
			foreach ($locFli as $lf) {
				$arrayGraphsLocations[$lf->graphs_id][$lf->flights_number][$lf->stations_id]=$lf->unixtime;
			}
			//print_r($arrayGraphsLocations);
			$y=ReportCachStops::model()->findAll(array(
				'condition'=> 'date = :date AND routes_id = :ro',
				'params'   =>array(':date' => $fromDate, ':ro' => $nodeId),
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
				//print_r($rows);
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
				unset($insert);

			}
			$result = array('success' => true, 'rows'=> $arRecord); 
			echo CJSON::encode($result);
		}
		if ($level==3) {

			$newFrom = strtotime($fromDate)+(2*3600);
			$newTo   = strtotime($fromDate)+(26*3600);
			$locFli = LocationsFlight::model()->findAll(array(
					'condition'=> 'unixtime >= :f AND unixtime <= :t AND graphs_id = :gid',
					'params'   =>array(':f'=>$newFrom, ':t'=>$newTo, ':gid'=>$nodeId)));
			foreach ($locFli as $lf) {
				$arrayGraphsLocations[$lf->flights_number][$lf->stations_id]=$lf->unixtime;
			}
			//print_r($arrayGraphsLocations);
			$y=ReportCachStops::model()->findAll(array(
				'condition'=> 'date = :date AND graphs_id = :gra',
				'params'   =>array(':date' => $fromDate, ':gra' => $nodeId),
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
				//print_r($rows);
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
						if (isset($arrayGraphsLocations[$rows['fln']][$rows['stations_id']])) {
							$r=ReportCachStops::model()->findByAttributes(array('id'=>$idRecord));
							$r->comment='already exist';
							$r->save();
						}
						if (!isset($arrayGraphsLocations[$rows['fln']][$rows['stations_id']])) {
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
				unset($insert);
			
			}
			$result = array('success' => true, 'rows'=> $arRecord); 
			echo CJSON::encode($result);
			
		}
		/*$a=ReportCachStops::model()->findAll(array(
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
		//print_r($rows);
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
		
		if (isset($arrayLocation)) {//перевірка на найменше відхилення
			for ($i=0; $i <count($arrayLocation) ; $i++) { 
				if (($arrayLocation[$i]['difLong']<$minDifLn) && ($arrayLocation[$i]['difLat']<$minDifLt)) {
					
					
						$minDifLn=$arrayLocation[$i]['difLong'];
						$minDifLt=$arrayLocation[$i]['difLat'];
						$insert=$arrayLocation[$i];
					
				}
			}
		}
		//print_r($insert);
		if (isset($insert)) {
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
			//echo $t->id;
			ReportCachStops::model()->deleteAll(array(
				'condition'=> 'id = :id',
				'params'   =>array(':id' => $idRecord)));
		}
		if (!isset($insert)) {
			$r=ReportCachStops::model()->findByAttributes(array('id'=>$idRecord));
			$r->comment='no Locations';
			$r->save();
		}
		$result = array('success' => true, 'rows'=> $idRecord); 
		echo CJSON::encode($result);*/
	}
}
?>	
