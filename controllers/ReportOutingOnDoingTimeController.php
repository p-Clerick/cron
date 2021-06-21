<?php
class ReportOutingOnDoingTimeController extends CController  {
	public function actionRead()//на посилання з гет
	{
		$level=Yii::app()->request->getParam('level');
		$nodeId=Yii::app()->request->getParam('recordIdLevel');
		$fromDateDate=Yii::app()->request->getParam('fromDateData');
		$fromDateTime=Yii::app()->request->getParam('fromDateTime');
		$time1=strtotime($fromDateDate." ".$fromDateTime);
		$timeFrom=$time1-(60*60);
		$time=$time1+(60*60);
		$st='yes';
		if ($level==1) {
			if(Yii::app()->user->name != "guest"){
	            $carrier = Yii::app()->user->checkUser(Yii::app()->user);
	        }
	        if ($carrier) {
	        	$a=Graphs::model()->with('route','carrier')->findAll(array(
	        		'condition'=> 't.carriers_id = :car',
					'params'   =>array(':car' => $carrier['carrier_id']),
					'order'    => 'route.name'));
				foreach ($a as $k) {
					$arrayGraphs[$k->routes_id][$k->id]=$k->status;
					if ($k->status=='yes') {
						$countPlan[$k->routes_id]=$countPlan[$k->routes_id]+1;
					}
					$arrayRoutes[$k->routes_id]=$k->route->name;
					$arrayCarriers[$k->routes_id]=$k->carrier->name;
				}
				$b=LocationsFlight::model()->with('route')->findAll(array(
					'condition'=> 'unixtime >= :f AND unixtime <= :t',
					'params'   =>array(':f'=>$timeFrom, ':t'=>$time),
					'order'    => 't.unixtime'));
				foreach ($b as $kk) {
					if ($kk->route->carriers_id==$carrier['carrier_id']) {
						$arrayIns[$kk->routes_id][$kk->graphs_id]=1;
					}
				}
	        }
	        else {
	        	$a=Graphs::model()->with('route','carrier')->findAll(array(
					'order'    => 'route.name'));
				foreach ($a as $k) {
					$arrayGraphs[$k->routes_id][$k->id]=$k->status;
					if ($k->status=='yes') {
						$countPlan[$k->routes_id]=$countPlan[$k->routes_id]+1;
					}
					$arrayRoutes[$k->routes_id]=$k->route->name;
					$arrayCarriers[$k->routes_id]=$k->carrier->name;
				}
				$b=LocationsFlight::model()->with('route')->findAll(array(
					'condition'=> 'unixtime >= :f AND unixtime <= :t',
					'params'   =>array(':f'=>$timeFrom, ':t'=>$time),
					'order'    => 't.unixtime'));
				foreach ($b as $kk) {
					$arrayIns[$kk->routes_id][$kk->graphs_id]=1;
					
				}
			}	
			foreach ($arrayGraphs as $key => $value) {
				foreach ($value as $key1 => $value1) {
					if (isset($arrayIns[$key][$key1])) {
						$arrT[$key][$key1]=1;
					}
					if (!isset($arrayIns[$key][$key1])) {
						$arrT[$key][$key1]=2;
					}
				}

			}
			foreach ($arrT as $key => $value) {
				foreach ($value as $key1 => $value1) {
					if ($value1 == 1) {
						$countFact[$key]=$countFact[$key]+1;
					}
				}
			}
			$y=1;
			foreach ($countPlan as $key => $value) {
				$rows[]=array(
					'npp'=>$y,
					'routeId'=>$key, 
					'routeName'=>$arrayRoutes[$key], 
					'carriers' => $arrayCarriers[$key], 
					'planCount'=>$value, 
					'factCount'=>$countFact[$key], 
					'difference'=>$countFact[$key]-$value,
					'percent'=>round(100*$countFact[$key]/$value,2)
				);
				$y=$y+1;
			}
		} 
		if ($level==2) {
			$a=Graphs::model()->with('route','carrier')->findAll(array(
				'condition' => 'routes_id = :t',
				'params'   =>array(':t'=>$nodeId),
				'order'    => 't.name'));
			foreach ($a as $k) {
				$arrayGraphs[$k->routes_id][$k->id]=$k->status;
				if ($k->status=='yes') {
					$countPlan[$k->routes_id]=$countPlan[$k->routes_id]+1;
				}
				$arrayRoutes[$k->routes_id]=$k->route->name;
				$arrayCarriers[$k->routes_id]=$k->carrier->name;
			}
			$b=LocationsFlight::model()->with('route')->findAll(array(
				'condition'=> 'unixtime >= :f AND unixtime <= :t AND routes_id = :rid',
				'params'   =>array(':f'=>$timeFrom, ':t'=>$time, ':rid' =>$nodeId),
				'order'    => 't.unixtime'));
			foreach ($b as $kk) {
				$arrayIns[$kk->routes_id][$kk->graphs_id]=1;
				
			}
			foreach ($arrayGraphs as $key => $value) {
				foreach ($value as $key1 => $value1) {
					if (isset($arrayIns[$key][$key1])) {
						$arrT[$key][$key1]=1;
					}
					if (!isset($arrayIns[$key][$key1])) {
						$arrT[$key][$key1]=2;
					}
				}

			}
			foreach ($arrT as $key => $value) {
				foreach ($value as $key1 => $value1) {
					if ($value1 == 1) {
						$countFact[$key]=$countFact[$key]+1;
					}
				}
			}
			$y=1;
			foreach ($countPlan as $key => $value) {
				$rows[]=array(
					'npp'=>$y,
					'routeId'=>$key, 
					'routeName'=>$arrayRoutes[$key], 
					'carriers' => $arrayCarriers[$key], 
					'planCount'=>$value, 
					'factCount'=>$countFact[$key], 
					'difference'=>$countFact[$key]-$value,
					'percent'=>round(100*$countFact[$key]/$value,2)
				);
				$y=$y+1;
			}
		} 
		if ($level==3) {
			$k=Graphs::model()->with('route','carrier')->findByAttributes(array('id'=>$nodeId));
			
				$arrayGraphs[$k->routes_id][$k->id]=$k->status;
				if ($k->status=='yes') {
					$countPlan[$k->routes_id]=$countPlan[$k->routes_id]+1;
				}
				$arrayRoutes[$k->routes_id]=$k->route->name;
				$arrayCarriers[$k->routes_id]=$k->carrier->name;
			
			$b=LocationsFlight::model()->with('route')->findAll(array(
				'condition'=> 'unixtime >= :f AND unixtime <= :t AND graphs_id = :rid',
				'params'   =>array(':f'=>$timeFrom, ':t'=>$time, ':rid' =>$nodeId),
				'order'    => 't.unixtime'));
			foreach ($b as $kk) {
				$arrayIns[$kk->routes_id][$kk->graphs_id]=1;
				
			}
			foreach ($arrayGraphs as $key => $value) {
				foreach ($value as $key1 => $value1) {
					if (isset($arrayIns[$key][$key1])) {
						$arrT[$key][$key1]=1;
					}
					if (!isset($arrayIns[$key][$key1])) {
						$arrT[$key][$key1]=2;
					}
				}

			}
			foreach ($arrT as $key => $value) {
				foreach ($value as $key1 => $value1) {
					if ($value1 == 1) {
						$countFact[$key]=$countFact[$key]+1;
					}
				}
			}
			$y=1;
			foreach ($countPlan as $key => $value) {
				$rows[]=array(
					'npp'=>$y,
					'routeId'=>$key, 
					'routeName'=>$arrayRoutes[$key], 
					'carriers' => $arrayCarriers[$key], 
					'planCount'=>$value, 
					'factCount'=>$countFact[$key], 
					'difference'=>$countFact[$key]-$value,
					'percent'=>round(100*$countFact[$key]/$value,2)
				);
				$y=$y+1;
			}
		} 
		$countRows=count($rows);
		for ($i=0; $i < $countRows; $i++) { 
			$rows[$countRows]['planCount']=$rows[$countRows]['planCount']+$rows[$i]['planCount'];
			$rows[$countRows]['factCount']=$rows[$countRows]['factCount']+$rows[$i]['factCount'];
		}
		$rows[$countRows]['npp']=$countRows+1;
		$rows[$countRows]['routeName']=Yii::app()->session['AllText'];
		$rows[$countRows]['carriers']=Yii::app()->session['InGeneral'];
		$rows[$countRows]['difference']=$rows[$countRows]['factCount']-$rows[$countRows]['planCount'];
		$rows[$countRows]['percent']=round(100*$rows[$countRows]['factCount']/$rows[$countRows]['planCount'],2);
		$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows); 
		echo CJSON::encode($result);
		
	}
}
?>	