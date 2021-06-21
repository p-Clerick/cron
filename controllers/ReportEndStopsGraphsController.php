<?php
class ReportEndStopsGraphsController extends CController  {
	public function actionRead()//на посилання з гет
	{

	
	/*	
		//визначення інтервалів
		$rt1=28;
		$rt2=1366005889;
		$fg=LocationsFlights::model()->with('stations')->findAll(array(
					'condition'=> 'routes_id = :t AND unixtime > :od',
					'params'   =>array(':t'=> $rt1, ':od'=> $rt2),
					'order'    => 'graphs_id,unixtime'));
		foreach ($fg as $kg) {
			$arrInterv[]=array('st'=>$kg->stations_id,'stN'=>$kg->stations->name,'time'=>$kg->unixtime,'graphs_id'=>$kg->graphs_id);
		}
		for ($i=0; $i < count($arrInterv)-1; $i++) { 
			if ((round(($arrInterv[$i+1]['time']-$arrInterv[$i]['time'])/60,2)>-20) && (round(($arrInterv[$i+1]['time']-$arrInterv[$i]['time'])/60,2)<20)) {
				$arrIntervT[$arrInterv[$i]['stN']." до ".$arrInterv[$i+1]['stN']][]=round(($arrInterv[$i+1]['time']-$arrInterv[$i]['time'])/60,2);
			}
		}
		foreach ($arrIntervT as $key => $value) {
			$middle[$key]=round(array_sum($value)/count($value),2);
		}
		foreach ($arrIntervT as $key => $value) {
			$arrIntervT[$key]['middle']=$middle[$key];
		}
		print_r($arrIntervT);
 		//print_r($middle);
*/
//////////////////////////////////////////////////////////////////////////////////////
		$period=Yii::app()->request->getParam('period');
		$level=Yii::app()->request->getParam('level');
		$nodeId=Yii::app()->request->getParam('recordIdLevel');
		$fromDate=Yii::app()->request->getParam('fromDate');
		$toDate=Yii::app()->request->getParam('toDate');
		if ($period==1){
			if ($level==1) {
				if(Yii::app()->user->name != "guest"){
		            $carrier = Yii::app()->user->checkUser(Yii::app()->user);
		        }
		        if ($carrier) {
		        	$a=ReportEndStops::model()->with('route','graph','station','bort','park','carrier')->findAll(array(
						'condition'=> 'date >= :f AND date <= :t',
						'params'   =>array(':f'=>$fromDate, ':t'=>$toDate),
						'order'    => 't.id'));
					foreach ($a as $k) {
						if ($k->route->carriers_id==$carrier['carrier_id']) {
							$timeOld= new Time($k->arrival_plan);
							$rows[]=array(
								'routeId'=>$k->routes_id,
								'routeName'=>$k->route->name,
								'graphName'=>$k->graph->name,
								'graphId'=>$k->graphs_id,
								'date'=>$k->date,
								'parkName'=>$k->carrier->name,
								'flightNumber'=>$k->flights_number,
								'stationsId'=>$k->stations_id,
								'stationsName'=>$k->station->name,
								'bortId'=>$k->borts_id,
								'bortNumber'=>$k->bort->number,
								'bortStateNumber'=>$k->bort->state_number,
								'arrival_plan'=>$timeOld->getFormattedTime()
							);
						}
					}
					$countRows=count($rows);
					for ($i=0; $i <$countRows ; $i++) { 
						$am[$rows[$i]['stationsName']]=$am[$rows[$i]['stationsName']]+1;
					}
					foreach ($am as $key => $value) {
						$amount=$amount."</br>".$key.": ".$value." ".Yii::app()->session['PiecesText'];
					}
		        }
		        else {
		        	$a=ReportEndStops::model()->with('route','graph','station','bort','park','carrier')->findAll(array(
						'condition'=> 'date >= :f AND date <= :t',
						'params'   =>array(':f'=>$fromDate, ':t'=>$toDate),
						'order'    => 't.id'));
					foreach ($a as $k) {
						$timeOld= new Time($k->arrival_plan);
						$rows[]=array(
							'routeId'=>$k->routes_id,
							'routeName'=>$k->route->name,
							'graphName'=>$k->graph->name,
							'graphId'=>$k->graphs_id,
							'date'=>$k->date,
							'parkName'=>$k->carrier->name,
							'flightNumber'=>$k->flights_number,
							'stationsId'=>$k->stations_id,
							'stationsName'=>$k->station->name,
							'bortId'=>$k->borts_id,
							'bortNumber'=>$k->bort->number,
							'bortStateNumber'=>$k->bort->state_number,
							'arrival_plan'=>$timeOld->getFormattedTime()
						);
					}
					$countRows=count($rows);
					for ($i=0; $i <$countRows ; $i++) { 
						$am[$rows[$i]['stationsName']]=$am[$rows[$i]['stationsName']]+1;
					}
					foreach ($am as $key => $value) {
						$amount=$amount."</br>".$key.": ".$value." ".Yii::app()->session['PiecesText'];
					}
				}	
			}
			if ($level==2) {
				$a=ReportEndStops::model()->with('route','graph','station','park','carrier')->findAll(array(
					'condition'=> 'date >= :f AND date <= :t AND t.routes_id = :rid',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':rid'=>$nodeId),
					'order'    => 't.id'));
				foreach ($a as $k) {
					$timeOld= new Time($k->arrival_plan);
					$rows[]=array(
						'routeId'=>$k->routes_id,
						'graphId'=>$k->graphs_id,
						'routeName'=>$k->route->name,
						'graphName'=>$k->graph->name,
						'date'=>$k->date,
						'flightNumber'=>$k->flights_number,
						'stationsId'=>$k->stations_id,
						'stationsName'=>$k->station->name,
						'bortId'=>$k->borts_id,
						'bortNumber'=>$k->bort->number,
						'parkName'=>$k->carrier->name,
						'bortStateNumber'=>$k->bort->state_number,
						'arrival_plan'=>$timeOld->getFormattedTime()
					);
				}
				$countRows=count($rows);
				for ($i=0; $i <$countRows ; $i++) { 
					$am[$rows[$i]['stationsName']]=$am[$rows[$i]['stationsName']]+1;
				}
				foreach ($am as $key => $value) {
					$amount=$amount."</br>".$key.": ".$value." ".Yii::app()->session['PiecesText'];
				}
			}
			if ($level==3) {
				$a=ReportEndStops::model()->with('route','graph','station','park','carrier')->findAll(array(
					'condition'=> 'date >= :f AND date <= :t AND t.graphs_id = :rid',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':rid'=>$nodeId),
					'order'    => 't.id'));
				foreach ($a as $k) {
					$timeOld= new Time($k->arrival_plan);
					$rows[]=array(
						'routeId'=>$k->routes_id,
						'graphId'=>$k->graphs_id,
						'routeName'=>$k->route->name,
						'graphName'=>$k->graph->name,
						'date'=>$k->date,
						'parkName'=>$k->carrier->name,
						'flightNumber'=>$k->flights_number,
						'stationsId'=>$k->stations_id,
						'stationsName'=>$k->station->name,
						'bortId'=>$k->borts_id,
						'bortNumber'=>$k->bort->number,
						'bortStateNumber'=>$k->bort->state_number,
						'arrival_plan'=>$timeOld->getFormattedTime()
					);
				}
				$countRows=count($rows);
				for ($i=0; $i <$countRows ; $i++) { 
					$am[$rows[$i]['stationsName']]=$am[$rows[$i]['stationsName']]+1;
				}
				foreach ($am as $key => $value) {
					$amount=$amount."</br>".$key.": ".$value." ".Yii::app()->session['PiecesText'];
				}
			}
		
			function mySort ($a,$b) {
				if ($a['date']==$b['date']) {
					if ($a['routeName']==$b['routeName']) {
						if ($a['graphName']==$b['graphName']) {
							if ($a['flightNumber']==$b['flightNumber']) {
								if ($a['arrival_plan']==$b['arrival_plan']) {
									return 0;
								}
								if ($a['arrival_plan']>$b['arrival_plan']) {
									return 1;
								}
								if ($a['arrival_plan']<$b['arrival_plan']) {
									return -1;
								}
							}
							if ($a['flightNumber']>$b['flightNumber']) {
								return 1;
							}
							if ($a['flightNumber']<$b['flightNumber']) {
								return -1;
							}
						}
						if ($a['graphName']>$b['graphName']) {
							return 1;
						}
						if ($a['graphName']<$b['graphName']) {
							return -1;
						}
					}
					if ($a['routeName']>$b['routeName']) {
						return 1;
					}
					if ($a['routeName']<$b['routeName']) {
						return -1;
					}
				}
				if ($a['date']>$b['date']) {
					return 1;
				}
				if ($a['date']<$b['date']) {
					return -1;
				}
			}
			if (isset($rows)) {
				usort($rows, "mySort");
			}
			for ($i=0; $i <$countRows ; $i++) { 
				$rows[$i]['npp']=$i+1;
			}
			$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows, 'amount'=>$amount); 
			echo CJSON::encode($result);
		}//if period=1	
		if ($period==2) {
			if ($level==3) {
				$a=ReportEndStops::model()->with('route','graph','station','park','carrier')->findAll(array(
					'condition'=> 'date >= :f AND date <= :t AND t.graphs_id = :rid',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':rid'=>$nodeId),
					'order'    => 't.date'));
				foreach ($a as $k) {
					$routeIdL3=$k->routes_id;
					$routeNameL3=$k->route->name;
					$graphIdL3=$k->graphs_id;
					$graphNameL3=$k->graph->name;
					$parkNameL3=$k->carrier->name;
					$rowsAll[]=array(
						'date'=>$k->date,
						'stationsId'=>$k->stations_id,
						'stationsName'=>$k->station->name,
						'count'=>1
					);
				}
				for ($i=0; $i <count($rowsAll) ; $i++) { 
					$amountfordate[$rowsAll[$i]['date']][$rowsAll[$i]['stationsName']]=$amountfordate[$rowsAll[$i]['date']][$rowsAll[$i]['stationsName']]+1;
				}
				foreach ($amountfordate as $key => $value) {
					foreach ($value as $key1 => $value1) {
						$rows[]=array(
							'routeId'=>$routeIdL3,
							'graphId'=>$graphIdL3,
							'routeName'=>$routeNameL3,
							'graphName'=>$graphNameL3,
							'date'=>$key,
							'parkName'=>$parkNameL3,
							'flightNumber'=>$value1,
							'stationsName'=>$key1
						);
					}
				}
			}
			if ($level==2) {
				$a=ReportEndStops::model()->with('route','graph','station','park','carrier')->findAll(array(
					'condition'=> 'date >= :f AND date <= :t AND t.routes_id = :rid',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':rid'=>$nodeId),
					'order'    => 't.date'));
				foreach ($a as $k) {
					$routeIdL3=$k->routes_id;
					$routeNameL3=$k->route->name;
					$parkNameL3=$k->carrier->name;
					$rowsAll[]=array(
						'date'=>$k->date,
						'stationsId'=>$k->stations_id,
						'stationsName'=>$k->station->name,
						'count'=>1
					);
				}
				for ($i=0; $i <count($rowsAll) ; $i++) { 
					$amountfordate[$rowsAll[$i]['date']][$rowsAll[$i]['stationsName']]=$amountfordate[$rowsAll[$i]['date']][$rowsAll[$i]['stationsName']]+1;
				}
				foreach ($amountfordate as $key => $value) {
					foreach ($value as $key1 => $value1) {
						$rows[]=array(
							'routeId'=>$routeIdL3,
							'routeName'=>$routeNameL3,
							'date'=>$key,
							'parkName'=>$parkNameL3,
							'flightNumber'=>$value1,
							'stationsName'=>$key1
						);
					}
				}
			}
			if ($level==1) {
				if(Yii::app()->user->name != "guest"){
		            $carrier = Yii::app()->user->checkUser(Yii::app()->user);
		        }
		        if ($carrier) {
		        	$a=ReportEndStops::model()->with('route','graph','station','bort','park','carrier')->findAll(array(
							'condition'=> 'date >= :f AND date <= :t AND route.carriers_id = :carid',
							'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':carid'=>$carrier['carrier_id']),
							'order'    => 't.date'));
					foreach ($a as $k) {
						if ($k->carriers_id==$carrier['carrier_id']) {
							$rowsAll[$k->carrier->name][$k->station->name][]=1;
						}
					}
					foreach ($rowsAll as $key => $value) {
						foreach ($value as $key1 => $value1) {
							$rows[]=array(
								'parkName'=>$key,
								'flightNumber'=>count($value1),
								'stationsName'=>$key1
							);
						}
					}
		        }
		        else {

		        
					$a=ReportEndStops::model()->with('route','graph','station','bort','park','carrier')->findAll(array(
							'condition'=> 'date >= :f AND date <= :t',
							'params'   =>array(':f'=>$fromDate, ':t'=>$toDate),
							'order'    => 't.date'));
					foreach ($a as $k) {
						$rowsAll[$k->carrier->name][$k->station->name][]=1;
					}
					foreach ($rowsAll as $key => $value) {
						foreach ($value as $key1 => $value1) {
							$rows[]=array(
								'parkName'=>$key,
								'flightNumber'=>count($value1),
								'stationsName'=>$key1
							);
						}
					}
				}
			}


			$countRows=count($rows);
			for ($i=0; $i <$countRows ; $i++) { 
					$am[$rows[$i]['stationsName']]=$am[$rows[$i]['stationsName']]+$rows[$i]['flightNumber'];
				}
				foreach ($am as $key => $value) {
					$amount=$amount."</br>".$key.": ".$value." ".Yii::app()->session['PiecesText'];
				}
			
			for ($i=0; $i <$countRows ; $i++) { 
				$rows[$i]['npp']=$i+1;
			}
			$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows, 'amount'=>$amount); 
			echo CJSON::encode($result);
		}
	}
}
?>	