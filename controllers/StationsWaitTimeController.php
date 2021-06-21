<?php
class StationsWaitTimeController extends CController  {
	public function actionRead()//на посилання з гет
	{
		$timeLastTransfer=2*60*60;//2 hours
		$stationsIdFind=Yii::app()->request->getParam('znak_id');
		$todayDataTime=date("Y-m-d H:i:s",time()-$timeLastTransfer);
		//$todayDataTime=date("Y-m-d");
		$todayTime=date("H:i:s");
		$time=explode(":", $todayTime);
		$timeFound1=$time[2]+($time[1]*60)+($time[0]*60*60);

		$r=MoveOnSchedule::model()->with('graph','schedule','route','bort')->findAll();
		foreach ($r as $k) {
			if ($k->datatime>=$todayDataTime) {
				$arrayMoveOnSchedule[$k->schedule->histories_id][$k->graph->name]=$k->time_difference;
				$arrayHistoriesIdFind[$k->schedule->histories_id]=$k->route->name;
				$arrayBortSpecNeeds[$k->schedule->histories_id][$k->graph->name]=$k->bort->special_needs;
			}	
		}
		//print_r($arrayBortSpecNeeds);
		$n=RouteTimeTable::model()->findAll(array(
					'condition'=> 'stations_id = :st 
									AND time <= :t
									AND time >= :f',
					'select'   => 't.routes_history_id, t.graphs_number, t.time',		
					'params'   =>array( ':st'=>$stationsIdFind,
										':f'=>$timeFound1-600, 
										':t'=>$timeFound1+3600)
					));
		foreach ($n as $k) {
			$nn[$k->routes_history_id][$k->graphs_number][]=$k->time;
		}
		foreach ($nn as $idHis => $value) {
			foreach ($value as $graphsName => $timeAll) {
				foreach ($timeAll as $key => $time) {
					if (isset($arrayMoveOnSchedule[$idHis][$graphsName])) {
						$timePlan=new Time($time);
						$waitOnPlan=round(($time-$timeFound1)/60,0);
						$wait=$waitOnPlan-$arrayMoveOnSchedule[$idHis][$graphsName];
						if ($wait>=0) {
							if ($arrayBortSpecNeeds[$idHis][$graphsName]=='yes') {
								$y[$idHis][$graphsName]=1;
							}
							if ($arrayBortSpecNeeds[$idHis][$graphsName]=='no') {
								$y[$idHis][$graphsName]=2;
							}
							$rows[]=array(
								'r'=>$arrayHistoriesIdFind[$idHis],
								't1'=>$wait,
								't2'=>$timePlan->getFormattedTime(),
								't3'=>$waitOnPlan,
								't4'=>$arrayMoveOnSchedule[$idHis][$graphsName],
								'color'=>$y[$idHis][$graphsName]
							);

						}	
					}
				}
			}
		}
		function sortByTimeWait ($a,$b) {
			if ( $a['t1'] == $b['t1'] ) {
				return 0;
			}
			if ( $a['t1'] > $b['t1'] ) {
				return 1;
			}
			if ( $a['t1'] < $b['t1'] ) {
				return -1;
			}
		}
		if (isset($rows)) {
			usort($rows, "sortByTimeWait");
		}
		
		$countRows=count($rows);
		$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows); 
		echo CJSON::encode($result);
	}
}