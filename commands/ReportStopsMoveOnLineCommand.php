<?php
Yii::import('application.models.*');
class ReportStopsMoveOnLineCommand extends CConsoleCommand
{
	public function run($dateToRecalc) {
 		$rewiew=$dateToRecalc;
 		$countDate=count($rewiew);
 		if ($countDate==0){
 			$day=date('Y-m-d');
 			$find=DaysToReport::model()->findByAttributes(array(
 				'date'=>$day));
 			$dy=$find->found_days;
 			$dyy=explode(",", $dy);
 			foreach ($dyy as $key => $value) {
 				$rewiew[$key]=$value;
 			}
 			$countDate=count($rewiew);
 		}
 		for ($cd=0; $cd < $countDate; $cd++) {
 			$startTimeReport[$cd]=time();
 			if ($rewiew[$cd]!=null) {//якщо треба перерахувати вручну за якийсь день
 		   		$dayToCalc[$cd]=$rewiew[$cd];//присвоюємо час що ввели вручну
 		   		//видаляемо дані з таблиць
	    		ReportStopsAverageGraphs::model()->deleteAll(array(
	    			'condition' => 'date = :d',
					'params' => array(':d' => $dayToCalc[$cd])));
	    		ReportStopsAverageRoutes::model()->deleteAll(array(
	    			'condition' => 'date = :d',
					'params' => array(':d' => $dayToCalc[$cd])));
	    		ReportStopsAverageBuses::model()->deleteAll(array(
	    			'condition' => 'date = :d',
					'params' => array(':d' => $dayToCalc[$cd])));

 	    	}//якщо перерахунок
 	    	if ($rewiew[$cd]==null){//робимо вночі кожного дня
 	    		$dayToCalc[$cd]=date("Y-m-d",strtotime ("yesterday"));//присвоюємо час що відповідає вчорашньому дню
 	    	}//if calc yesterday

	    	$tdfrom[$cd]=strtotime($dayToCalc[$cd])+3600;
			$tdto[$cd]=strtotime($dayToCalc[$cd])+23*3600+59*60+59+3600;
			$sql = Yii::app()->db->createCommand()
					    ->select()
					    ->from('locations_in_flights')
					    ->where('unixtime>='.$tdfrom[$cd].' and unixtime<='.$tdto[$cd])
					    ->order('unixtime')
					    ->queryAll();
			$countSql=count($sql);		    
			if ($countSql==0) {
				$success[$cd]='N';
				$message[$cd]="no found records in table for date ".$dayToCalc[$cd];
				$endTimeReport[$cd]=time();
				$newRecordReport = new ExecutionsCommands;
				$newRecordReport->date=date("Y-m-d");
				$newRecordReport->commands_id=8;
				$newRecordReport->start_time=$startTimeReport[$cd];
				$newRecordReport->end_time=$endTimeReport[$cd];
				$newRecordReport->duration=$endTimeReport[$cd]-$startTimeReport[$cd];
				$newRecordReport->success=$success[$cd];
				$newRecordReport->comment=$message[$cd];
				$newRecordReport->save();
			}
			else if ($countSql!=0) {
				$success[$cd]='Y';		    
				foreach ($sql as $key => $value) {
					foreach ($value as $key2 => $value2) {
						if ($key2=='time_difference') {
							$arrayTimeGraphs[$sql[$key]['stations_id']][$sql[$key]['routes_id']][$sql[$key]['graphs_id']][]=$value2;
	 					}
	 				}
	 			}
				foreach ($arrayTimeGraphs as $stat => $value) {
					foreach ($value as $route => $value1) {
						foreach ($value1 as $gr => $value2) {
							$countRecord=count($value2);
							$sumRecord=array_sum($value2);
							$middle=round($sumRecord/$countRecord,1);
							$arrayToInsertGraphs[]=array(
								'route'=>$route,
								'graph'=>$gr,
								'stat'=>$stat,
								'middle'=>$middle
							);
						}
					}
				}
				$countInsertGraphs=count($arrayToInsertGraphs);
				for ($i=0; $i < $countInsertGraphs; $i++) { 
					$e = new ReportStopsAverageGraphs;
					$e->date=$dayToCalc[$cd];
					$e->routes_id=$arrayToInsertGraphs[$i]['route'];
					$e->graphs_id=$arrayToInsertGraphs[$i]['graph'];
					$e->stations_id=$arrayToInsertGraphs[$i]['stat'];
					$e->average_deviation=$arrayToInsertGraphs[$i]['middle'];
					$e->save();
				}	
				for ($i=0; $i < $countInsertGraphs; $i++) {
					$arrayTimeRoutes[$arrayToInsertGraphs[$i]['stat']][$arrayToInsertGraphs[$i]['route']][]=$arrayToInsertGraphs[$i]['middle'];
				}
				foreach ($arrayTimeRoutes as $stat => $value) {
					foreach ($value as $route => $value1) {
						$countRecord=count($value1);
						$sumRecord=array_sum($value1);
						$middle=round($sumRecord/$countRecord,1);
						$arrayToInsertRoutes[]=array(
							'route'=>$route,
							'stat'=>$stat,
							'middle'=>$middle
						);
					}
				}
				$countInsertRoutes=count($arrayToInsertRoutes);
				for ($i=0; $i < $countInsertRoutes; $i++) { 
					$e = new ReportStopsAverageRoutes;
					$e->date=$dayToCalc[$cd];
					$e->routes_id=$arrayToInsertRoutes[$i]['route'];
					$e->stations_id=$arrayToInsertRoutes[$i]['stat'];
					$e->average_deviation=$arrayToInsertRoutes[$i]['middle'];
					$e->save();
				}
				for ($i=0; $i < $countInsertRoutes; $i++) {
					$arrayTimeBuses[$arrayToInsertRoutes[$i]['stat']][]=$arrayToInsertRoutes[$i]['middle'];
				}
				foreach ($arrayTimeBuses as $stat => $value) {
					$countRecord=count($value);
					$sumRecord=array_sum($value);
					$middle=round($sumRecord/$countRecord,1);
					$arrayToInsertBuses[]=array(
						'stat'=>$stat,
						'middle'=>$middle
					);
				}
				$countInsertBuses=count($arrayToInsertBuses);
				for ($i=0; $i < $countInsertBuses; $i++) { 
					$e = new ReportStopsAverageBuses;
					$e->date=$dayToCalc[$cd];
					$e->stations_id=$arrayToInsertBuses[$i]['stat'];
					$e->average_deviation=$arrayToInsertBuses[$i]['middle'];
					$e->save();
				}
				
				unset($arrayTimeGraphs);
				unset($arrayToInsertGraphs);
				unset($arrayTimeRoutes);
				unset($arrayToInsertRoutes);
				unset($arrayTimeBuses);
				unset($arrayToInsertBuses);
				$endTimeReport[$cd]=time();
				$cdPlusOne=$cd+1;
				$message[$cd]="calc report on day ".$dayToCalc[$cd]." ".$cdPlusOne." from ".$countDate;
				$newRecordReport = new ExecutionsCommands;
				$newRecordReport->date=date("Y-m-d");
				$newRecordReport->commands_id=8;
				$newRecordReport->start_time=$startTimeReport[$cd];
				$newRecordReport->end_time=$endTimeReport[$cd];
				$newRecordReport->duration=$endTimeReport[$cd]-$startTimeReport[$cd];
				$newRecordReport->success=$success[$cd];
				$newRecordReport->comment=$message[$cd];
				$newRecordReport->save();
			}	
		}
    }
}
?>