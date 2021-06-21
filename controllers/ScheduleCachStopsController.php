<?php
class ScheduleCachStopsController extends Controller {
	public function actionRead() {
		$level=Yii::app()->request->getParam('level');
		$nodeId=Yii::app()->request->getParam('recordIdLevel');
		$fromDate=Yii::app()->request->getParam('fromDate');
		$unix1=strtotime($fromDate);
		$unix2=strtotime($fromDate)+23*3600+59*60+59;

		if ($level==2) {
			$pc=StationsScenario::model()->with('stations')->findAll(array(
						'condition'=> 't.routes_id =:route',
						'params'   =>array(':route'=>$nodeId),
						'order'    => 't.id'));
			foreach ($pc as $k) {
				if ($k->pc_status=="yes") {
					$arrPc[]=array(
						'stid'=>$k->stations_id,
						'stN'=>$k->stations->name
					);
				}
			}
			$grAll=Graphs::model()->findAll(array(
						'condition'=> 'routes_id =:route',
						'params'   =>array(':route'=>$nodeId),
						'order'    => 'id'));
			foreach ($grAll as $k) {
				$arrayInsert[]=array(
					'graphId'=>$k->id,
					'routes_id'=>$k->routes_id,
					'carriers_id'=>$k->carriers_id
				);
			}
			$maxflNumber=1;
			$a = LocationsFlight::model()->with('route','stations','graph')->findAll(array(
					'condition'=> 'unixtime >= :f AND unixtime <= :t AND t.routes_id =:route',
					'params'   =>array(':f'=>$unix1, ':t'=>$unix2, ':route'=>$nodeId),
					'order'    => 't.id,t.graphs_id'));
			foreach ($a as $k) {
				$timePlan=new Time($k->arrival_plan);
				$timeReal=strftime('%H:%M:%S',$k->unixtime);
				$all=$timePlan->getFormattedtime()." /".$timeReal." (".$k->time_difference.")";
				$arrayTimes[$k->route->name][$k->graph->name][$k->flights_number][$k->stations->name]=$all;
				$routeName=$k->route->name;
				if ($k->flights_number>$maxflNumber) {
					$maxflNumber=$k->flights_number;
				}
			}
			$n=1;
			for ($i=0; $i < count($arrayInsert); $i++) { 
				for ($ii=0; $ii <count($arrPc) ; $ii++) { 
					$rows[]=array(
						'npp'=>$n,
						'routeName'=>$routeName,
						'graphName'=>$i+1,
						'stationsName'=>$arrPc[$ii]['stN']/*,
						'fl1'=>0,'fl2'=>0,'fl3'=>0,'fl4'=>0,'fl5'=>0,'fl6'=>0,'fl7'=>0,'fl8'=>0,'fl9'=>0,'fl10'=>0,'fl10'=>0,'fl12'=>0,'fl13'=>0,'fl14'=>0,'fl15'=>0*/
						);
					$n=$n+1;
				}
			}
			$countRows=count($rows);
			for ($i=0; $i <$countRows ; $i++) {
				for ($maxF=1; $maxF <=$maxflNumber ; $maxF++) { 
						$rows[$i]['fl'.$maxF]=$arrayTimes[$rows[$i]['routeName']][$rows[$i]['graphName']][$maxF][$rows[$i]['stationsName']];
					} 
				/*$rows[$i]['fl1']=$arrayTimes[$rows[$i]['routeName']][$rows[$i]['graphName']][1][$rows[$i]['stationsName']];
				$rows[$i]['fl2']=$arrayTimes[$rows[$i]['routeName']][$rows[$i]['graphName']][2][$rows[$i]['stationsName']];
				$rows[$i]['fl3']=$arrayTimes[$rows[$i]['routeName']][$rows[$i]['graphName']][3][$rows[$i]['stationsName']];
				$rows[$i]['fl4']=$arrayTimes[$rows[$i]['routeName']][$rows[$i]['graphName']][4][$rows[$i]['stationsName']];
				$rows[$i]['fl5']=$arrayTimes[$rows[$i]['routeName']][$rows[$i]['graphName']][5][$rows[$i]['stationsName']];
				$rows[$i]['fl6']=$arrayTimes[$rows[$i]['routeName']][$rows[$i]['graphName']][6][$rows[$i]['stationsName']];
				$rows[$i]['fl7']=$arrayTimes[$rows[$i]['routeName']][$rows[$i]['graphName']][7][$rows[$i]['stationsName']];
				$rows[$i]['fl8']=$arrayTimes[$rows[$i]['routeName']][$rows[$i]['graphName']][8][$rows[$i]['stationsName']];
				$rows[$i]['fl9']=$arrayTimes[$rows[$i]['routeName']][$rows[$i]['graphName']][9][$rows[$i]['stationsName']];
				$rows[$i]['fl10']=$arrayTimes[$rows[$i]['routeName']][$rows[$i]['graphName']][10][$rows[$i]['stationsName']];
				$rows[$i]['fl11']=$arrayTimes[$rows[$i]['routeName']][$rows[$i]['graphName']][11][$rows[$i]['stationsName']];
				$rows[$i]['fl12']=$arrayTimes[$rows[$i]['routeName']][$rows[$i]['graphName']][12][$rows[$i]['stationsName']];
				$rows[$i]['fl13']=$arrayTimes[$rows[$i]['routeName']][$rows[$i]['graphName']][13][$rows[$i]['stationsName']];
				$rows[$i]['fl14']=$arrayTimes[$rows[$i]['routeName']][$rows[$i]['graphName']][14][$rows[$i]['stationsName']];
				$rows[$i]['fl15']=$arrayTimes[$rows[$i]['routeName']][$rows[$i]['graphName']][15][$rows[$i]['stationsName']];*/
			}
		}
		if ($level==3) {
			$ohol=Yii::app()->request->getParam('ohol');
			if ($ohol==1){

			

			///////////////////////////////////////////////////////////
			$typeDay=date("w",strtotime($fromDate));
			if (($typeDay==0) || ($typeDay==6)) {$typeDay=2;}
			else {$typeDay=1;}
			$uy=Schedules::model()->findAll(array(
				'condition'=> 'graphs_id = :tid AND schedule_types_id = :stidday AND create_date <= :createDate',
				'order'    =>'id',
				'params'   =>array(':tid'=>$nodeId,':stidday'=>$typeDay,':createDate'=>$fromDate)));
			foreach ($uy as $k) {
				$routehistoryId=$k->histories_id;
			}
			$er=Graphs::model()->findByAttributes(array('id'=>$nodeId));
			$graphNumber=$er->name;
			$s1=RouteTimeTable::model()->with('stationName')->findAll(array(
				'condition'=> 'routes_history_id =:gid AND graphs_number =:n',
				'params'   =>array(':gid'=>$routehistoryId, ':n'=>$graphNumber),
				'order'    => 't.number,t.Id'));
			foreach ($s1 as $k) {
				$ar[$k->stations_id][$k->flights_number]=$k->time;
				$arStatName0[$k->stations_id]=$k->stationName->name;
			}
			foreach ($arStatName0 as $key => $value) {
				$arStatName[]=array($key,$value);
			}
			$s2=Yii::app()->db->createCommand()
					    ->select()
					    ->from('stations_locations')
					    ->where('unixtime>='.$unix1.' and unixtime<='.$unix2.' and graphs_id='.$nodeId)
					    ->order('unixtime')
					    ->queryAll();
			foreach ($s2 as $key => $value) {
				$we[$value['stations_id']][]=$value['unixtime']-$unix1;
			}
			foreach ($ar as $st => $arfl) {
			   	foreach ($arfl as $flN => $value) {
			   		foreach ($we as $stLoc => $arLoc) {
			   			foreach ($arLoc as $key => $valuetimeLoc) {
			   				if ($value>$valuetimeLoc-3600) {
			   					if ($value<$valuetimeLoc+3600) {
			   						if ($st==$stLoc) {
			   							$ar[$st][$flN]=$value."/".$valuetimeLoc;
			}   }	}	}	}	}	}  
			//print_r($arStatName);
			for ($ii=0; $ii <count($arStatName) ; $ii++) { 
					$rows[]=array(
						'npp'=>$ii+1,
						'graphName'=>$arStatName[$ii][0],
						'stationsName'=>$arStatName[$ii][1]
					);
				}
			$countRows=count($rows);
			for ($i=0; $i <$countRows ; $i++) {
				for ($s=1; $s < 16; $s++) { 
					$length=strlen($ar[$rows[$i]['graphName']][$s]);
					if ($length>5) {
						$dfg=explode("/", $ar[$rows[$i]['graphName']][$s]);
						$ty=new Time($dfg[1]);
						$rows[$i]['fl'.$s]=$ty->getFormattedTime();
					}
				}
			}
			for ($i=0; $i <$countRows ; $i++) {
				for ($s=1; $s < 16; $s++) {
					$countData[$s]=$countData[$s]+count($rows[$i]['fl'.$s]);
				}
			}
			$rows[$countRows]=array(
						'npp'=>$countRows+1,
						'stationsName'=>"%"
					);
			for ($s=1; $s < 16; $s++) {
				$rows[$countRows]['fl'.$s]=round($countData[$s]/$countRows*100,2);
			}
			$maxflNumber=20;
			}			
		
		



///////////////////////////////////////////////////////////////////
else {
			//print_r($ar);
			$grAll=Graphs::model()->findAll(array(
						'condition'=> 'id =:gid',
						'params'   =>array(':gid'=>$nodeId),
						'order'    => 'id'));
			foreach ($grAll as $k) {
				$arrayInsert[]=array(
					'graphId'=>$k->id,
					'routes_id'=>$k->routes_id,
					'carriers_id'=>$k->carriers_id,
					'name'=>$k->name
				);
			}
			$pc=StationsScenario::model()->with('stations')->findAll(array(
						'condition'=> 't.routes_id =:route',
						'params'   =>array(':route'=>$arrayInsert[0]['routes_id']),
						'order'    => 't.id'));
			foreach ($pc as $k) {
				if ($k->pc_status=="yes") {
					$arrPc[]=array(
						'stid'=>$k->stations_id,
						'stN'=>$k->stations->name
					);
				}
			}
			$maxflNumber=1;
			$a = LocationsFlight::model()->with('route','stations','graph')->findAll(array(
					'condition'=> 'unixtime >= :f AND unixtime <= :t AND t.graphs_id =:gid',
					'params'   =>array(':f'=>$unix1, ':t'=>$unix2, ':gid'=>$nodeId),
					'order'    => 't.id'));
			foreach ($a as $k) {
				$timePlan=new Time($k->arrival_plan);
				$timeReal=strftime('%H:%M:%S',$k->unixtime);
				$all=$timePlan->getFormattedtime()." /".$timeReal." (".$k->time_difference.")";
				$arrayTimes[$k->route->name][$k->graph->name][$k->flights_number][$k->stations->name]=$all;
				$routeName=$k->route->name;
				if ($k->flights_number>$maxflNumber) {
					$maxflNumber=$k->flights_number;
				}
			}

			$n=1;
				for ($ii=0; $ii <count($arrPc) ; $ii++) { 
					$rows[]=array(
						'npp'=>$n,
						'routeName'=>$routeName,
						'graphName'=>$arrayInsert[0]['name'],
						'stationsName'=>$arrPc[$ii]['stN']
						);
					$n=$n+1;
				}
			//print_r($arrayTimes);
			$countRows=count($rows);
			for ($i=0; $i <$countRows ; $i++) { 
				for ($maxF=1; $maxF <=$maxflNumber ; $maxF++) { 
					$rows[$i]['fl'.$maxF]=$arrayTimes[$rows[$i]['routeName']][$rows[$i]['graphName']][$maxF][$rows[$i]['stationsName']];
				}
			}
		}
	}

		$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows,'maxflNumber'=>$maxflNumber); 
		echo CJSON::encode($result);
	}
}