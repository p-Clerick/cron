<?php
class ReportAverageGraphsController extends CController  {
	public function actionRead()//на посилання з гет
	{
		$chart=Yii::app()->request->getParam('chart');
		$level=Yii::app()->request->getParam('level');
		$nodeId=Yii::app()->request->getParam('recordIdLevel');
		$fromDate=Yii::app()->request->getParam('fromDate');
		$toDate=Yii::app()->request->getParam('toDate');
		if ($level==1) {
			if(Yii::app()->user->name != "guest"){
	            $carrier = Yii::app()->user->checkUser(Yii::app()->user);
	        }
	        if ($carrier) {
	        	$a = ReportAverageGraphs::model()->with('route')->findAll(array(
					'condition'=> 'date >= :f AND date <= :t',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate),
					'order'    => 't.id'));
				foreach ($a as $k) {
					if ($k->route->carriers_id==$carrier['carrier_id']) {
						$dateOTP[$k->routes_id][]=$k->ontime_percentage;
						$dateOTA[$k->routes_id][]=$k->ontime_average;
						$dateLP[$k->routes_id][]=$k->lateness_percentage;
						$dateLA[$k->routes_id][]=$k->lateness_average;
						$dateAP[$k->routes_id][]=$k->advance_percentage;
						$dateAA[$k->routes_id][]=$k->advance_average;
						$dateRouteName[$k->routes_id]=$k->route->name;
					}
				}
	        }
	        else {
	        	$a = ReportAverageGraphs::model()->with('route')->findAll(array(
					'condition'=> 'date >= :f AND date <= :t',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate),
					'order'    => 't.id'));
				foreach ($a as $k) {
					$dateOTP[$k->routes_id][]=$k->ontime_percentage;
					$dateOTA[$k->routes_id][]=$k->ontime_average;
					$dateLP[$k->routes_id][]=$k->lateness_percentage;
					$dateLA[$k->routes_id][]=$k->lateness_average;
					$dateAP[$k->routes_id][]=$k->advance_percentage;
					$dateAA[$k->routes_id][]=$k->advance_average;
					$dateRouteName[$k->routes_id]=$k->route->name;
				}
	        }
			
			//print_r($dateOTP);
			foreach ($dateOTP as $routeId => $value) {
				$countRecord=count($value);
				$sumRecord=array_sum($value);
				$percent=round($sumRecord/$countRecord,2);
				$rowsData[$routeId]['ontP']=$percent;
			}
			foreach ($dateOTA as $routeId => $value) {
				$countRecord=count($value);
				$sumRecord=array_sum($value);
				foreach ($value as $key => $value1) {
					if ($value1==null) {
						$nnn[$routeId]=$nnn[$routeId]+1;
					}
				}
				if ($nnn[$routeId]==$countRecord) {
					$percent=round($sumRecord/($countRecord),2);
				}
				if ($nnn[$routeId]!=$countRecord) {
					$percent=round($sumRecord/($countRecord-$nnn[$routeId]),2);
				}
				$rowsData[$routeId]['ontA']=$percent;
			}
			foreach ($dateLA as $routeId => $value) {
				$countRecord=count($value);
				$sumRecord=array_sum($value);
				foreach ($value as $key => $value1) {
					if ($value1==null) {
						$nn[$routeId]=$nn[$routeId]+1;
					}
				}
				if ($nn[$routeId]==$countRecord) {
					$percent=round($sumRecord/($countRecord),2);
				}
				if ($nn[$routeId]!=$countRecord) {
					$percent=round($sumRecord/($countRecord-$nn[$routeId]),2);
				}
				$rowsData[$routeId]['lateA']=$percent;
			}
			foreach ($dateLP as $routeId => $value) {
				$countRecord=count($value);
				$sumRecord=array_sum($value);
				$percent=round($sumRecord/$countRecord,2);
				$rowsData[$routeId]['lateP']=$percent;
			}
			foreach ($dateAA as $routeId => $value) {
				$countRecord=count($value);
				$sumRecord=array_sum($value);
				foreach ($value as $key => $value1) {
					if ($value1==null) {
						$n[$routeId]=$n[$routeId]+1;
					}
				}
				if ($n[$routeId]==$countRecord) {
					$percent=round($sumRecord/($countRecord),2);
				}
				if ($n[$routeId]!=$countRecord) {
					$percent=round($sumRecord/($countRecord-$n[$routeId]),2);
				}
				$rowsData[$routeId]['advA']=$percent;
			}
			foreach ($dateAP as $routeId => $value) {
				$rowsData[$routeId]['advP']=100-$rowsData[$routeId]['lateP']-$rowsData[$routeId]['ontP'];
			}
			//print_r($rowsData);
			$e=0;
			foreach ($rowsData as $routeId => $value) {
				$rows[$e]=array(
					'routeId'=>$routeId,
					'routeName'=>$dateRouteName[$routeId],
					'graphName'=>Yii::app()->session['AllGrafiks'],
					'date'=>$fromDate." - ".$toDate
				);
				foreach ($value as $key => $value1) {
					$rows[$e][$key]=$value1;
				}
				$e=$e+1;
			}
			$countRows=count($rows);
		
			function mySort ($a,$b) {
				if ( $a['routeName'] == $b['routeName'] ) {
					return 0;
				}
				if ( $a['routeName'] > $b['routeName'] ) {
					return 1;
				}
				if ( $a['routeName'] < $b['routeName'] ) {
					return -1;
				}
			}
			if (isset($rows)) {
				usort($rows, "mySort");
			}
			if ($chart==0) {
				for ($i=0; $i <$countRows ; $i++) {
					$rows[$i]['npp']=$i+1; 
					$p[$i][]=$rows[$i]['ontP'];
					$p[$i][]=$rows[$i]['lateP'];
					$p[$i][]=$rows[$i]['advP'];
					$max=0;
					for ($ii=0; $ii <3 ; $ii++) { 
						if ($p[$i][$ii]>=$max) {
							$max=$p[$i][$ii];
							$maxCol=$ii;
						}
					}
					$rows[$i]['maxRecord']=$max;
					$rows[$i]['maxRecordId']=$i;
					$rows[$i]['maxCol']=$maxCol;
					$allDataOp=$allDataOp+$rows[$i]['ontP'];
					$allDataLp=$allDataLp+$rows[$i]['lateP'];
					$allDataAp=$allDataAp+$rows[$i]['advP'];
					$allDataOa=$allDataOa+$rows[$i]['ontA'];
					$allDataLa=$allDataLa+$rows[$i]['lateA'];
					$allDataAa=$allDataAa+$rows[$i]['advA'];
				}
				$rows[$countRows]=array(
					'npp'=>$countRows+1,
					'date'=>Yii::app()->session['InGeneral'],
					'ontP'=>round($allDataOp/$countRows,2),
					'lateP'=>round($allDataLp/$countRows,2),
					'advP'=>round($allDataAp/$countRows,2),
					'ontA'=>round($allDataOa/$countRows,2),
					'lateA'=>round($allDataLa/$countRows,2),
					'advA'=>round($allDataAa/$countRows,2)
				);
				$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows+1); 
				echo CJSON::encode($result);
			}
			if ($chart==1) {
				for ($i=0; $i < $countRows; $i++) { 
				$chartRows[]=array(
					'yLabel'=>Yii::app()->session['RouteTextFull']." ".$rows[$i]['routeName'],
					'plus'=>$rows[$i]['advP'],
					'minus'=>$rows[$i]['lateP'],
					'ontime'=>$rows[$i]['ontP']
				);
			}
			$result = array('success' => true, 'rows'=>$chartRows); 
			echo CJSON::encode($result);
			}
		}
		if ($level==2) {
			$a = ReportAverageGraphs::model()->with('route','graph')->findAll(array(
				'condition'=> 't.routes_id = :rhid AND date >= :f AND date <= :t',
				'params'   => array(':rhid' => $nodeId, ':f'=>$fromDate, ':t'=>$toDate),
				'order'    => 't.id'));
			foreach ($a as $k) {
				$dateOTP[$k->graphs_id][]=$k->ontime_percentage;
				$dateOTA[$k->graphs_id][]=$k->ontime_average;
				$dateLP[$k->graphs_id][]=$k->lateness_percentage;
				$dateLA[$k->graphs_id][]=$k->lateness_average;
				$dateAP[$k->graphs_id][]=$k->advance_percentage;
				$dateAA[$k->graphs_id][]=$k->advance_average;
				$dateRouteName[$k->graphs_id]=$k->route->name;
				$dateGraphName[$k->graphs_id]=$k->graph->name;
			}
			//print_r($dateOTP);
			foreach ($dateOTP as $graphId => $value) {
				$countRecord=count($value);
				$sumRecord=array_sum($value);
				$percent=round($sumRecord/$countRecord,2);
				$rowsData[$graphId]['ontP']=$percent;
			}
			foreach ($dateOTA as $graphId => $value) {
				$countRecord=count($value);
				$sumRecord=array_sum($value);
				foreach ($value as $key => $value1) {
					if ($value1==null) {
						$nnn[$graphId]=$nnn[$graphId]+1;
					}
				}
				if ($nnn[$graphId]==$countRecord) {
					$percent=round($sumRecord/($countRecord),2);
				}
				if ($nnn[$graphId]!=$countRecord) {
					$percent=round($sumRecord/($countRecord-$nnn[$graphId]),2);
				}
				$rowsData[$graphId]['ontA']=$percent;
			}
			
			foreach ($dateLA as $graphId => $value) {
				$countRecord=count($value);
				$sumRecord=array_sum($value);
				foreach ($value as $key => $value1) {
					if ($value1==null) {
						$n[$graphId]=$n[$graphId]+1;
					}
				}
				if ($n[$graphId]==$countRecord) {
					$percent=round($sumRecord/($countRecord),2);
				}
				if ($n[$graphId]!=$countRecord) {
					$percent=round($sumRecord/($countRecord-$n[$graphId]),2);
				}
				$rowsData[$graphId]['lateA']=$percent;
			}
			foreach ($dateLP as $graphId => $value) {
				$countRecord=count($value);
				$sumRecord=array_sum($value);
				$percent=round($sumRecord/$countRecord,2);
				$rowsData[$graphId]['lateP']=$percent;
			}
			foreach ($dateAA as $graphId => $value) {
				$countRecord=count($value);
				$sumRecord=array_sum($value);
				foreach ($value as $key => $value1) {
					if ($value1==null) {
						$nn[$graphId]=$nn[$graphId]+1;
					}
				}
				if ($nn[$graphId]==$countRecord) {
					$percent=round($sumRecord/($countRecord),2);
				}
				if ($nn[$graphId]!=$countRecord) {
					$percent=round($sumRecord/($countRecord-$nn[$graphId]),2);
				}
				$rowsData[$graphId]['advA']=$percent;
			}
			foreach ($dateAP as $graphId => $value) {
				$rowsData[$graphId]['advP']=100-$rowsData[$graphId]['lateP']-$rowsData[$graphId]['ontP'];
			}
			$e=0;
			foreach ($rowsData as $graphId => $value) {
				$rows[$e]=array(
					'routeId'=>$nodeId,
					'graphId'=>$graphId,
					'routeName'=>$dateRouteName[$graphId],
					'graphName'=>$dateGraphName[$graphId],
					'date'=>$fromDate." - ".$toDate
				);
				foreach ($value as $key => $value1) {
					$rows[$e][$key]=$value1;
				}
				$e=$e+1;
			}
			$countRows=count($rows);
			function mySort ($a,$b) {
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
			if (isset($rows)) {
				usort($rows, "mySort");
			}
			if ($chart==0) {
				for ($i=0; $i <$countRows ; $i++) {
					$rows[$i]['npp']=$i+1; 
					$p[$i][]=$rows[$i]['ontP'];
					$p[$i][]=$rows[$i]['lateP'];
					$p[$i][]=$rows[$i]['advP'];
					$max=0;
					for ($ii=0; $ii <3 ; $ii++) { 
						if ($p[$i][$ii]>=$max) {
							$max=$p[$i][$ii];
							$maxCol=$ii;
						}
					}
					$rows[$i]['maxRecord']=$max;
					$rows[$i]['maxRecordId']=$i;
					$rows[$i]['maxCol']=$maxCol;
					$allDataOp=$allDataOp+$rows[$i]['ontP'];
					$allDataLp=$allDataLp+$rows[$i]['lateP'];
					$allDataAp=$allDataAp+$rows[$i]['advP'];
					$allDataOa=$allDataOa+$rows[$i]['ontA'];
					$allDataLa=$allDataLa+$rows[$i]['lateA'];
					$allDataAa=$allDataAa+$rows[$i]['advA'];
				}
				$rows[$countRows]=array(
					'npp'=>$countRows+1,
					'date'=>Yii::app()->session['InGeneral'],
					'ontP'=>round($allDataOp/$countRows,2),
					'lateP'=>round($allDataLp/$countRows,2),
					'advP'=>round($allDataAp/$countRows,2),
					'ontA'=>round($allDataOa/$countRows,2),
					'lateA'=>round($allDataLa/$countRows,2),
					'advA'=>round($allDataAa/$countRows,2)
				);
				$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows+1); 
				echo CJSON::encode($result);
			}
			if ($chart==1) {
				for ($i=0; $i < $countRows; $i++) { 
				$chartRows[]=array(
					'yLabel'=>Yii::app()->session['RouteTextFull']." ".$rows[$i]['routeName']."  ".Yii::app()->session['GrafikTextFull']." ".$rows[$i]['graphName'],
					'plus'=>$rows[$i]['advP'],
					'minus'=>$rows[$i]['lateP'],
					'ontime'=>$rows[$i]['ontP']
				);
			}
			$result = array('success' => true, 'rows'=>$chartRows); 
			echo CJSON::encode($result);
			}

		}
		if ($level==3) {
			$a = ReportAverageGraphs::model()->with('graph','route')->findAll(array(
				'condition'=> 't.graphs_id = :rhid AND date >= :f AND date <= :t',
				'params'   => array(':rhid' => $nodeId, ':f'=>$fromDate, ':t'=>$toDate),
				'order'    => 't.id'));
			foreach ($a as $k) {
				$rows[]=array(
					'date'=>$k->date,
					'routeName'=>$k->route->name,
					'graphName'=>$k->graph->name,
					'graphId'=>$k->graphs_id,
					'ontP'=>$k->ontime_percentage,
					'ontA'=>$k->ontime_average,
					'lateP'=>$k->lateness_percentage,
					'lateA'=>$k->lateness_average,
					'advP'=>$k->advance_percentage,
					'advA'=>$k->advance_average,
				);
			}
			$countRows=count($rows);
			function mySort ($a,$b) {
				if ( $a['date'] == $b['date'] ) {
					return 0;
				}
				if ( $a['date'] > $b['date'] ) {
					return 1;
				}
				if ( $a['date'] < $b['date'] ) {
					return -1;
				}
			}
			if (isset($rows)) {
				usort($rows, "mySort");
			}
			
			if ($chart==0) {
				
					for ($i=0; $i <$countRows ; $i++) {
						$rows[$i]['npp']=$i+1; 
						$p[$i][]=$rows[$i]['ontP'];
						$p[$i][]=$rows[$i]['lateP'];
						$p[$i][]=$rows[$i]['advP'];
						$max=0;
						for ($ii=0; $ii <3 ; $ii++) { 
							if ($p[$i][$ii]>=$max) {
								$max=$p[$i][$ii];
								$maxCol=$ii;
							}
						}
						$rows[$i]['maxRecord']=$max;
						$rows[$i]['maxRecordId']=$i;
						$rows[$i]['maxCol']=$maxCol;
						$allDataOp=$allDataOp+$rows[$i]['ontP'];
						$allDataLp=$allDataLp+$rows[$i]['lateP'];
						$allDataAp=$allDataAp+$rows[$i]['advP'];
						$allDataOa=$allDataOa+$rows[$i]['ontA'];
						$allDataLa=$allDataLa+$rows[$i]['lateA'];
						$allDataAa=$allDataAa+$rows[$i]['advA'];
					}
					$rows[$countRows]=array(
						'npp'=>$countRows+1,
						'date'=>Yii::app()->session['InGeneral'],
						'ontP'=>round($allDataOp/$countRows,2),
						'lateP'=>round($allDataLp/$countRows,2),
						'advP'=>round($allDataAp/$countRows,2),
						'ontA'=>round($allDataOa/$countRows,2),
						'lateA'=>round($allDataLa/$countRows,2),
						'advA'=>round($allDataAa/$countRows,2)
					);
					
				$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows+1); 
				echo CJSON::encode($result);
			}
			if ($chart==1) {
				for ($i=0; $i < $countRows; $i++) { 
				$chartRows[]=array(
					'yLabel'=>$rows[$i]['date']."  ".Yii::app()->session['RouteTextFull']." ".$rows[$i]['routeName']."  ".Yii::app()->session['GrafikTextFull']." ".$rows[$i]['graphName'],
					'plus'=>$rows[$i]['advP'],
					'minus'=>$rows[$i]['lateP'],
					'ontime'=>$rows[$i]['ontP']
				);
			}
			$result = array('success' => true, 'rows'=>$chartRows); 
			echo CJSON::encode($result);
			}
		}
	}
}
?>	