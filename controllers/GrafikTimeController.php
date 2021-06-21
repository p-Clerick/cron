<?php
class GrafikTimeController extends Controller
{
	public function actionCreate ($id) {
		$grafikid=Yii::app()->request->getParam('grafikid');

		$r = Graphs::model()->findByAttributes(array(
			'id'=>$grafikid));
		$routeid=$r->routes_id;

		$rr = StationsScenario::model()->findAll(array(
			'condition'=> 'routes_id = :rhid',
			'params'   => array(':rhid' => $routeid),
			'order'    => 'number'));
		foreach ($rr as $k) {
			$arrStations[]=array($k->number, $k->stations_id);
		}
		//шукаемо ім*я точки за її ід
			$cf=count($arrStations);
			for($i=0; $i<$cf; $i++)
			{
				$d = Stations::model()->findByAttributes(array('id'=>$arrStations[$i][1]));
				$title=$d->name;
				$f[$i]['title']=$title;
				$f[$i]['stationId']=$arrStations[$i][1];
				$f[$i]['number']=$arrStations[$i][0];
			}
			$result = array('success' => true, 'rows'=>$f );
			echo CJSON::encode($result);
	}

	public function actionRead () {
		$grafikid=Yii::app()->request->getParam('grafikid');
		$graphType=Yii::app()->request->getParam('graphType');
		$st='yes';
		$shiftArray = [];
		$profArray = [];
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
			'name' => 'fl',
			'type' => 'int');

		$r = Graphs::model()->findByAttributes(array(
			'id'=>$grafikid));
		$routeid=$r->routes_id;
		$gN=$r->name;

		$rr = StationsScenario::model()->findAll(array(
			'condition'=> 'routes_id = :rhid',
			'params'   => array(':rhid' => $routeid),
			'order'    => 'number'));
		foreach ($rr as $k) {
			$arrStations[]=array($k->number, $k->stations_id);
			$fields[] = array(
				'name' => $k->stations_id);
		}
		$q = Schedules::model()->findByAttributes(array('graphs_id'=>$grafikid, 'status' => $st, 'schedule_types_id'=>$graphType));
		$idHis=$q->histories_id;
		
		$b=RouteTimeTable::model()->findAll(array(
			'condition'=> 'routes_history_id = :rhid AND graphs_number = :gn',
			'params'   => array(':rhid' => $idHis, ':gn' => $gN),
			'order'    => 'id'));
		foreach ($b as $k) {
			$time= new Time($k->time);
			$fl[]=$k->flights_number;
			$rizn=$fl[0]-1;
			$flightNew=($k->flights_number)-$rizn;
			$arrayTime[]=array(
				$k->Id,
				$flightNew,
				$time->getFormattedTime(),
				$k->stations_id,
				$k->number,
				$k->dinner_start,
				$k->dinner_end,
				$k->workshift_start,
				$k->workshift_end,
				$k->prevention
			);
			if (isset($k->dinner_start)) {
				$ds = new Time($k->dinner_start);
				$de = new Time($k->dinner_end);
				$dinnerArray[]=array(
					'start'=>$ds->getFormattedTime(),
					'end'=>$de->getFormattedTime(),
					'stovpets'=>$k->number, 
					'fl'=>$flightNew
				);
			}
			if (isset($k->workshift_start))
			{
				$ws = new Time($k->workshift_start);
				$shiftArray[]=array(
					'start'=>$ws->getFormattedTime(),
					'stovpets'=>$k->number, 
					'fl'=>$flightNew
				);
			}
			if (isset($k->prevention))
			{
				$profArray[]=array(
					'start'=>$time->getFormattedTime(),
					'stovpets'=>$k->number, 
					'fl'=>$flightNew
				);
			}
		}
		$cAT=count($arrayTime);
		for ($i=0; $i < $cAT; $i++) { 
			$rows[$arrayTime[$i][1]-1]['fl']=$arrayTime[$i][1];
			$rows[$arrayTime[$i][1]-1][$arrayTime[$i][3]]=$arrayTime[$i][2];
			$rows[$arrayTime[$i][1]-1]['id']=$arrayTime[$i][1]-1;
		}
		$countRows=count($rows);
		$countDinners=count($dinnerArray);
		$countShifts=count($shiftArray);
		$countProf=count($profArray);

		for ($i=0; $i<$countRows; $i++)
		{
			for ($a=0; $a<$countDinners; $a++)
			{
				if ($rows[$i]['fl']==$dinnerArray[$a]['fl'])
				{
					$dinnerArray[$a]['recordRowsId']=$rows[$i]['id'];
				}
			}
			for ($a=0; $a<$countShifts; $a++)
			{
				if ($rows[$i]['fl']==$shiftArray[$a]['fl'])
				{
					$shiftArray[$a]['recordRowsId']=$rows[$i]['id'];
				}
			}
			for ($a=0; $a<$countProf; $a++)
			{
				if ($rows[$i]['fl']==$profArray[$a]['fl'])
				{
					$profArray[$a]['recordRowsId']=$rows[$i]['id'];
				}
			}
		}
		

		$result = array('success' => true, 'rows' => array(), );
		$metaData['fields'] = $fields;
		$result['metaData'] = $metaData;		
		$result['rows'] = $rows;
		$result['dinners'] = $dinnerArray;
		$result['shifts'] = $shiftArray;
		$result['prof'] = $profArray;

		echo CJSON::encode($result);
	}
}
?>