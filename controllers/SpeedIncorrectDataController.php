<?php
class SpeedIncorrectDataController extends Controller {
	public function actionRead() {
		$level=Yii::app()->request->getParam('level');
		$nodeId=Yii::app()->request->getParam('recordIdLevel');
		$fromDate=Yii::app()->request->getParam('fromDate');
		$toDate=Yii::app()->request->getParam('toDate');
		$typeView=Yii::app()->request->getParam('typeView');
		$detailsFor=Yii::app()->request->getParam('detailsFor');

		//$fromDate=$fromDate." 03:00:00";
		

		$carrier=Carriers::model()->findAll();
		foreach ($carrier as $k) 
		{
			$arrayCarriers[$k->id]=$k->name;
		}
		$route=Route::model()->findAll();
		foreach ($route as $k) {
			$arrayRoutes[$k->id]=array(
				'name'=>$k->name,
				'carriers_id'=>$k->carriers_id);
		}
		$graph=Graphs::model()->findAll();
		foreach ($graph as $k) {
			$arrayGraphs[$k->id]=array(
				'name'=>$k->name,
				'routes_id'=>$k->routes_id);
		}
		$bort=Borts::model()->findAll();
		foreach ($bort as $k) {
			$arrayBorts[$k->id]=array(
				'number'=>$k->number,
				"state_number"=>$k->state_number
			);
		}

		if ($typeView==1) {
			$maxSpeed=Yii::app()->request->getParam('maxSpeed');
			if ($level==0)
			{
				if(Yii::app()->user->name != "guest"){
			        $carrierUser = Yii::app()->user->checkUser(Yii::app()->user);
			    }
			    if ($carrierUser) {
			    	$a=ReportSpeedBorts::model()->findAll(array(
						'order'=>'speed_level',
						'condition'=> 'date >= :f AND date <= :t AND carriers_id = :cid',
						'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':cid'=>$carrierUser['carrier_id'])));
			    }
			    else {
			    	$a=ReportSpeedBorts::model()->findAll(array(
						'order'=>'speed_level',
						'condition'=> 'date >= :f AND date <= :t',
						'params'   =>array(':f'=>$fromDate, ':t'=>$toDate)));
			    }
				
				foreach ($a as $k) 
				{
					if ($k->speed_level>=$maxSpeed) {
						$arrayReportSpeedCarriers[$k->carriers_id]=$arrayReportSpeedCarriers[$k->carriers_id]+($k->time_sum);
					}
					$maxSpeedLevelFromTable[$k->carriers_id]=$k->speed_level+1;
					$AllTime[$k->carriers_id]=$AllTime[$k->carriers_id]+($k->time_sum);
				}
				$npp=1;
				foreach ($arrayReportSpeedCarriers as $key => $value) {
					$timeSpeedLevel=new Time($value);
					$rows[]=array(
						'npp'=>$npp,
						'carriers_id'=>$key,
						'carriers_name'=>$arrayCarriers[$key],
						'speed_level'=>$maxSpeed." - ".$maxSpeedLevelFromTable[$key],
						'time'=>$timeSpeedLevel->getFormattedTime(),
						'percent'=> round($value*100/$AllTime[$key],4),
						'typeView'=>$typeView
					);
					$npp=$npp+1;
				}
			}// перевізники
			if ($level==1)
			{
				if ($detailsFor==0) {
					if(Yii::app()->user->name != "guest"){
				        $carrierUser = Yii::app()->user->checkUser(Yii::app()->user);
				    }
				    if ($carrierUser) {
				    	$a=ReportSpeedBorts::model()->findAll(array(
							'order'=>'speed_level',
							'condition'=> 'date >= :f AND date <= :t AND carriers_id = :cid',
							'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':cid'=>$carrierUser['carrier_id'])));
				    }
				    else {
				    	$a=ReportSpeedBorts::model()->findAll(array(
							'order'=>'speed_level',
							'condition'=> 'date >= :f AND date <= :t',
							'params'   =>array(':f'=>$fromDate, ':t'=>$toDate)));
				    }
					foreach ($a as $k) 
					{
						if ($k->speed_level>=$maxSpeed) {
							$arrayReportSpeedRoutes[$k->routes_id]=$arrayReportSpeedRoutes[$k->routes_id]+($k->time_sum);
						}
						$maxSpeedLevelFromTable[$k->routes_id]=$k->speed_level+1;
						$AllTime[$k->routes_id]=$AllTime[$k->routes_id]+($k->time_sum);
					}
				}
				if ($detailsFor!=0) {
					$maxSpeedExplode=explode(" - ", $maxSpeed);
					$maxSpeed=$maxSpeedExplode[0];
					$a=ReportSpeedBorts::model()->findAll(array(
							'order'=>'speed_level',
							'condition'=> 'date >= :f AND date <= :t AND carriers_id = :cid',
							'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':cid'=>$detailsFor)));
				    foreach ($a as $k) 
					{
						if ($k->speed_level>=$maxSpeed) {
							$arrayReportSpeedRoutes[$k->routes_id]=$arrayReportSpeedRoutes[$k->routes_id]+($k->time_sum);
						}
						$maxSpeedLevelFromTable[$k->routes_id]=$k->speed_level+1;
						$AllTime[$k->routes_id]=$AllTime[$k->routes_id]+($k->time_sum);
					}
				}
				$npp=1;
				foreach ($arrayReportSpeedRoutes as $key => $value) {
					$timeSpeedLevel=new Time($value);
					$rows[]=array(
						'npp'=>$npp,
						'carriers_id'=>$arrayRoutes[$key]['carriers_id'],
						'carriers_name'=>$arrayCarriers[$arrayRoutes[$key]['carriers_id']],
						'routes_id'=>$key,
						'routes_name'=>$arrayRoutes[$key]['name'],
						'speed_level'=>$maxSpeed." - ".$maxSpeedLevelFromTable[$key],
						'time'=>$timeSpeedLevel->getFormattedTime(),
						'percent'=> round($value*100/$AllTime[$key],4),
						'typeView'=>$typeView
					);
					$npp=$npp+1;
				}
			}// all routes (buses)
			if ($level==2)
			{
				if ($detailsFor!=0) {
					$maxSpeedExplode=explode(" - ", $maxSpeed);
					$maxSpeed=$maxSpeedExplode[0];
				}
				$a=ReportSpeedBorts::model()->findAll(array(
					'order'=>'speed_level',
					'condition'=> 'date >= :f AND date <= :t AND t.routes_id = :rId',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':rId'=>$nodeId)));
				foreach ($a as $k) 
				{
					if ($k->speed_level>=$maxSpeed) {
						$arrayReportSpeedGraphs[$k->graphs_id]=$arrayReportSpeedGraphs[$k->graphs_id]+($k->time_sum);
					}
					$maxSpeedLevelFromTable[$k->graphs_id]=$k->speed_level+1;
					$AllTime[$k->graphs_id]=$AllTime[$k->graphs_id]+($k->time_sum);
				}
				$npp=1;
				foreach ($arrayReportSpeedGraphs as $key => $value) {
					$timeSpeedLevel=new Time($value);
					$rows[]=array(
						'npp'=>$npp,
						'carriers_id'=>$arrayRoutes[$arrayGraphs[$key]['routes_id']]['carriers_id'],
						'carriers_name'=>$arrayCarriers[$arrayRoutes[$arrayGraphs[$key]['routes_id']]['carriers_id']],
						'routes_id'=>$arrayGraphs[$key]['routes_id'],
						'routes_name'=>$arrayRoutes[$arrayGraphs[$key]['routes_id']]['name'],
						'graphs_id'=>$key,
						'graphs_name'=>$arrayGraphs[$key]['name'],
						'speed_level'=>$maxSpeed." - ".$maxSpeedLevelFromTable[$key],
						'time'=>$timeSpeedLevel->getFormattedTime(),
						'percent'=> round($value*100/$AllTime[$key],4),
						'typeView'=>$typeView
					);
					$npp=$npp+1;
				}
			}// one route
			if ($level==3)
			{
				$a=ReportSpeedBorts::model()->findAll(array(
					'order'=>'speed_level',
					'condition'=> 'date >= :f AND date <= :t AND t.graphs_id = :rId',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':rId'=>$nodeId)));
				foreach ($a as $k) 
				{
					if ($k->speed_level>=$maxSpeed) {
						$arrayReportSpeedGraphs[$k->graphs_id]=$arrayReportSpeedGraphs[$k->graphs_id]+($k->time_sum);
					}
					$maxSpeedLevelFromTable[$k->graphs_id]=$k->speed_level+1;
					$AllTime[$k->graphs_id]=$AllTime[$k->graphs_id]+($k->time_sum);
				}
				$npp=1;
				foreach ($arrayReportSpeedGraphs as $key => $value) {
					$timeSpeedLevel=new Time($value);
					$rows[]=array(
						'npp'=>$npp,
						'carriers_id'=>$arrayRoutes[$arrayGraphs[$key]['routes_id']]['carriers_id'],
						'carriers_name'=>$arrayCarriers[$arrayRoutes[$arrayGraphs[$key]['routes_id']]['carriers_id']],
						'routes_id'=>$arrayGraphs[$key]['routes_id'],
						'routes_name'=>$arrayRoutes[$arrayGraphs[$key]['routes_id']]['name'],
						'graphs_id'=>$key,
						'graphs_name'=>$arrayGraphs[$key]['name'],
						'speed_level'=>$maxSpeed." - ".$maxSpeedLevelFromTable[$key],
						'time'=>$timeSpeedLevel->getFormattedTime(),
						'percent'=> round($value*100/$AllTime[$key],4),
						'typeView'=>$typeView
					);
					$npp=$npp+1;
				}
			}// one route
		}//if ($typeView==1) перевищення швидкості загалом по виірці
		if ($typeView==2) 
		{
			$maxSpeed=Yii::app()->request->getParam('maxSpeed');
			
				if ($level==3)
				{
					$a=ReportSpeedBorts::model()->findAll(array(
						'order'=>'speed_level',
						'condition'=> 'date >= :f AND date <= :t AND graphs_id = :gId',
						'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':gId'=>$nodeId)));
				}
				if ($level==2)
				{
					$a=ReportSpeedBorts::model()->findAll(array(
						'order'=>'speed_level',
						'condition'=> 'date >= :f AND date <= :t AND t.routes_id = :rId',
						'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':rId'=>$nodeId)));
				}
				if ($level==1)
				{
					if(Yii::app()->user->name != "guest"){
				        $carrierUser = Yii::app()->user->checkUser(Yii::app()->user);
				    }
				    if ($carrierUser) {
				    	$a=ReportSpeedBorts::model()->findAll(array(
							'order'=>'speed_level',
							'condition'=> 'date >= :f AND date <= :t AND carriers_id = :cid',
							'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':cid'=>$carrierUser['carrier_id'])));
				    }
				    else {
				    	$a=ReportSpeedBorts::model()->findAll(array(
							'order'=>'speed_level',
							'condition'=> 'date >= :f AND date <= :t',
							'params'   =>array(':f'=>$fromDate, ':t'=>$toDate)));
				    }
				}
				if ($level==0)
				{
					if(Yii::app()->user->name != "guest"){
				        $carrierUser = Yii::app()->user->checkUser(Yii::app()->user);
				    }
				    if ($carrierUser) {
				    	$a=ReportSpeedBorts::model()->findAll(array(
							'order'=>'speed_level',
							'condition'=> 'date >= :f AND date <= :t AND carriers_id = :cid',
							'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':cid'=>$carrierUser['carrier_id'])));
				    }
				    else {
				    	$a=ReportSpeedBorts::model()->findAll(array(
							'order'=>'speed_level',
							'condition'=> 'date >= :f AND date <= :t',
							'params'   =>array(':f'=>$fromDate, ':t'=>$toDate)));
				    }
				}
			

			foreach ($a as $k) {
				if ($k->speed_level>=$maxSpeed) {
					$arrayReportSpeedBorts[$k->carriers_id][$k->routes_id][$k->graphs_id][$k->borts_id]=$arrayReportSpeedBorts[$k->carriers_id][$k->routes_id][$k->graphs_id][$k->borts_id]+($k->time_sum);
				}
				$maxSpeedLevelFromTable[$k->borts_id][$k->graphs_id]=$k->speed_level+1;
				$AllTime[$k->borts_id][$k->graphs_id]=$AllTime[$k->borts_id][$k->graphs_id]+($k->time_sum);
			}
			$npp=1;
			foreach ($arrayReportSpeedBorts as $cid => $cidArray) {
				foreach ($cidArray as $rid => $ridArray) {
					foreach ($ridArray as $gid => $gidArray) {
						foreach ($gidArray as $bid => $value) {
							$timeSpeedLevel=new Time($value);
							$rows[]=array(
								'npp'=>$npp,
								'borts_id'=>$bid,
								'bortNumber'=>$arrayBorts[$bid]['number'],
								'bortStateNumber'=>$arrayBorts[$bid]['state_number'],
								'graphs_id'=>$gid,
								'graphs_name'=>$arrayGraphs[$gid]['name'],
								'routes_id'=>$rid,
								'routes_name'=>$arrayRoutes[$rid]['name'],
								'carriers_id'=>$cid,
								'carriers_name'=>$arrayCarriers[$cid],
								'speed_level'=>$maxSpeed." - ".$maxSpeedLevelFromTable[$bid][$gid],
								'time'=>$timeSpeedLevel->getFormattedTime(),
								'percent'=> round($value*100/$AllTime[$bid][$gid],4),
								'typeView'=>$typeView
							);
							$npp=$npp+1;
						}
					}
				}
			}
		}//if ($typeView==2) перевищення швидкості по бортам
		if ($typeView==3) 
		{
			$speedLevelRegime=Yii::app()->request->getParam('SpeedLevel');
			if ($level==0)
			{
				if(Yii::app()->user->name != "guest"){
			        $carrierUser = Yii::app()->user->checkUser(Yii::app()->user);
			    }
			    if ($carrierUser) {
			    	$a=ReportSpeedBorts::model()->findAll(array(
						'order'=>'speed_level',
						'condition'=> 'date >= :f AND date <= :t AND carriers_id = :cid',
						'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':cid'=>$carrierUser['carrier_id'])));
			    }
			    else {
			    	$a=ReportSpeedBorts::model()->findAll(array(
						'order'=>'speed_level',
						'condition'=> 'date >= :f AND date <= :t',
						'params'   =>array(':f'=>$fromDate, ':t'=>$toDate)));
			    }
				foreach ($a as $k) {
					if (!isset($k->speed_level)) {
						$keyLevel=999;
					}
					else if ($k->speed_level==0) {
						//$keyLevel=$speedLevelRegime;
						$keyLevel=0;
					}
					else {
						$keyLevel=intval($k->speed_level/$speedLevelRegime);
					}
					/*if ($keyLevel!=0) {
						$arrayReportSpeed[$k->carriers_id][$keyLevel]=$arrayReportSpeed[$k->carriers_id][$keyLevel]+($k->time_sum);
						$AllTime[$k->carriers_id]=$AllTime[$k->carriers_id]+($k->time_sum);
					}*/
					$arrayReportSpeed[$k->carriers_id][$keyLevel]=$arrayReportSpeed[$k->carriers_id][$keyLevel]+($k->time_sum);
					$AllTime[$k->carriers_id]=$AllTime[$k->carriers_id]+($k->time_sum);
				}
				$npp=1;
				foreach ($arrayReportSpeed as $cid => $cidArray) {
					foreach ($cidArray as $key => $value) {
						if ($key!=999) 
								{
									$speedLevelFrom=$key*$speedLevelRegime;
									$speedLevelTo=$speedLevelFrom+$speedLevelRegime;
									$keyLev=$speedLevelFrom." - ".$speedLevelTo;
								}
								else if ($key==999) 
								{
									$keyLev=Yii::app()->session['canNotBeDetermined'];
								}
						$timeSpeedLevel= new Time($value);
						$rows[]=array(
							'npp'=>$npp,
							'carriers_id'=>$cid,
							'carriers_name'=>$arrayCarriers[$cid],
							'speed_level'=>$keyLev,
							'time'=>$timeSpeedLevel->getFormattedTime(),
							'percent'=> round($value*100/$AllTime[$cid],4),
				 			'typeView'=>$typeView
						);
						$npp=$npp+1;
					}
				}
			}//carriers
			if ($level==1)
			{
				if(Yii::app()->user->name != "guest"){
			        $carrierUser = Yii::app()->user->checkUser(Yii::app()->user);
			    }
			    if ($carrierUser) {
			    	$a=ReportSpeedBorts::model()->findAll(array(
						'order'=>'speed_level',
						'condition'=> 'date >= :f AND date <= :t AND carriers_id = :cid',
						'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':cid'=>$carrierUser['carrier_id'])));
			    }
			    else {
			    	if ($detailsFor!=0) {
						$maxSpeedExplode=explode(" - ", $maxSpeed);
						$maxSpeed=$maxSpeedExplode[0];
						$a=ReportSpeedBorts::model()->findAll(array(
								'order'=>'speed_level',
								'condition'=> 'date >= :f AND date <= :t AND carriers_id = :cid',
								'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':cid'=>$detailsFor)));
					}
					else {
						$a=ReportSpeedBorts::model()->findAll(array(
							'order'=>'speed_level',
							'condition'=> 'date >= :f AND date <= :t',
							'params'   =>array(':f'=>$fromDate, ':t'=>$toDate)));
					}
			    }
				foreach ($a as $k) {
					if (!isset($k->speed_level)) {
						$keyLevel=999;
					}
					else if ($k->speed_level==0) {
						//$keyLevel=$speedLevelRegime;
						$keyLevel=0;
					}
					else {
						$keyLevel=intval($k->speed_level/$speedLevelRegime);
					}
					/*if ($keyLevel!=0) {
						$arrayReportSpeed[$k->carriers_id][$k->routes_id][$keyLevel]=$arrayReportSpeed[$k->carriers_id][$k->routes_id][$keyLevel]+($k->time_sum);
						$AllTime[$k->carriers_id][$k->routes_id]=$AllTime[$k->carriers_id][$k->routes_id]+($k->time_sum);
					}*/
					$arrayReportSpeed[$k->carriers_id][$k->routes_id][$keyLevel]=$arrayReportSpeed[$k->carriers_id][$k->routes_id][$keyLevel]+($k->time_sum);
					$AllTime[$k->carriers_id][$k->routes_id]=$AllTime[$k->carriers_id][$k->routes_id]+($k->time_sum);
				}
				$npp=1;
				foreach ($arrayReportSpeed as $cid => $cidArray) {
					foreach ($cidArray as $rid => $ridArray) {
						foreach ($ridArray as $key => $value) {
							if ($key!=999) 
								{
									$speedLevelFrom=$key*$speedLevelRegime;
									$speedLevelTo=$speedLevelFrom+$speedLevelRegime;
									$keyLev=$speedLevelFrom." - ".$speedLevelTo;
								}
								else if ($key==999) 
								{
									$keyLev=Yii::app()->session['canNotBeDetermined'];
								}
							$timeSpeedLevel= new Time($value);
							$rows[]=array(
								'npp'=>$npp,
								'carriers_id'=>$cid,
								'carriers_name'=>$arrayCarriers[$cid],
								'routes_id'=>$rid,
								'routes_name'=>$arrayRoutes[$rid]['name'],
								'speed_level'=>$keyLev,
								'time'=>$timeSpeedLevel->getFormattedTime(),
								'percent'=> round($value*100/$AllTime[$cid][$rid],4),
								'typeView'=>$typeView
							);
							$npp=$npp+1;
						}
					}
				}
			}
			if ($level==2)
			{
				$a=ReportSpeedBorts::model()->findAll(array(
					'order'=>'speed_level',
					'condition'=> 'date >= :f AND date <= :t AND t.routes_id = :rId',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':rId'=>$nodeId)));
				foreach ($a as $k) {
					if (!isset($k->speed_level)) {
						$keyLevel=999;
					}
					else if ($k->speed_level==0) {
						//$keyLevel=$speedLevelRegime;
						$keyLevel=0;
					}
					else {
						$keyLevel=intval($k->speed_level/$speedLevelRegime);
					}
					/*if ($keyLevel!=0) {
						$arrayReportSpeed[$k->carriers_id][$k->routes_id][$k->graphs_id][$keyLevel]=$arrayReportSpeed[$k->carriers_id][$k->routes_id][$k->graphs_id][$keyLevel]+($k->time_sum);
						$AllTime[$k->carriers_id][$k->routes_id][$k->graphs_id]=$AllTime[$k->carriers_id][$k->routes_id][$k->graphs_id]+($k->time_sum);
					}*/
					$arrayReportSpeed[$k->carriers_id][$k->routes_id][$k->graphs_id][$keyLevel]=$arrayReportSpeed[$k->carriers_id][$k->routes_id][$k->graphs_id][$keyLevel]+($k->time_sum);
					$AllTime[$k->carriers_id][$k->routes_id][$k->graphs_id]=$AllTime[$k->carriers_id][$k->routes_id][$k->graphs_id]+($k->time_sum);
				}
				$npp=1;
				foreach ($arrayReportSpeed as $cid => $cidArray) {
					foreach ($cidArray as $rid => $ridArray) {
						foreach ($ridArray as $gid => $gidArray) {
							foreach ($gidArray as $key => $value) {
								if ($key!=999) 
								{
									$speedLevelFrom=$key*$speedLevelRegime;
									$speedLevelTo=$speedLevelFrom+$speedLevelRegime;
									$keyLev=$speedLevelFrom." - ".$speedLevelTo;
								}
								else if ($key==999) 
								{
									$keyLev=Yii::app()->session['canNotBeDetermined'];
								}
								$timeSpeedLevel= new Time($value);
								$rows[]=array(
									'npp'=>$npp,
									'carriers_id'=>$cid,
									'carriers_name'=>$arrayCarriers[$cid],
									'routes_name'=>$arrayRoutes[$rid]['name'],
									'routes_id'=>$rid,
									'graphs_id'=>$gid,
									'graphs_name'=>$arrayGraphs[$gid]['name'],
									'speed_level'=>$keyLev,
									'time'=>$timeSpeedLevel->getFormattedTime(),
									'percent'=> round($value*100/$AllTime[$cid][$rid][$gid],4),
									'typeView'=>$typeView
								);
								$npp=$npp+1;
							}
						}	
					}
				}
			}
			if ($level==3)
			{
				$a=ReportSpeedBorts::model()->findAll(array(
					'order'=>'speed_level',
					'condition'=> 'date >= :f AND date <= :t AND t.graphs_id = :gId',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':gId'=>$nodeId)));
				foreach ($a as $k) {
					if (!isset($k->speed_level)) {
						$keyLevel=999;
					}
					else if ($k->speed_level==0) {
						//$keyLevel=$speedLevelRegime;
						$keyLevel=0;
					}
					else {
						$keyLevel=intval($k->speed_level/$speedLevelRegime);
					}
					/*if ($keyLevel!=0) {
						$arrayReportSpeed[$k->carriers_id][$k->routes_id][$k->graphs_id][$keyLevel]=$arrayReportSpeed[$k->carriers_id][$k->routes_id][$k->graphs_id][$keyLevel]+($k->time_sum);
						$AllTime[$k->carriers_id][$k->routes_id][$k->graphs_id]=$AllTime[$k->carriers_id][$k->routes_id][$k->graphs_id]+($k->time_sum);
					}*/
					$arrayReportSpeed[$k->carriers_id][$k->routes_id][$k->graphs_id][$keyLevel]=$arrayReportSpeed[$k->carriers_id][$k->routes_id][$k->graphs_id][$keyLevel]+($k->time_sum);
					$AllTime[$k->carriers_id][$k->routes_id][$k->graphs_id]=$AllTime[$k->carriers_id][$k->routes_id][$k->graphs_id]+($k->time_sum);
				}
				$npp=1;
				foreach ($arrayReportSpeed as $cid => $cidArray) {
					foreach ($cidArray as $rid => $ridArray) {
						foreach ($ridArray as $gid => $gidArray) {
							foreach ($gidArray as $key => $value) {
								if ($key!=999) 
								{
									$speedLevelFrom=$key*$speedLevelRegime;
									$speedLevelTo=$speedLevelFrom+$speedLevelRegime;
									$keyLev=$speedLevelFrom." - ".$speedLevelTo;
								}
								else if ($key==999) 
								{
									$keyLev=Yii::app()->session['canNotBeDetermined'];
								}
								$timeSpeedLevel= new Time($value);
								$rows[]=array(
									'npp'=>$npp,
									'carriers_id'=>$cid,
									'carriers_name'=>$arrayCarriers[$cid],
									'routes_name'=>$arrayRoutes[$rid]['name'],
									'routes_id'=>$rid,
									'graphs_id'=>$gid,
									'graphs_name'=>$arrayGraphs[$gid]['name'],
									'speed_level'=>$keyLev,
									'time'=>$timeSpeedLevel->getFormattedTime(),
									'percent'=> round($value*100/$AllTime[$cid][$rid][$gid],4),
									'typeView'=>$typeView
								);
								$npp=$npp+1;
							}
						}	
					}
				}
			}
		}//if ($typeView==3) режим швидкості
		if($typeView==4) {
			$speedLevelRegime=Yii::app()->request->getParam('SpeedLevel');
			if ($level==3)
			{
				$a=ReportSpeedBorts::model()->findAll(array(
					'order'=>'speed_level',
					'condition'=> 'date >= :f AND date <= :t AND graphs_id = :gId',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':gId'=>$nodeId)));
			}
			if ($level==2)
			{
				$a=ReportSpeedBorts::model()->findAll(array(
					'order'=>'speed_level',
					'condition'=> 'date >= :f AND date <= :t AND t.routes_id = :rId',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':rId'=>$nodeId)));
			}
			if ($level==1)
			{
				if(Yii::app()->user->name != "guest"){
			        $carrierUser = Yii::app()->user->checkUser(Yii::app()->user);
			    }
			    if ($carrierUser) {
			    	$a=ReportSpeedBorts::model()->findAll(array(
						'order'=>'speed_level',
						'condition'=> 'date >= :f AND date <= :t AND carriers_id = :cid',
						'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':cid'=>$carrierUser['carrier_id'])));
			    }
			    else {
			    	$a=ReportSpeedBorts::model()->findAll(array(
						'order'=>'speed_level',
						'condition'=> 'date >= :f AND date <= :t',
						'params'   =>array(':f'=>$fromDate, ':t'=>$toDate)));
			    }
			}
			if ($level==0)
			{
				if(Yii::app()->user->name != "guest"){
			        $carrierUser = Yii::app()->user->checkUser(Yii::app()->user);
			    }
			    if ($carrierUser) {
			    	$a=ReportSpeedBorts::model()->findAll(array(
						'order'=>'speed_level',
						'condition'=> 'date >= :f AND date <= :t AND carriers_id = :cid',
						'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':cid'=>$carrierUser['carrier_id'])));
			    }
			    else {
			    	$a=ReportSpeedBorts::model()->findAll(array(
						'order'=>'speed_level',
						'condition'=> 'date >= :f AND date <= :t',
						'params'   =>array(':f'=>$fromDate, ':t'=>$toDate)));
			    }
			}
			foreach ($a as $k) {
				if (!isset($k->speed_level)) {
					$keyLevel=999;
				}
				else if ($k->speed_level==0) {
					//$keyLevel=$speedLevelRegime;
					$keyLevel=0;
				}
				else {
					$keyLevel=intval($k->speed_level/$speedLevelRegime);
				}
				/*if ($keyLevel!=0) {
					$arrayReportSpeed[$k->carriers_id][$k->routes_id][$k->graphs_id][$k->borts_id][$keyLevel]=$arrayReportSpeed[$k->carriers_id][$k->routes_id][$k->graphs_id][$k->borts_id][$keyLevel]+($k->time_sum);
					$AllTime[$k->carriers_id][$k->routes_id][$k->graphs_id][$k->borts_id]=$AllTime[$k->carriers_id][$k->routes_id][$k->graphs_id][$k->borts_id]+($k->time_sum);
				}*/
				$arrayReportSpeed[$k->carriers_id][$k->routes_id][$k->graphs_id][$k->borts_id][$keyLevel]=$arrayReportSpeed[$k->carriers_id][$k->routes_id][$k->graphs_id][$k->borts_id][$keyLevel]+($k->time_sum);
				$AllTime[$k->carriers_id][$k->routes_id][$k->graphs_id][$k->borts_id]=$AllTime[$k->carriers_id][$k->routes_id][$k->graphs_id][$k->borts_id]+($k->time_sum);
			}
			$npp=1;
			foreach ($arrayReportSpeed as $cid => $cidArray) {
				foreach ($cidArray as $rid => $ridArray) {
					foreach ($ridArray as $gid => $gidArray) {
						foreach ($gidArray as $bid => $bidArray) {
							foreach ($bidArray as $key => $value) {
								if ($key!=999) 
								{
									$speedLevelFrom=$key*$speedLevelRegime;
									$speedLevelTo=$speedLevelFrom+$speedLevelRegime;
									$keyLev=$speedLevelFrom." - ".$speedLevelTo;
								}
								else if ($key==999) 
								{
									$keyLev=Yii::app()->session['canNotBeDetermined'];
								}
								$timeSpeedLevel= new Time($value);
								$rows[]=array(
									'npp'=>$npp,
									'borts_id'=>$bid,
									'bortNumber'=>$arrayBorts[$bid]['number'],
									'bortStateNumber'=>$arrayBorts[$bid]['state_number'],
									'graphs_id'=>$gid,
									'graphs_name'=>$arrayGraphs[$gid]['name'],
									'routes_id'=>$rid,
									'routes_name'=>$arrayRoutes[$rid]['name'],
									'carriers_name'=>$arrayCarriers[$cid],
									'carriers_id'=>$cid,
									'speed_level'=>$keyLev,
									'time'=>$timeSpeedLevel->getFormattedTime(),
									'percent'=> round($value*100/$AllTime[$cid][$rid][$gid][$bid],4),
									'typeView'=>$typeView
								);
								$npp=$npp+1;
							}
						}
					}	
				}
			}
		}//if ($typeView==4) режим швидкості borts
		//$countRows=count($rows);

		//sort
		function mySortReportSpeedAll ($a,$b) {
				if ( $a['carriers_name'] == $b['carriers_name'] ) {
					if (isset($a['routes_name'])) {
						if ($a['routes_name']==$b['routes_name']) {
							if (isset($a['graphs_name'])) {
								if ($a['graphs_name']==$b['graphs_name']) {
									if (isset($a['bortStateNumber'])) {
										if ($a['bortStateNumber']==$b['bortStateNumber']) {
											if ($a['npp']==$b['npp']) {
												return 0;
											}
											if ($a['npp']>$b['npp']) {
												return 1;
											}
											if ($a['npp']<$b['npp']) {
												return -1;
											}
										}
										if ($a['bortStateNumber']>$b['bortStateNumber']) {
											return 1;
										}
										if ($a['bortStateNumber']<$b['bortStateNumber']) {
											return -1;
										}
									}
									else {
										if ($a['npp']==$b['npp']) {
											return 0;
										}
										if ($a['npp']>$b['npp']) {
											return 1;
										}
										if ($a['npp']<$b['npp']) {
											return -1;
										}
									}
								}
								if ($a['graphs_name']>$b['graphs_name']) {
									return 1;
								}
								if ($a['graphs_name']==$b['graphs_name']) {
									return -1;
								}
							}
							else {
								if ($a['npp']==$b['npp']) {
									return 0;
								}
								if ($a['npp']>$b['npp']) {
									return 1;
								}
								if ($a['npp']<$b['npp']) {
									return -1;
								}
							}
						}
						if ($a['routes_name']>$b['routes_name']) {
							return 1;
						}
						if ($a['routes_name']==$b['routes_name']) {
							return -1;
						}
					}
					else {
						if ($a['npp']==$b['npp']) {
							return 0;
						}
						if ($a['npp']>$b['npp']) {
							return 1;
						}
						if ($a['npp']<$b['npp']) {
							return -1;
						}
					}
				}
				if ( $a['carriers_name'] > $b['carriers_name'] ) {
					return 1;
				}
				if ( $a['carriers_name'] < $b['carriers_name'] ) {
					return -1;
				}
			}
			if (isset($rows)) {
				usort($rows, "mySortReportSpeedAll");
			}
		$countRows=count($rows);
		$numberNpp=1;
		for ($i=0; $i <$countRows ; $i++) { 
			$rows[$i]['npp']=$numberNpp;
			$numberNpp=$numberNpp+1;
		}
		$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows);
		echo CJSON::encode($result);
	}// actionRead
}
?>