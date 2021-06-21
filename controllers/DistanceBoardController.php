<?php
class DistanceBoardController extends Controller  {
	public function actionRead()//на посилання з гет
	{
		$level=Yii::app()->request->getParam('level');
		$nodeId=Yii::app()->request->getParam('recordIdLevel');

		if ($level==1) {
			//шукаемо всі звязки між зупинками
			$a=DayIntervalStations::model()->findAll();
			foreach ($a as $k) {
				$arrayStationsRelations[$k->stations_id_from][$k->stations_id_to]=1;
			}

			//print_r($arrayStationsRelations);
			
			$b=DistanceStations::model()->findAll();
			foreach ($b as $k) {
				$arraydistanceOld[$k->stations_id_from][$k->stations_id_to]=$k->distance_in_meters;
			}
			//print_r($arraydistanceOld);
			$c=Stations::model()->findAll();
			foreach ($c as $k) {
				$arrayStationsName[$k->id]=$k->name;
			}
			foreach ($arrayStationsRelations as $from => $arto) {
				foreach ($arto as $to => $value) {
					$rows[]=array(
						'npp'=>count($rows)+1,
						'stops_from_id'=>$from,
						'stops_to_id'=>$to,
						'stops_from_name'=>$arrayStationsName[$from],
						'stops_to_name'=>$arrayStationsName[$to],
						'distance'=>$arraydistanceOld[$from][$to]
					);
					$sumdistance=$sumdistance+$arraydistanceOld[$from][$to];
				}
			}

			$result = array('success' => true, 'rows' => $rows, 'sumdistance'=>$sumdistance/1000 );
			echo CJSON::encode($result);
		}
		if ($level==2) {
			//шукаемо всі звязки між зупинками
			$a=DayIntervalStations::model()->findAll();
			foreach ($a as $k) {
				$arrayStationsRelations[$k->stations_id_from][$k->stations_id_to]=1;
			}
			//print_r($arrayStationsRelations);
			
			$b=DistanceStations::model()->findAll();
			foreach ($b as $k) {
				$arraydistanceOld[$k->stations_id_from][$k->stations_id_to]=$k->distance_in_meters;
			}
			//print_r($b);
			$c=Stations::model()->findAll();
			foreach ($c as $k) {
				$arrayStationsName[$k->id]=$k->name;
			}
			foreach ($arrayStationsRelations as $from => $arto) {
				$rows = array();
				foreach ($arto as $to => $value) {
					$rows[$from][$to]=array(
						'npp'=>count($rows)+1,
						'stops_from_id'=>$from,
						'stops_to_id'=>$to,
						'stops_from_name'=>$arrayStationsName[$from],
						'stops_to_name'=>$arrayStationsName[$to],
						'distance'=>$arraydistanceOld[$from][$to]
					);
				}
			}
			//шукаемо наш маршрут сценарій зупинок
			$routeScenario=StationsScenario::model()->findAll(array(
				'condition'=> 't.routes_id = :rhid',
				'params'   => array(':rhid' => $nodeId),
				'order'    => 't.id'));
			foreach ($routeScenario as $k) {
				$arrayScenario[]=$k->stations_id;
			}
			for ($i=0; $i <count($arrayScenario)-1 ; $i++) { 
				$rowsRoute[]=array(
						'npp'=>$i+1,
						'stops_from_id'=>$arrayScenario[$i],
						'stops_to_id'=>$arrayScenario[$i+1],
						'stops_from_name'=>$rows[$arrayScenario[$i]][$arrayScenario[$i+1]]['stops_from_name'],
						'stops_to_name'=>$rows[$arrayScenario[$i]][$arrayScenario[$i+1]]['stops_to_name'],
						'distance'=>$rows[$arrayScenario[$i]][$arrayScenario[$i+1]]['distance']
					);
				$sumdistance=$sumdistance+$rows[$arrayScenario[$i]][$arrayScenario[$i+1]]['distance'];
			}
			
			$result = array('success' => true, 'rows' => $rowsRoute, 'sumdistance'=>$sumdistance/1000 );
			echo CJSON::encode($result);
		}
			
	}
	public function actionCreate()//на посилання з гет
	{
		$from=Yii::app()->request->getParam('from');
		$to=Yii::app()->request->getParam('to');
		$distance=Yii::app()->request->getParam('distance');

		$a=DistanceStations::model()->findByAttributes(array('stations_id_from'=>$from,'stations_id_to'=>$to));
		if (isset($a)) {
			$a->distance_in_meters=$distance;
			$a->save();
		}
		if (!isset($a)) {
			$b= new DistanceStations;
			$b->stations_id_from=$from;
			$b->stations_id_to=$to;
			$b->distance_in_meters=$distance;
			$b->save();
		}
		$result = array('success' => true);
		echo CJSON::encode($result);
	}
}