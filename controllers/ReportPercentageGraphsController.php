<?php
class ReportPercentageGraphsController extends CController  {
	public function actionRead() 
	{
		$chart=Yii::app()->request->getParam('chart');
		$level=Yii::app()->request->getParam('level');
		$nodeId=Yii::app()->request->getParam('recordIdLevel');
		$fromDate=Yii::app()->request->getParam('fromDate');
		$toDate=Yii::app()->request->getParam('toDate');
	
		if ($level==1)
		{
			if(Yii::app()->user->name != "guest"){
	            $carrier = Yii::app()->user->checkUser(Yii::app()->user);
	        }
	        if ($carrier) {
	        	$a = ReportPercentageRoutesGraphs::model()->with('route')->findAll(array(
					'condition'=> 'date >= :f AND date <= :t',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate),
					'order'    => 't.id'));
				foreach ($a as $k) {
					if ($k->route->carriers_id==$carrier['carrier_id']) {
						$dataPCplan[$k->routes_id][$k->date]=$k->count_stations_plan;
						$dataPCfakt[$k->routes_id][$k->date]=$k->count_stations_fakt;
						$dataPercPlan[$k->routes_id][$k->date]=$k->percentage_stations;
						$dataPercFakt[$k->routes_id][$k->date]=$k->percentage_realization;
						$dataRouteName[$k->routes_id]=$k->route->name;
						$dataFlplan[$k->routes_id][$k->date]=$k->count_flight_plan;
						$dataFlfakt[$k->routes_id][$k->date]=$k->count_flight_fakt;
						$dataFlPerc[$k->routes_id][$k->date]=$k->percentage_flight;
						$dataCountRouteDirections[$k->routes_id][$k->date]=$k->count_route_directions;
					}
				}
	        } else {
	        	$a = ReportPercentageRoutesGraphs::model()->with('route')->findAll(array(
					'condition'=> 'date >= :f AND date <= :t',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate),
					'order'    => 't.id'));
				foreach ($a as $k) {
					$dataPCplan[$k->routes_id][$k->carriers_id][$k->date]=$k->count_stations_plan;
					$dataPCfakt[$k->routes_id][$k->carriers_id][$k->date]=$k->count_stations_fakt;
					$dataPercPlan[$k->routes_id][$k->carriers_id][$k->date]=$k->percentage_stations;
					$dataPercFakt[$k->routes_id][$k->carriers_id][$k->date]=$k->percentage_realization;
					$dataRouteName[$k->routes_id][$k->carriers_id]=$k->route->name;
                    $dataCarriersName[$k->routes_id][$k->carriers_id]=$k->carrier->name;
					$dataFlplan[$k->routes_id][$k->carriers_id][$k->date]=$k->count_flight_plan;
					$dataFlfakt[$k->routes_id][$k->carriers_id][$k->date]=$k->count_flight_fakt;
					$dataFlPerc[$k->routes_id][$k->carriers_id][$k->date]=$k->percentage_flight;
					$dataCountRouteDirections[$k->routes_id][$k->carriers_id][$k->date]=$k->count_route_directions;
				}
			}	
			//print_r($data);
			foreach ($dataCountRouteDirections as $routeId => $value) {
                foreach($value as $carrierid => $value1){
				    $countSumDirections[$routeId][$carrierid]=array_sum($value1);
                }
			}
			foreach ($dataPCplan as $routeId => $value) {
                foreach($value as $carrierid => $value1) {
                    $countRecord[$routeId][$carrierid] = count($value1);
                    $countSumPCplan[$routeId][$carrierid] = array_sum($value1);
                }
			}
			foreach ($dataPCfakt as $routeId => $value) {
                foreach($value as $carrierid => $value1) {
                    $countSumPCfakt[$routeId][$carrierid] = array_sum($value1);
                }
			}
			foreach ($dataPercPlan as $routeId => $value) {
                foreach($value as $carrierid => $value1) {
                    $SumPercPlan[$routeId][$carrierid] = array_sum($value1);
                }
			}
			foreach ($dataPercFakt as $routeId => $value) {
                foreach($value as $carrierid => $value1) {
                    $SumPercFakt[$routeId][$carrierid] = array_sum($value1);
                }
			}
			foreach ($dataFlplan as $routeId => $value) {
                foreach($value as $carrierid => $value1) {
                    $countSumFlplan[$routeId][$carrierid] = array_sum($value1);
                }
			}
			foreach ($dataFlfakt as $routeId => $value) {
                foreach($value as $carrierid => $value1) {
                    $countSumFlfakt[$routeId][$carrierid] = array_sum($value1);
                }
			}
			foreach ($SumPercFakt as $routeId => $value) {
                foreach($value as $carrierid => $value1) {
                    $rows[] = array(
                        'date' => $fromDate . " - " . $toDate,
                        'routeId' => $routeId,
                        'carriersId' => $carrierid,
                        'carrierName' => $dataCarriersName[$routeId][$carrierid],
                        'routeName' => $dataRouteName[$routeId][$carrierid],
                        'graphName' => Yii::app()->session['AllGrafiks'],
                        'countPlan' => $countSumPCplan[$routeId][$carrierid],
                        'countFakt' => $countSumPCfakt[$routeId][$carrierid],
                        'countPlanFlight' => $countSumFlplan[$routeId][$carrierid],
                        'countFaktFlight' => $countSumFlfakt[$routeId][$carrierid],
                        'percentDoingFlight' => round($countSumFlfakt[$routeId][$carrierid] / $countSumFlplan[$routeId][$carrierid] * 100, 2),
                        'percentAll' => round($countSumPCfakt[$routeId][$carrierid] / $countSumPCplan[$routeId][$carrierid] * 100, 2),
                        'percentInFlights' => round($SumPercFakt[$routeId][$carrierid]/ $countRecord[$routeId][$carrierid], 2),
                        'countRouteDirections' => $countSumDirections[$routeId][$carrierid]
                    );
                }
			}
			$countRows=count($rows);
			//sort
			function mysort ($a,$b) {
				if ($a['percentInFlights']==$b['percentInFlights']) {
					if ($a['routeName']==$b['routeName']) {
						return 0;
					}
					if ($a['routeName']>$b['routeName']) {
						return 1;
					}
					if ($a['routeName']<$b['routeName']) {
						return -1;
					}
				}
				if ($a['percentInFlights']>$b['percentInFlights']) {
					return -1;
				}
				if ($a['percentInFlights']<$b['percentInFlights']) {
					return 1;
				}
			}
			if (isset($rows)) {
				usort($rows, "mysort");
			}
			if ($chart==0) {
				$carrier=Route::model()->with('carrier')->findAll();
				foreach ($carrier as $k) {
					$arrayCarrier[$k->id]=$k->carrier->name;
				}
                $rows[$countRows]['percentInFlights'] = 0;
                $rows[$countRows]['countPlan'] = 0;
                $rows[$countRows]['countFakt'] = 0;
                $rows[$countRows]['countPlanFlight'] = 0;
                $rows[$countRows]['countFaktFlight'] = 0;
                $rows[$countRows]['countRouteDirections'] = 0;
				for ($i=0; $i < $countRows; $i++) {
					//$rows[$i]['carrierName']=$arrayCarrier[$rows[$i]['routeId']];
					$rows[$i]['npp']=$i+1;  
					$rows[$countRows]['date']=Yii::app()->session['InGeneral'];
					$rows[$countRows]['percentInFlights']=$rows[$countRows]['percentInFlights']+$rows[$i]['percentInFlights'];
					//$rows[$countRows]['percentAll']=$rows[$countRows]['percentAll']+$rows[$i]['percentAll'];
					//$rows[$countRows]['percentDoingFlight']=$rows[$countRows]['percentDoingFlight']+$rows[$i]['percentDoingFlight'];
					$rows[$countRows]['countPlan']=$rows[$countRows]['countPlan']+$rows[$i]['countPlan'];
					$rows[$countRows]['countFakt']=$rows[$countRows]['countFakt']+$rows[$i]['countFakt'];
					$rows[$countRows]['countPlanFlight']=$rows[$countRows]['countPlanFlight']+$rows[$i]['countPlanFlight'];
					$rows[$countRows]['countFaktFlight']=$rows[$countRows]['countFaktFlight']+$rows[$i]['countFaktFlight'];
					$rows[$countRows]['countRouteDirections']=$rows[$countRows]['countRouteDirections']+$rows[$i]['countRouteDirections'];
				}
				$rows[$countRows]['npp']=$countRows+1; 
				$rows[$countRows]['percentInFlights']=round($rows[$countRows]['percentInFlights']/$countRows,2);
				$rows[$countRows]['percentAll']=round($rows[$countRows]['countFakt']/$rows[$countRows]['countPlan']*100,2);
				$rows[$countRows]['percentDoingFlight']=round($rows[$countRows]['countFaktFlight']/$rows[$countRows]['countPlanFlight']*100,2);

				$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows); 
				echo CJSON::encode($result);
			}
			if ($chart==1) {
				for ($i=0; $i < $countRows; $i++) { 
					$chartRows[]=array(
						'yLabel'=>$rows[$i]['percentInFlights'],
						'xLabel'=>Yii::app()->session['RouteTextFull']." ".$rows[$i]['routeName'],
						'flights'=>$rows[$i]['percentDoingFlight'],
						'stops'=>$rows[$i]['percentAll']
					);
				}
				$result = array('success' => true, 'rows'=>$chartRows); 
				echo CJSON::encode($result);
			}
		}
		if ($level==2) {
			$a = ReportPercentageGraphs::model()->with('route','graph')->findAll(array(
				'condition'=> 't.routes_id = :rhid AND date >= :f AND date <= :t',
				'params'   => array(':rhid' => $nodeId, ':f'=>$fromDate, ':t'=>$toDate),
				'order'    => 't.id'));
			foreach ($a as $k) {
				$dataPCplan[$k->graphs_id][$k->date]=$k->count_stations_plan;
				$dataPCfakt[$k->graphs_id][$k->date]=$k->count_stations_fakt;
				$dataPercPlan[$k->graphs_id][$k->date]=$k->percentage_stations;
				$dataPercFakt[$k->graphs_id][$k->date]=$k->percentage_realization;
				$dataRouteName[$k->graphs_id]=$k->route->name;
				$dataGraphName[$k->graphs_id]=$k->graph->name;
				$dataFlplan[$k->graphs_id][$k->date]=$k->count_flight_plan;
				$dataFlfakt[$k->graphs_id][$k->date]=$k->count_flight_fakt;
				$dataFlPerc[$k->graphs_id][$k->date]=$k->percentage_flight;
				$dataCountRouteDirections[$k->graphs_id][$k->date]=$k->count_route_directions;
			}
			//print_r($data);
			foreach ($dataPCplan as $graphId => $value) {
				$countRecord[$graphId]=count($value);
				$countSumPCplan[$graphId]=array_sum($value);
			}
			foreach ($dataCountRouteDirections as $graphId => $value) {
				$countSumDirections[$graphId]=array_sum($value);
			}
			foreach ($dataPCfakt as $graphId => $value) {
				$countSumPCfakt[$graphId]=array_sum($value);
			}
			foreach ($dataPercPlan as $graphId => $value) {
				$SumPercPlan[$graphId]=array_sum($value);
			}
			foreach ($dataPercFakt as $graphId => $value) {
				$SumPercFakt[$graphId]=array_sum($value);
			}
			foreach ($dataFlplan as $graphId => $value) {
				$countSumFlplan[$graphId]=array_sum($value);
			}
			foreach ($dataFlfakt as $graphId => $value) {
				$countSumFlfakt[$graphId]=array_sum($value);
			}
			foreach ($SumPercFakt as $graphId => $value) {
				$rows[]=array(
					'date'=>$fromDate." - ".$toDate,
					'routeId'=>$nodeId,
					'graphId'=>$graphId,
					'routeName'=>$dataRouteName[$graphId],
					'graphName'=>$dataGraphName[$graphId],
					'countPlan'=>$countSumPCplan[$graphId],
					'countFakt'=>$countSumPCfakt[$graphId],
					'countPlanFlight'=>$countSumFlplan[$graphId],
					'countFaktFlight'=>$countSumFlfakt[$graphId],
					'percentDoingFlight'=>round($countSumFlfakt[$graphId]/$countSumFlplan[$graphId]*100,2),
					'percentAll'=>round($countSumPCfakt[$graphId]/$countSumPCplan[$graphId]*100,2),
					'percentInFlights'=>round($SumPercFakt[$graphId]/$countRecord[$graphId],2),
					'countRouteDirections'=>$countSumDirections[$graphId]
				);
			}
			$countRows=count($rows);
			//sort
			function mysort ($a,$b) {
				if ($a['percentInFlights']==$b['percentInFlights']) {
					if ($a['graphName']==$b['graphName']) {
						return 0;
					}
					if ($a['graphName']>$b['graphName']) {
						return 1;
					}
					if ($a['graphName']<$b['graphName']) {
						return -1;
					}
				}
				if ($a['percentInFlights']>$b['percentInFlights']) {
					return -1;
				}
				if ($a['percentInFlights']<$b['percentInFlights']) {
					return 1;
				}
			}
			if (isset($rows)) {
				usort($rows, "mysort");
			}
			if ($chart==0) {
				$carrier=Route::model()->with('carrier')->findAll();
				foreach ($carrier as $k) {
					$arrayCarrier[$k->id]=$k->carrier->name;
				}
                $rows[$countRows]['percentInFlights'] = 0;
				for ($i=0; $i < $countRows; $i++) {
					$rows[$i]['carrierName']=$arrayCarrier[$rows[$i]['routeId']]; 
					$rows[$i]['npp']=$i+1; 
					$rows[$countRows]['date']=Yii::app()->session['InGeneral'];
					$rows[$countRows]['percentInFlights']=$rows[$countRows]['percentInFlights']+$rows[$i]['percentInFlights'];
					//$rows[$countRows]['percentAll']=$rows[$countRows]['percentAll']+$rows[$i]['percentAll'];
					//$rows[$countRows]['percentDoingFlight']=$rows[$countRows]['percentDoingFlight']+$rows[$i]['percentDoingFlight'];
					$rows[$countRows]['countPlan']=$rows[$countRows]['countPlan']+$rows[$i]['countPlan'];
					$rows[$countRows]['countFakt']=$rows[$countRows]['countFakt']+$rows[$i]['countFakt'];
					$rows[$countRows]['countPlanFlight']=$rows[$countRows]['countPlanFlight']+$rows[$i]['countPlanFlight'];
					$rows[$countRows]['countFaktFlight']=$rows[$countRows]['countFaktFlight']+$rows[$i]['countFaktFlight'];
					$rows[$countRows]['countRouteDirections']=$rows[$countRows]['countRouteDirections']+$rows[$i]['countRouteDirections'];
				}
				$rows[$countRows]['npp']=$countRows+1;
				$rows[$countRows]['percentInFlights']=round($rows[$countRows]['percentInFlights']/$countRows,2);
				$rows[$countRows]['percentAll']=round($rows[$countRows]['countFakt']/$rows[$countRows]['countPlan']*100,2);
				$rows[$countRows]['percentDoingFlight']=round($rows[$countRows]['countFaktFlight']/$rows[$countRows]['countPlanFlight']*100,2);
				$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows); 
				echo CJSON::encode($result);
			}
			if ($chart==1) {
				for ($i=0; $i < $countRows; $i++) { 
					$chartRows[]=array(
						'yLabel'=>$rows[$i]['percentInFlights'],
						'xLabel'=>Yii::app()->session['RouteTextFull']." ".$rows[$i]['routeName']."  ".Yii::app()->session['GrafikTextFull']." ".$rows[$i]['graphName'],
						'flights'=>$rows[$i]['percentDoingFlight'],
						'stops'=>$rows[$i]['percentAll']
					);
				}
				$result = array('success' => true, 'rows'=>$chartRows); 
				echo CJSON::encode($result);
			}
		}
		if ($level==3) {
			$a = ReportPercentageGraphs::model()->with('graph','route')->findAll(array(
				'condition'=> 't.graphs_id = :rhid AND date >= :f AND date <= :t',
				'params'   => array(':rhid' => $nodeId, ':f'=>$fromDate, ':t'=>$toDate),
				'order'    => 't.id'));
			foreach ($a as $k) {
				$rows[]=array(
					'date'=>$k->date,
					'routeId'=>$k->routes_id,
					'routeName'=>$k->route->name,
					'graphId'=>$nodeId,
					'graphName'=>$k->graph->name,
					'countPlan'=>$k->count_stations_plan,
					'countFakt'=>$k->count_stations_fakt,
					'percentAll'=>round($k->percentage_stations,2),
					'percentInFlights'=>round($k->percentage_realization,2),
					'countPlanFlight'=>$k->count_flight_plan,
					'countFaktFlight'=>$k->count_flight_fakt,
					'percentDoingFlight'=>round($k->percentage_flight,2),
					'countRouteDirections'=>$k->count_route_directions
				);
			}
			$countRows=count($rows);
			//sort
			function mysort ($a,$b) {
				if ($a['date']==$b['date']) {
					return 0;
				}
				if ($a['date']>$b['date']) {
					return 1;
				}
				if ($a['date']<$b['date']) {
					return -1;
				}
			}
			if (isset($rows)) {
				usort($rows, "mysort");
			}
			if ($chart==0) {
				$carrier=Route::model()->with('carrier')->findAll();
				foreach ($carrier as $k) {
					$arrayCarrier[$k->id]=$k->carrier->name;
				}
				for ($i=0; $i < $countRows; $i++) {
					$rows[$i]['carrierName']=$arrayCarrier[$rows[$i]['routeId']]; 
					$rows[$i]['npp']=$i+1; 
					$rows[$countRows]['date']=Yii::app()->session['InGeneral'];
					$rows[$countRows]['percentInFlights']=$rows[$countRows]['percentInFlights']+$rows[$i]['percentInFlights'];
					//$rows[$countRows]['percentAll']=$rows[$countRows]['percentAll']+$rows[$i]['percentAll'];
					//$rows[$countRows]['percentDoingFlight']=$rows[$countRows]['percentDoingFlight']+$rows[$i]['percentDoingFlight'];
					$rows[$countRows]['countPlan']=$rows[$countRows]['countPlan']+$rows[$i]['countPlan'];
					$rows[$countRows]['countFakt']=$rows[$countRows]['countFakt']+$rows[$i]['countFakt'];
					$rows[$countRows]['countPlanFlight']=$rows[$countRows]['countPlanFlight']+$rows[$i]['countPlanFlight'];
					$rows[$countRows]['countFaktFlight']=$rows[$countRows]['countFaktFlight']+$rows[$i]['countFaktFlight'];
					$rows[$countRows]['countRouteDirections']=$rows[$countRows]['countRouteDirections']+$rows[$i]['countRouteDirections'];
				}
				$rows[$countRows]['npp']=$countRows+1;
				$rows[$countRows]['percentInFlights']=round($rows[$countRows]['percentInFlights']/$countRows,2);
				$rows[$countRows]['percentAll']=round($rows[$countRows]['countFakt']/$rows[$countRows]['countPlan']*100,2);
				$rows[$countRows]['percentDoingFlight']=round($rows[$countRows]['countFaktFlight']/$rows[$countRows]['countPlanFlight']*100,2);
				$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows); 
				echo CJSON::encode($result);
			}
			if ($chart==1) {
				for ($i=0; $i < $countRows; $i++) { 
					$chartRows[]=array(
						'yLabel'=>$rows[$i]['percentInFlights'],
						'xLabel'=>Yii::app()->session['RouteTextFull']." ".$rows[$i]['routeName']."  ".Yii::app()->session['GrafikTextFull']." ".$rows[$i]['graphName']."  ".$rows[$i]['date'],
						'flights'=>$rows[$i]['percentDoingFlight'],
						'stops'=>$rows[$i]['percentAll']
					);
				}
				$result = array('success' => true, 'rows'=>$chartRows); 
				echo CJSON::encode($result);
			}
		}
	}
}
?>	