<?php
class ReportPercentageFlightsGraphsController extends CController  {
	public function actionRead()//на посилання з гет
	{
		$sortNo=Yii::app()->request->getParam('sortNo');
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
	        	$a = ReportPercentageFlightsGraphs::model()->with('route','graph','bort')->findAll(array(
					'condition'=> 'date >= :f AND date <= :t',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate),
					'order'    => 't.id'));
				foreach ($a as $k) {
					if ($k->route->carriers_id==$carrier['carrier_id']) {
						$rows[]=array(
							'id'=>$k->id,
							'routeId'=>$k->routes_id,
							'routeName'=>$k->route->name,
							'graphName'=>$k->graph->name,
							'graphId'=>$k->graphs_id,
							'date'=>$k->date,
							'flight'=>$k->flights_number,
							'percentAll'=>$k->percentage_stations,
							'percentEndStops'=>$k->percentage_end_stops,
							'percentInFlights'=>$k->percentage_realization,
							'countPlan'=>$k->count_stations_plan,
							'countFakt'=>$k->count_stations_fakt,
							'bortName'=>$k->bort->number,
							'bortStateNumber'=>$k->bort->state_number,
							'countRouteDirections'=>$k->count_route_directions
						);
					}	
				}
				$countRows=count($rows);

	        } else {
	        	$a = ReportPercentageFlightsGraphs::model()->with('route','graph','bort')->findAll(array(
					'condition'=> 'date >= :f AND date <= :t',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate),
					'order'    => 't.id'));
				foreach ($a as $k) {
					$rows[]=array(
							'id'=>$k->id,
							'routeId'=>$k->routes_id,
							'routeName'=>$k->route->name,
							'graphName'=>$k->graph->name,
							'graphId'=>$k->graphs_id,
							'date'=>$k->date,
							'flight'=>$k->flights_number,
							'percentAll'=>$k->percentage_stations,
							'percentEndStops'=>$k->percentage_end_stops,
							'percentInFlights'=>$k->percentage_realization,
							'countPlan'=>$k->count_stations_plan,
							'countFakt'=>$k->count_stations_fakt,
							'bortName'=>$k->bort->number,
							'bortStateNumber'=>$k->bort->state_number,
							'countRouteDirections'=>$k->count_route_directions
					);
				}
				$countRows=count($rows);
			}
		}
		////////////////////////////////////////////////////////////////////////////////////////////
		if ($level==2) {
			$a = ReportPercentageFlightsGraphs::model()->with('route','graph','bort')->findAll(array(
				'condition'=> 't.routes_id = :rhid AND date >= :f AND date <= :t',
				'params'   => array(':rhid' => $nodeId, ':f'=>$fromDate, ':t'=>$toDate),
				'order'    => 't.id'));
			foreach ($a as $k) {
				$rows[]=array(
						'id'=>$k->id,
						'routeId'=>$k->routes_id,
						'graphId'=>$k->graphs_id,
						'routeName'=>$k->route->name,
						'graphName'=>$k->graph->name,
						'date'=>$k->date,
						'flight'=>$k->flights_number,
						'percentAll'=>$k->percentage_stations,
						'percentInFlights'=>$k->percentage_realization,
						'countPlan'=>$k->count_stations_plan,
						'countFakt'=>$k->count_stations_fakt,
						'bortName'=>$k->bort->number,
						'percentEndStops'=>$k->percentage_end_stops,
						'bortStateNumber'=>$k->bort->state_number,
						'countRouteDirections'=>$k->count_route_directions
				);
			}
			$countRows=count($rows);
		
		}
		if ($level==3) {
			$a = ReportPercentageFlightsGraphs::model()->with('route','graph','bort')->findAll(array(
				'condition'=> 't.graphs_id = :rhid AND date >= :f AND date <= :t',
				'params'   => array(':rhid' => $nodeId, ':f'=>$fromDate, ':t'=>$toDate),
				'order'    => 't.id'));
			foreach ($a as $k) {
				$rows[]=array(
						'id'=>$k->id,
						'routeId'=>$k->routes_id,
						'graphId'=>$k->graphs_id,
						'routeName'=>$k->route->name,
						'graphName'=>$k->graph->name,
						'date'=>$k->date,
						'flight'=>$k->flights_number,
						'percentAll'=>$k->percentage_stations,
						'percentInFlights'=>$k->percentage_realization,
						'countPlan'=>$k->count_stations_plan,
						'countFakt'=>$k->count_stations_fakt,
						'bortName'=>$k->bort->number,
						'percentEndStops'=>$k->percentage_end_stops,
						'bortStateNumber'=>$k->bort->state_number,
						'countRouteDirections'=>$k->count_route_directions
				);
			}
			$countRows=count($rows);
			
		}
		//сортування за маршр граф рейс дата
			function sortArrayForChartFlights ($a,$b) {
				if ( $a['date'] == $b['date'] ) {
					if ( $a['routeName'] == $b['routeName'] ) {
						if ( $a['graphName'] == $b['graphName'] ) {
							if ( $a['flight'] == $b['flight'] ) {
								return 0;
							}
							if ( $a['flight'] > $b['flight'] ) {
								return 1;
							}
							if ( $a['flight'] < $b['flight'] ) {
								return -1;
							}
						}
						if ( $a['graphName'] > $b['graphName'] ) {
							return 1;
						}
						if ( $a['graphName'] < $b['graphName'] ) {
							return -1;
						}
					}
					if ( $a['routeName'] < $b['routeName'] ) {
						return -1;
					}
					if ( $a['routeName'] > $b['routeName'] ) {
						return 1;
					}
				}
				else if ( $a['date'] < $b['date'] ) {
					return -1;
				}
				else {
					return 1;
				}
					
			}
			//sort
			if (isset($rows)) {
				usort($rows, "sortArrayForChartFlights");	
			}
				
		if ($chart==0) {
			if ($sortNo==0) {
				for ($i=0; $i < $countRows; $i++) { 
					$rows[$i]['npp']=$i+1; 
				}
				$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows); 
				echo CJSON::encode($result);
			}
			if ($sortNo==1) {
				for ($i=0; $i < $countRows; $i++) { 
					if ($rows[$i]['percentInFlights']==0) {
						$rowsNo[]=$rows[$i];
					}
				}
				$countRowsNo=count($rowsNo);
				for ($i=0; $i <$countRowsNo ; $i++) { 
					$rowsNo[$i]['npp']=$i+1;
				}
				$result = array('success' => true, 'rows'=>$rowsNo, 'totalCount'=>$countRowsNo); 
				echo CJSON::encode($result);
			}
			if ($sortNo==2) {
				for ($i=0; $i < $countRows; $i++) { 
					if ($rows[$i]['percentInFlights']<50) {
						$rowsNo[]=$rows[$i];
					}
				}
				$countRowsNo=count($rowsNo);
				for ($i=0; $i <$countRowsNo ; $i++) { 
					$rowsNo[$i]['npp']=$i+1;
				}
				$result = array('success' => true, 'rows'=>$rowsNo, 'totalCount'=>$countRowsNo); 
				echo CJSON::encode($result);
			}
			if ($sortNo==3) {
				for ($i=0; $i < $countRows; $i++) { 
					if ($rows[$i]['flight']==1) {
						$rowsNo[]=$rows[$i];
					}
				}
				$countRowsNo=count($rowsNo);
				for ($i=0; $i <$countRowsNo ; $i++) { 
					$rowsNo[$i]['npp']=$i+1;
				}
				$result = array('success' => true, 'rows'=>$rowsNo, 'totalCount'=>$countRowsNo); 
				echo CJSON::encode($result);
			}
			if ($sortNo==4) {
				for ($i=0; $i < $countRows; $i++) { 
					if ($rows[$i]['flight']!=$rows[$i+1]['flight']-1) {
						$rowsNo[]=$rows[$i];
					}
				}
				$countRowsNo=count($rowsNo);
				for ($i=0; $i <$countRowsNo ; $i++) { 
					$rowsNo[$i]['npp']=$i+1;
				}
				$result = array('success' => true, 'rows'=>$rowsNo, 'totalCount'=>$countRowsNo); 
				echo CJSON::encode($result);
			}
		}

		if ($chart==1) {
			if ($level==1) {
				for ($i=0; $i < $countRows; $i++) {
					$chartRows[]=array(
						'yLabel'=>$rows[$i]['percentInFlights'],
						'xLabel'=>" ".Yii::app()->session['RouteTextFull']." ".$rows[$i]['routeName']."  ".Yii::app()->session['GrafikTextFull']." ".$rows[$i]['graphName']."  ".Yii::app()->session['FlightText']." ".$rows[$i]['flight']
					);
				}
			}
			if ($level==2) {
				for ($i=0; $i < $countRows; $i++) {
					$chartRows[]=array(
						'yLabel'=>$rows[$i]['percentInFlights'],
						'xLabel'=>" ".Yii::app()->session['RouteTextFull']." ".$rows[$i]['routeName']."  ".Yii::app()->session['GrafikTextFull']." ".$rows[$i]['graphName']."  ".Yii::app()->session['FlightText']." ".$rows[$i]['flight']
					);
				}
			}
			if ($level==3) {
				for ($i=0; $i < $countRows; $i++) {
					$chartRows[]=array(
						'yLabel'=>$rows[$i]['percentInFlights'],
						'xLabel'=>" ".Yii::app()->session['RouteTextFull']." ".$rows[$i]['routeName']."  ".Yii::app()->session['GrafikTextFull']." ".$rows[$i]['graphName']."  ".Yii::app()->session['FlightText']." ".$rows[$i]['flight']
					);
				}
			}
			$result = array('success' => true, 'rows'=>$chartRows); 
			echo CJSON::encode($result);
		}	
	}
}
?>	