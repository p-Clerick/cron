<?php
class SpeedDetailsController extends Controller {
	public function actionRead() {
		$fromDate1=Yii::app()->request->getParam('fromDate');
		$toDate1=Yii::app()->request->getParam('toDate');
		$minSpeed=Yii::app()->request->getParam('minSpeed');
		$maxSpeed=Yii::app()->request->getParam('maxSpeed');
		$level=Yii::app()->request->getParam('level');
		$nodeId=Yii::app()->request->getParam('recordIdLevel');
		$group=Yii::app()->request->getParam('group');
		$stationFrom=Yii::app()->request->getParam('stationFrom');
		$stationTo=Yii::app()->request->getParam('stationTo');
		$duration=Yii::app()->request->getParam('duration');
		$issetBort=Yii::app()->request->getParam('issetBort');

		$fromDate   = strtotime($fromDate1)+ 3*3600;
		$toDate     = strtotime($toDate1)  +26*3600+59*60+59+3600;

		$maxTimeToNoData=30;

		if ($minSpeed==Yii::app()->session['canNotBeDetermined']) {
			$canNotBeDetermined=1;
		}
		else {
			$canNotBeDetermined=0;
		}

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
///////////////////////
/////$group==0/////////
///////////////////////
		if ($group==0) {
			if ($level==3) {
				if ($issetBort==0) {
					$a=Locations::model()->findAll(array(
						'order'=>'unixtime',
						'condition'=> 'unixtime >= :f AND unixtime <= :t AND graphs_id = :gid',
						'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':gid'=>$nodeId)));
				}
				if ($issetBort==1) {
					$needBortId=Yii::app()->request->getParam('borts_id');
					$a=Locations::model()->findAll(array(
						'order'=>'unixtime',
						'condition'=> 'unixtime >= :f AND unixtime <= :t AND graphs_id = :gid AND borts_id = :bid',
						'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':gid'=>$nodeId, ':bid'=>$needBortId)));
				}
			}
			if ($level==2) {
				$a=Locations::model()->findAll(array(
					'order'=>'unixtime',
					'condition'=> 'unixtime >= :f AND unixtime <= :t AND routes_id = :rid',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':rid'=>$nodeId)));
			}
			if ($level==1) {
				$a=Locations::model()->findAll(array(
					'order'=>'unixtime',
					'condition'=> 'unixtime >= :f AND unixtime <= :t',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate)));
			}
			if ($level==0) {
				$a=Locations::model()->findAll(array(
					'order'=>'unixtime',
					'condition'=> 'unixtime >= :f AND unixtime <= :t',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate)));
			}
			foreach ($a as $k) {
				$arraySpeedAll[$k->routes_id][$k->graphs_id][$k->borts_id][]=array(
						'fromLat'=>$k->latitude,
						'fromLong'=>$k->longitude,
						'unixtime'=>$k->unixtime,
						'speed'=>$k->speed
					);
			}
			foreach ($arraySpeedAll as $rid => $ridArray) {
				foreach ($ridArray as $gid => $gidArray) {
					foreach ($gidArray as $bid => $arraySpeed) {
						for ($i=0; $i <count($arraySpeed)-1 ; $i++) { 
							$timeBetween=$arraySpeed[$i+1]['unixtime']-$arraySpeed[$i]['unixtime'];
							if ($timeBetween!=0) {
								$R=6372795;
								$φA=(double) (floor($arraySpeed[$i]['fromLat']/100)*100+(($arraySpeed[$i]['fromLat']   - floor($arraySpeed[$i]['fromLat']/100)*100)*100/60))/100;
								$λA=(double) (floor($arraySpeed[$i]['fromLong']/100)*100+(($arraySpeed[$i]['fromLong']   - floor($arraySpeed[$i]['fromLong']/100)*100)*100/60))/100;
								$φB=(double) (floor($arraySpeed[$i+1]['fromLat']/100)*100+(($arraySpeed[$i+1]['fromLat']   - floor($arraySpeed[$i+1]['fromLat']/100)*100)*100/60))/100;
								$λB=(double) (floor($arraySpeed[$i+1]['fromLong']/100)*100+(($arraySpeed[$i+1]['fromLong']   - floor($arraySpeed[$i+1]['fromLong']/100)*100)*100/60))/100;
								// перевести координаты в радианы
							    $lat1 = $φA * M_PI / 180;
							    $lat2 = $φB * M_PI / 180;
							    $long1 = $λA * M_PI / 180;
							    $long2 = $λB * M_PI / 180;
							    // косинусы и синусы широт и разницы долгот
							    $cl1 = cos($lat1);
							    $cl2 = cos($lat2);
							    $sl1 = sin($lat1);
								$sl2 = sin($lat2);
								$delta = $long2 - $long1;
								$cdelta = cos($delta);
								$sdelta = sin($delta);
							 
								// вычисления длины большого круга
								$y = sqrt(pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2));
								$x = $sl1 * $sl2 + $cl1 * $cl2 * $cdelta;
								 
								//
								$ad = atan2($y, $x);
								$dist = round($ad * $R,0);
								$Distance=$dist;
								$speed=round(($Distance/$timeBetween)*3.6,2); 
								

								$timeBetweenFormat=new Time($timeBetween);
								if ($timeBetween>$maxTimeToNoData) {
									$noteText=Yii::app()->session['betweenDataMoreThan30s'];
									$note=1;
								} 
								if ($timeBetween<=$maxTimeToNoData) {
									$roundToInt=intval($speed);
									if ($roundToInt>=120) {
										$noteText=Yii::app()->session['SpeedMoreThan120kmPerH'];
										$note=1;
									}
									if ($roundToInt<120) {
										if (($arraySpeed[$i]['speed']==0) || ($arraySpeed[$i+1]['speed']==0)) {
											if ($roundToInt>=80) {
												$noteText=Yii::app()->session['SpeedOneOfSpeedGPSnull'];
												$note=1;
											}
											if ($roundToInt<80) {
												$note=0;
											}
										}
										else {
											$note=0;
										}
									}
								}
								if (($canNotBeDetermined==1) && ($note==1)){
									$rows[]=array(
											'npp'=>count($rows)+1,
											'borts_id'=>$bid,
											'bortNumber'=>$arrayBorts[$bid]['number'],
											'bortStateNumber'=>$arrayBorts[$bid]['state_number'],
											'carriers_name'=>$arrayCarriers[$arrayRoutes[$rid]['carriers_id']],
											'note'=>$noteText,
											'graphs_id'=>$gid,
											'graphs_name'=>$arrayGraphs[$gid]['name'],
											'routes_id'=>$rid,
											'routes_name'=>$arrayRoutes[$rid]['name'],
											'fromTime'=>date("Y-m-d H:i:s",$arraySpeed[$i]['unixtime']),
											'toTime'=>date("Y-m-d H:i:s",$arraySpeed[$i+1]['unixtime']),
											'timeBetween'=>$timeBetweenFormat->getFormattedTime(),
											'distance'=>$Distance/1000,
											'fromSpeed'=>$arraySpeed[$i]['speed'],
											'toSpeed'=>$arraySpeed[$i+1]['speed'],
											'fromLat1'=>$φA,
											'toLat2'=>$φB,
											'fromLong1'=>$λA,
											'toLong2'=>$λB,
											'speedCoordinates'=>$speed,
											'speedAverage'=>round(($arraySpeed[$i]['speed']+$arraySpeed[$i+1]['speed'])/2,2),
											'speedCoordinatesSum'=>$speed,
											'speedAverageSum'=>round(($arraySpeed[$i]['speed']+$arraySpeed[$i+1]['speed'])/2,2)
										);
									}
									if (($canNotBeDetermined==0) && ($note==0)){
										if (($speed>=$minSpeed) && ($speed<$maxSpeed)) {
											$rows[]=array(
												'npp'=>count($rows)+1,
												'borts_id'=>$bid,
												'bortNumber'=>$arrayBorts[$bid]['number'],
												'bortStateNumber'=>$arrayBorts[$bid]['state_number'],
												'carriers_name'=>$arrayCarriers[$arrayRoutes[$rid]['carriers_id']],
												//'note'=>$note,
												'graphs_id'=>$gid,
												'graphs_name'=>$arrayGraphs[$gid]['name'],
												'routes_id'=>$rid,
												'routes_name'=>$arrayRoutes[$rid]['name'],
												'fromTime'=>date("Y-m-d H:i:s",$arraySpeed[$i]['unixtime']),
												'toTime'=>date("Y-m-d H:i:s",$arraySpeed[$i+1]['unixtime']),
												'timeBetween'=>$timeBetweenFormat->getFormattedTime(),
												'distance'=>$Distance/1000,
												'fromSpeed'=>$arraySpeed[$i]['speed'],
												'toSpeed'=>$arraySpeed[$i+1]['speed'],
												'fromLat1'=>$φA,
												'toLat2'=>$φB,
												'fromLong1'=>$λA,
												'toLong2'=>$λB,
												'speedCoordinates'=>$speed,
												'speedAverage'=>round(($arraySpeed[$i]['speed']+$arraySpeed[$i+1]['speed'])/2,2),
												'speedCoordinatesSum'=>$speed,
												'speedAverageSum'=>round(($arraySpeed[$i]['speed']+$arraySpeed[$i+1]['speed'])/2,2)
											);
										}
									}
								
							}
						}unset($arraySpeed);
					}
				}
			}

			$countRows=count($rows);
			$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows);
			echo CJSON::encode($result);
		}
///////////////////////
/////$group==1/////////
///////////////////////

		if ($group==1) {
			//шукаемо координати зупинок
			$stFrom=Stations::model()->findByAttributes(array('id'=>$stationFrom));
			$stLongFrom=(float)$stFrom->longitude;
			$stLatFrom=(float)$stFrom->latitude;
			$stTo=Stations::model()->findByAttributes(array('id'=>$stationTo));
			$stLongTo=(float)$stTo->longitude;
			$stLatTo=(float)$stTo->latitude;
			if ($stLongFrom>$stLongTo) {
				$fromLong=$stLongTo;
				$toLong=$stLongFrom;
			}
			if ($stLongFrom<$stLongTo) {
				$toLong=$stLongTo;
				$fromLong=$stLongFrom;
			}
			if ($stLatFrom>$stLatTo) {
				$fromLat=$stLatTo;
				$toLat=$stLatFrom;
			}
			if ($stLatFrom<$stLatTo) {
				$toLat=$stLatTo;
				$fromLat=$stLatFrom;
			}
			//$diff1=abs($toLat-$fromLong);
			//if ($diff1<0.9) {
				$fromLat=$fromLat-0.09;
				$toLat=$toLat+0.09;
			//}
			//$diff2=abs($toLong-$fromLong);
			//if ($diff2<0.9) {
				$fromLong=$fromLong-0.09;
				$toLong=$toLong+0.09;
			//}
			if ($level==3) {
				$a=Locations::model()->findAll(array(
					'order'=>'unixtime',
					'condition'=> 'unixtime >= :f AND unixtime <= :t AND graphs_id = :gid AND latitude >= :fromLat AND latitude <= :toLat AND longitude >= :fromLong AND longitude <= :toLong',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':gid'=>$nodeId, ':fromLat'=>$fromLat, ':toLat'=>$toLat, ':fromLong'=>$fromLong, ':toLong'=>$toLong)));
			}
			if ($level==2) {
				$a=Locations::model()->findAll(array(
					'order'=>'unixtime',
					'condition'=> 'unixtime >= :f AND unixtime <= :t AND routes_id = :rid AND latitude >= :fromLat AND latitude <= :toLat AND longitude >= :fromLong AND longitude <= :toLong',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':rid'=>$nodeId, ':fromLat'=>$fromLat, ':toLat'=>$toLat, ':fromLong'=>$fromLong, ':toLong'=>$toLong)));
			}
			if ($level==1) {
				$a=Locations::model()->findAll(array(
					'order'=>'unixtime',
					'condition'=> 'unixtime >= :f AND unixtime <= :t AND latitude >= :fromLat AND latitude <= :toLat AND longitude >= :fromLong AND longitude <= :toLong',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':fromLat'=>$fromLat, ':toLat'=>$toLat, ':fromLong'=>$fromLong, ':toLong'=>$toLong)));
			}
			if ($level==0) {
				$a=Locations::model()->findAll(array(
					'order'=>'unixtime',
					'condition'=> 'unixtime >= :f AND unixtime <= :t AND latitude >= :fromLat AND latitude <= :toLat AND longitude >= :fromLong AND longitude <= :toLong',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':fromLat'=>$fromLat, ':toLat'=>$toLat, ':fromLong'=>$fromLong, ':toLong'=>$toLong)));
			}
			foreach ($a as $k) {
				$arraySpeedAll[$k->routes_id][$k->graphs_id][$k->borts_id][]=array(
						'fromLat'=>$k->latitude,
						'fromLong'=>$k->longitude,
						'unixtime'=>$k->unixtime,
						'speed'=>$k->speed
					);
			}
			foreach ($arraySpeedAll as $rid => $ridArray) {
				foreach ($ridArray as $gid => $gidArray) {
					foreach ($gidArray as $bid => $arraySpeed) {
						for ($i=0; $i <count($arraySpeed)-1 ; $i++) { 
							$timeBetween=$arraySpeed[$i+1]['unixtime']-$arraySpeed[$i]['unixtime'];
							if ($timeBetween!=0) {
								if ($timeBetween<=$maxTimeToNoData) {
									$R=6372795;
									$φA=(double) (floor($arraySpeed[$i]['fromLat']/100)*100+(($arraySpeed[$i]['fromLat']   - floor($arraySpeed[$i]['fromLat']/100)*100)*100/60))/100;
									$λA=(double) (floor($arraySpeed[$i]['fromLong']/100)*100+(($arraySpeed[$i]['fromLong']   - floor($arraySpeed[$i]['fromLong']/100)*100)*100/60))/100;
									$φB=(double) (floor($arraySpeed[$i+1]['fromLat']/100)*100+(($arraySpeed[$i+1]['fromLat']   - floor($arraySpeed[$i+1]['fromLat']/100)*100)*100/60))/100;
									$λB=(double) (floor($arraySpeed[$i+1]['fromLong']/100)*100+(($arraySpeed[$i+1]['fromLong']   - floor($arraySpeed[$i+1]['fromLong']/100)*100)*100/60))/100;
									// перевести координаты в радианы
								    $lat1 = $φA * M_PI / 180;
								    $lat2 = $φB * M_PI / 180;
								    $long1 = $λA * M_PI / 180;
								    $long2 = $λB * M_PI / 180;
								    // косинусы и синусы широт и разницы долгот
								    $cl1 = cos($lat1);
								    $cl2 = cos($lat2);
								    $sl1 = sin($lat1);
								    $sl2 = sin($lat2);
								    $delta = $long2 - $long1;
								    $cdelta = cos($delta);
								    $sdelta = sin($delta);
								    // вычисления длины большого круга
								    $y = sqrt(pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2));
								    $x = $sl1 * $sl2 + $cl1 * $cl2 * $cdelta;
									//
								    $ad = atan2($y, $x);
								    $dist = round($ad * $R,0);
									$Distance=$dist;
									$speed=round(($Distance/$timeBetween)*3.6,2);
									$roundToInt=intval($speed);
									if ($roundToInt>=120) {

									}
									if ($roundToInt<120) {
										if (($arraySpeed[$i]['speed']==0) || ($arraySpeed[$i+1]['speed']==0)) {
											if ($roundToInt>=80) {

											}
											if ($roundToInt<80) {
												if (($speed>=$minSpeed) && ($speed<$maxSpeed))
												{
													$rowsAll[$bid][]=array(
														'npp'=>count($rows)+1,
														'borts_id'=>$bid,
														'bortNumber'=>$arrayBorts[$bid]['number'],
														'bortStateNumber'=>$arrayBorts[$bid]['state_number'],
														'carriers_name'=>$arrayCarriers[$arrayRoutes[$rid]['carriers_id']],
														'graphs_id'=>$gid,
														'graphs_name'=>$arrayGraphs[$gid]['name'],
														'routes_id'=>$rid,
														'routes_name'=>$arrayRoutes[$rid]['name'],
														'fromTime'=>date("Y-m-d H:i:s",$arraySpeed[$i]['unixtime']),
														'toTime'=>date("Y-m-d H:i:s",$arraySpeed[$i+1]['unixtime']),
														'timeBetween'=>$timeBetween,
														'distance'=>$Distance/1000,
														'fromSpeed'=>$arraySpeed[$i]['speed'],
														'toSpeed'=>$arraySpeed[$i+1]['speed'],
														'fromLat1'=>$φA,
														'toLat2'=>$φB,
														'fromLong1'=>$λA,
														'toLong2'=>$λB,
														'speedCoordinates'=>$speed,
														'speedAverage'=>round(($arraySpeed[$i]['speed']+$arraySpeed[$i+1]['speed'])/2,2)
													);
												}
											}
										}
										else  {
											if (($speed>=$minSpeed) && ($speed<$maxSpeed))
											{
												$rowsAll[$bid][]=array(
													'npp'=>count($rows)+1,
													'borts_id'=>$bid,
													'bortNumber'=>$arrayBorts[$bid]['number'],
													'bortStateNumber'=>$arrayBorts[$bid]['state_number'],
													'carriers_name'=>$arrayCarriers[$arrayRoutes[$rid]['carriers_id']],
													'graphs_id'=>$gid,
													'graphs_name'=>$arrayGraphs[$gid]['name'],
													'routes_id'=>$rid,
													'routes_name'=>$arrayRoutes[$rid]['name'],
													'fromTime'=>date("Y-m-d H:i:s",$arraySpeed[$i]['unixtime']),
													'toTime'=>date("Y-m-d H:i:s",$arraySpeed[$i+1]['unixtime']),
													'timeBetween'=>$timeBetween,
													'distance'=>$Distance/1000,
													'fromSpeed'=>$arraySpeed[$i]['speed'],
													'toSpeed'=>$arraySpeed[$i+1]['speed'],
													'fromLat1'=>$φA,
													'toLat2'=>$φB,
													'fromLong1'=>$λA,
													'toLong2'=>$λB,
													'speedCoordinates'=>$speed,
													'speedAverage'=>round(($arraySpeed[$i]['speed']+$arraySpeed[$i+1]['speed'])/2,2)
												);
											}
										}
									}
								}
							}
						}
					}
				}
			}
			foreach ($rowsAll as $bid => $arrayBorts) {
					for ($i=0; $i <count($arrayBorts) ; $i++) { 
						if ($i==0) {
							$rowsN[]=$arrayBorts[$i];
							$rowsN[count($rowsN)-1]['arrayTime']=$arrayBorts[$i]['fromTime'];
							$rowsN[count($rowsN)-1]['arraySpeed']=$arrayBorts[$i]['fromSpeed'];
						}
						else {
							if ($arrayBorts[$i]['fromTime']==$rowsN[count($rowsN)-1]['toTime']) {
								$rowsN[count($rowsN)-1]['arrayTime']=$rowsN[count($rowsN)-1]['arrayTime']."*".$arrayBorts[$i]['fromTime'];
								$rowsN[count($rowsN)-1]['arraySpeed']=$rowsN[count($rowsN)-1]['arraySpeed']."*".$arrayBorts[$i]['fromSpeed'];
								$rowsN[count($rowsN)-1]['toTime']=$arrayBorts[$i]['toTime'];
								$rowsN[count($rowsN)-1]['timeBetween']=$rowsN[count($rowsN)-1]['timeBetween']+$arrayBorts[$i]['timeBetween'];
								$rowsN[count($rowsN)-1]['distance']=$rowsN[count($rowsN)-1]['distance']+$arrayBorts[$i]['distance'];
								$rowsN[count($rowsN)-1]['toSpeed']=$arrayBorts[$i]['toSpeed'];
								$rowsN[count($rowsN)-1]['fromLat1']=$rowsN[count($rowsN)-1]['fromLat1']."*".$arrayBorts[$i]['fromLat1'];
								$rowsN[count($rowsN)-1]['toLat2']=$rowsN[count($rowsN)-1]['toLat2']."*".$arrayBorts[$i]['toLat2'];
								$rowsN[count($rowsN)-1]['fromLong1']=$rowsN[count($rowsN)-1]['fromLong1']."*".$arrayBorts[$i]['fromLong1'];
								$rowsN[count($rowsN)-1]['toLong2']=$rowsN[count($rowsN)-1]['toLong2']."*".$arrayBorts[$i]['toLong2'];
								$rowsN[count($rowsN)-1]['speedCoordinates']=$rowsN[count($rowsN)-1]['speedCoordinates']."*".$arrayBorts[$i]['speedCoordinates'];
								$rowsN[count($rowsN)-1]['speedAverage']=$rowsN[count($rowsN)-1]['speedAverage']."*".$arrayBorts[$i]['speedAverage'];
							}
							else {
								$rowsN[]=$arrayBorts[$i];
								$rowsN[count($rowsN)-1]['arrayTime']=$arrayBorts[$i]['fromTime'];
								$rowsN[count($rowsN)-1]['arraySpeed']=$arrayBorts[$i]['fromSpeed'];
							}
						}
					}
			}
				$numpp=1;
				for ($i=0; $i < count($rowsN); $i++) { 
					if ($rowsN[$i]['timeBetween']>=$duration) {
						$timeBetweenFormat=new Time($rowsN[$i]['timeBetween']);
						$rowsN[$i]['timeBetween']=$timeBetweenFormat->getFormattedTime();
						$rows[]=$rowsN[$i];
						$rows[count($rows)-1]['npp']=$numpp;
						$numpp=$numpp+1;
						$speedExplodeCoordinates=explode("*", $rows[count($rows)-1]['speedCoordinates']);
						$sumAll=array_sum($speedExplodeCoordinates);
						$rows[count($rows)-1]['speedCoordinatesSum']=round($sumAll/(count($speedExplodeCoordinates)),2);
						$speedExplodeAverage=explode("*", $rows[count($rows)-1]['speedAverage']);
						$sumAllAverage=array_sum($speedExplodeAverage);
						$rows[count($rows)-1]['speedAverageSum']=round($sumAllAverage/(count($speedExplodeAverage)),2);
					}
				}
				$countRows=count($rows);
				$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows);
				echo CJSON::encode($result);
		}
///////////////////////
/////$group==3/////////
///////////////////////		
		if ($group==3) {
			$fromDate   = strtotime($fromDate1)+ 5*3600+10*60+0;
			$toDate     = strtotime($toDate1)  +6*3600+12*60+50;
			if ($level==3) {
				$a=Locations::model()->findAll(array(
					'order'=>'unixtime',
					'condition'=> 'unixtime >= :f AND unixtime <= :t AND graphs_id = :gid',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':gid'=>$nodeId)));
			}
			if ($level==2) {
				$a=Locations::model()->findAll(array(
					'order'=>'unixtime',
					'condition'=> 'unixtime >= :f AND unixtime <= :t AND routes_id = :rid',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':rid'=>$nodeId)));
			}
			if ($level==1) {
				$a=Locations::model()->findAll(array(
					'order'=>'unixtime',
					'condition'=> 'unixtime >= :f AND unixtime <= :t',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate)));
			}
			if ($level==0) {
				$a=Locations::model()->findAll(array(
					'order'=>'unixtime',
					'condition'=> 'unixtime >= :f AND unixtime <= :t',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate)));
			}
			foreach ($a as $k) {
				//if ($k->time_difference!=null) {
					$arraySpeedAll[$k->routes_id][$k->graphs_id][$k->borts_id][]=array(
						'fromLat'=>$k->latitude,
						'fromLong'=>$k->longitude,
						'unixtime'=>$k->unixtime,
						'speed'=>$k->speed
					);
					$arraySpeedAll111[date("Y-m-d H:i:s",$k->unixtime)][]=$k->id;
				//}
			}
			foreach ($arraySpeedAll as $rid => $ridArray) {
				foreach ($ridArray as $gid => $gidArray) {
					foreach ($gidArray as $bid => $arraySpeed) {
						for ($i=0; $i <count($arraySpeed)-1 ; $i++) { 
							$timeBetween=$arraySpeed[$i+1]['unixtime']-$arraySpeed[$i]['unixtime'];
							
								$R=6372795;
								$φA=(double) (floor($arraySpeed[$i]['fromLat']/100)*100+(($arraySpeed[$i]['fromLat']   - floor($arraySpeed[$i]['fromLat']/100)*100)*100/60))/100;
								$λA=(double) (floor($arraySpeed[$i]['fromLong']/100)*100+(($arraySpeed[$i]['fromLong']   - floor($arraySpeed[$i]['fromLong']/100)*100)*100/60))/100;
								$φB=(double) (floor($arraySpeed[$i+1]['fromLat']/100)*100+(($arraySpeed[$i+1]['fromLat']   - floor($arraySpeed[$i+1]['fromLat']/100)*100)*100/60))/100;
								$λB=(double) (floor($arraySpeed[$i+1]['fromLong']/100)*100+(($arraySpeed[$i+1]['fromLong']   - floor($arraySpeed[$i+1]['fromLong']/100)*100)*100/60))/100;
								// перевести координаты в радианы
							    $lat1 = $φA * M_PI / 180;
							    $lat2 = $φB * M_PI / 180;
							    $long1 = $λA * M_PI / 180;
							    $long2 = $λB * M_PI / 180;
							    // косинусы и синусы широт и разницы долгот
							    $cl1 = cos($lat1);
							    $cl2 = cos($lat2);
							    $sl1 = sin($lat1);
								$sl2 = sin($lat2);
								$delta = $long2 - $long1;
								$cdelta = cos($delta);
								$sdelta = sin($delta);
							 
								// вычисления длины большого круга
								$y = sqrt(pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2));
								$x = $sl1 * $sl2 + $cl1 * $cl2 * $cdelta;
								 
								//
								$ad = atan2($y, $x);
								$dist = round($ad * $R,0);
								$Distance=$dist;
								
								if ($timeBetween==0) {
									$speed=0;
								}
								if ($timeBetween!=0) {
									$speed=round(($Distance/$timeBetween)*3.6,2);
								}
								$timeBetweenFormat=new Time($timeBetween);
								if ($timeBetween==0) {
									$noteText="Дублювання, 0 секунд між передачами";
								}
								if ($timeBetween!=0) {
									if ($timeBetween>$maxTimeToNoData) {
										$noteText=Yii::app()->session['betweenDataMoreThan30s'];
									}//if ($timeBetween>$maxTimeToNoData) {
									if ($timeBetween<=$maxTimeToNoData) {
										$roundToInt=intval($speed);
										if ($roundToInt>=120) {
											$noteText=Yii::app()->session['SpeedMoreThan120kmPerH'];
										}//if ($roundToInt>120) {
										if ($roundToInt<120) {
											if (($arraySpeed[$i]['speed']==0) || ($arraySpeed[$i+1]['speed']==0)) {
												if ($roundToInt>=80) {
													$noteText=Yii::app()->session['SpeedOneOfSpeedGPSnull'];
												}//if ($speed>=100) {
												if ($roundToInt<80) {
													$noteText=" ";
												}//if ($speed<100) {
											}//if (($arraySpeed[$i]['speed']==0) || ($arraySpeed[$i+1]['speed']==0)) {
											else {
												$noteText=" ";
											}//else
										}//if ($roundToInt<=120) {
									}//if ($timeBetween<=$maxTimeToNoData) {
								}//if ($timeBetween!=0) {
									$rows[]=array(
														'npp'=>count($rows)+1,
														'borts_id'=>$bid,
														'bortNumber'=>$arrayBorts[$bid]['number'],
														'bortStateNumber'=>$arrayBorts[$bid]['state_number'],
														'carriers_name'=>$arrayCarriers[$arrayRoutes[$rid]['carriers_id']],
														'note'=>$noteText,
														'graphs_id'=>$gid,
														'graphs_name'=>$arrayGraphs[$gid]['name'],
														'routes_id'=>$rid,
														'routes_name'=>$arrayRoutes[$rid]['name'],
														'fromTime'=>date("Y-m-d H:i:s",$arraySpeed[$i]['unixtime']),
														'toTime'=>date("Y-m-d H:i:s",$arraySpeed[$i+1]['unixtime']),
														'timeBetween'=>$timeBetweenFormat->getFormattedTime(),
														'distance'=>$Distance/1000,
														'fromSpeed'=>$arraySpeed[$i]['speed'],
														'toSpeed'=>$arraySpeed[$i+1]['speed'],
														'fromLat1'=>$φA,
														'toLat2'=>$φB,
														'fromLong1'=>$λA,
														'toLong2'=>$λB,
														'speedCoordinates'=>$speed,
														'speedAverage'=>round(($arraySpeed[$i]['speed']+$arraySpeed[$i+1]['speed'])/2,2),
														'speedCoordinatesSum'=>$speed,
														'speedAverageSum'=>round(($arraySpeed[$i]['speed']+$arraySpeed[$i+1]['speed'])/2,2)
													);
						}//for ($i=0; $i <count($arraySpeed)-1 ; $i++) {
						unset($arraySpeed);
					}//foreach ($gidArray as $bid => $arraySpeed) {
				}//foreach ($ridArray as $gid => $gidArray) {
			}//foreach ($arraySpeedAll as $rid => $ridArray) {

			$countRows=count($rows);
			for ($i=0; $i < $countRows; $i++) { 

				$er=abs($rows[$i]['speedCoordinatesSum']-$rows[$i]['speedAverageSum']);
				if ($er>=10) {
					$rows[$i]['modabs']=$er;
				}
			}
			$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows);
			echo CJSON::encode($result);
		}
	}
}
?>