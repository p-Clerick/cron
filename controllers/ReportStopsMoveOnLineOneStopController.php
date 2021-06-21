<?php
class ReportStopsMoveOnLineOneStopController extends CController {
	public function actionRead() {
		$level         = Yii::app()->request->getParam('level');
		$nodeId        = Yii::app()->request->getParam('recordIdLevel');
		$fromDate      = Yii::app()->request->getParam('fromDate');
		$toDate        = Yii::app()->request->getParam('toDate');
		$stationsId    = Yii::app()->request->getParam('detailsStops');
		$fromDate   = strtotime($fromDate);
		$toDate     = strtotime($toDate) + 23*60*60 + 59*60 + 59;
		$today=date('Y-m-d');
		if ($level==1) {
			if(Yii::app()->user->name != "guest"){
	            $carrier = Yii::app()->user->checkUser(Yii::app()->user);
	        }
	        if ($carrier){
	        	$a=LocationsFlights::model()->with('route')->findAll(array(
						'condition'=> 'stations_id = :stid AND unixtime >= :f AND unixtime <= :t',
						'params'   => array(
									':stid' => $stationsId,
									':f'    =>$fromDate,
									':t'    =>$toDate
						),
						'order'    => 'arrival_plan'
					));
				foreach ($a as $k) {
					if ($k->route->carriers_id==$carrier['carrier_id']) {
						$t  = strftime('%Y-%m-%d %H:%M:%S',$k->unixtime);
						$tt = explode(" ", $t);
						$time= new Time($k->arrival_plan);
						$da=$time->getFormattedTime();
						$rows[]=array(
							'datadata'      =>$tt[0],
							'datatime'      =>$da,
							'timeAverage'   =>$k->time_difference
						);
					}	
				}
	        }else{
	        	$a=LocationsFlights::model()->findAll(array(
						'condition'=> 'stations_id = :stid AND unixtime >= :f AND unixtime <= :t',
						'params'   => array(
									':stid' => $stationsId,
									':f'    =>$fromDate,
									':t'    =>$toDate
						),
						'order'    => 'arrival_plan'
					));
				foreach ($a as $k) {
					$t  = strftime('%Y-%m-%d %H:%M:%S',$k->unixtime);
					$tt = explode(" ", $t);
					$time= new Time($k->arrival_plan);
					$da=$time->getFormattedTime();
					$rows[]=array(
						'datadata'      =>$tt[0],
						'datatime'      =>$da,
						'timeAverage'   =>$k->time_difference
					);
				}
			}	
			foreach ($rows as $key => $value) {
				$categories[$value['datatime']]=$value['datatime'];
				$seriesName[$value['datadata']]=$value['datadata'];
			}
			ksort($seriesName);
			foreach ($seriesName as $key => $value) {
				foreach ($categories as $keyC => $valueC) {
					$dataArr[$value][$valueC]=0;
				}
			}
			foreach ($dataArr as $keyD => $valueD) {
				foreach ($valueD as $keyT => $valueT) {
					foreach ($rows as $keyR => $valueR) {
						if (($valueR['datadata']==$keyD) && ($valueR['datatime']==$keyT)) {
							$dataArr[$keyD][$keyT]=$valueR['timeAverage'];
						}
					}
				}
			}
			//print_r($dataArr);
			foreach ($seriesName as $keyS => $valueS) {
				$AS[]=$keyS;
			}
			foreach ($categories as $keyC => $valueC) {
				$AC[]=$keyC;
			}
			foreach ($seriesName as $keyS => $valueS) {
				$rowsNew[]['seriesName']=$AS;
			}
			$countRowsNew=count($rowsNew);
			for ($i=0; $i <$countRowsNew ; $i++) { 
				$rowsNew[$i]['categories']=$AC;
				$rowsNew[$i]['datadata']=$AS[$i];
				foreach ($dataArr as $keyD => $valueD) {
					foreach ($valueD as $keyT => $valueT) {
						if ($keyD==$rowsNew[$i]['datadata']) {
							$rowsNew[$i]['timeAverage'][]=$valueT;
						}
					}
				}
			}
		}
		if ($level==2) {
			$a=LocationsFlights::model()->findAll(array(
					'condition'=> 'stations_id = :stid AND routes_id = :nid AND unixtime >= :f AND unixtime <= :t',
					'params'   => array(
								':stid' => $stationsId,
								':f'    => $fromDate,
								':t'    => $toDate,
								':nid'  => $nodeId
					),
					'order'    => 'arrival_plan'
				));
			foreach ($a as $k) {
				$t  = strftime('%Y-%m-%d %H:%M:%S',$k->unixtime);
				$tt = explode(" ", $t);
				$time= new Time($k->arrival_plan);
				$da=$time->getFormattedTime();
				$rows[]=array(
					'datadata'      =>$tt[0],
					'datatime'      =>$da,
					'timeAverage'   =>$k->time_difference
				);
			}
			foreach ($rows as $key => $value) {
				$categories[$value['datatime']]=$value['datatime'];
				$seriesName[$value['datadata']]=$value['datadata'];
			}
			ksort($seriesName);
			foreach ($seriesName as $key => $value) {
				foreach ($categories as $keyC => $valueC) {
					$dataArr[$value][$valueC]=0;
				}
			}
			foreach ($dataArr as $keyD => $valueD) {
				foreach ($valueD as $keyT => $valueT) {
					foreach ($rows as $keyR => $valueR) {
						if (($valueR['datadata']==$keyD) && ($valueR['datatime']==$keyT)) {
							$dataArr[$keyD][$keyT]=$valueR['timeAverage'];
						}
					}
				}
			}
			//print_r($dataArr);
			foreach ($seriesName as $keyS => $valueS) {
				$AS[]=$keyS;
			}
			foreach ($categories as $keyC => $valueC) {
				$AC[]=$keyC;
			}
			foreach ($seriesName as $keyS => $valueS) {
				$rowsNew[]['seriesName']=$AS;
			}
			$countRowsNew=count($rowsNew);
			for ($i=0; $i <$countRowsNew ; $i++) { 
				$rowsNew[$i]['categories']=$AC;
				$rowsNew[$i]['datadata']=$AS[$i];
				foreach ($dataArr as $keyD => $valueD) {
					foreach ($valueD as $keyT => $valueT) {
						if ($keyD==$rowsNew[$i]['datadata']) {
							$rowsNew[$i]['timeAverage'][]=$valueT;
						}
					}
				}
			}
		}
		if ($level==3) {
			$a=LocationsFlights::model()->findAll(array(
					'condition'=> 'stations_id = :stid AND graphs_id = :nid AND unixtime >= :f AND unixtime <= :t',
					'params'   => array(
								':stid' => $stationsId,
								':f'    => $fromDate,
								':t'    => $toDate,
								':nid'  => $nodeId
					),
					'order'    => 'arrival_plan'
				));
			foreach ($a as $k) {
				$t  = strftime('%Y-%m-%d %H:%M:%S',$k->unixtime);
				$tt = explode(" ", $t);
				$time= new Time($k->arrival_plan);
				$da=$time->getFormattedTime();
				$rows[]=array(
					'datadata'      =>$tt[0],
					'datatime'      =>$da,
					'timeAverage'   =>$k->time_difference
				);
			}
			foreach ($rows as $key => $value) {
				$categories[$value['datatime']]=$value['datatime'];
				$seriesName[$value['datadata']]=$value['datadata'];
			}
			ksort($seriesName);
			foreach ($seriesName as $key => $value) {
				foreach ($categories as $keyC => $valueC) {
					$dataArr[$value][$valueC]=0;
				}
			}
			foreach ($dataArr as $keyD => $valueD) {
				foreach ($valueD as $keyT => $valueT) {
					foreach ($rows as $keyR => $valueR) {
						if (($valueR['datadata']==$keyD) && ($valueR['datatime']==$keyT)) {
							$dataArr[$keyD][$keyT]=$valueR['timeAverage'];
						}
					}
				}
			}
			//print_r($dataArr);
			foreach ($seriesName as $keyS => $valueS) {
				$AS[]=$keyS;
			}
			foreach ($categories as $keyC => $valueC) {
				$AC[]=$keyC;
			}
			foreach ($seriesName as $keyS => $valueS) {
				$rowsNew[]['seriesName']=$AS;
			}
			$countRowsNew=count($rowsNew);
			for ($i=0; $i <$countRowsNew ; $i++) { 
				$rowsNew[$i]['categories']=$AC;
				$rowsNew[$i]['datadata']=$AS[$i];
				foreach ($dataArr as $keyD => $valueD) {
					foreach ($valueD as $keyT => $valueT) {
						if ($keyD==$rowsNew[$i]['datadata']) {
							$rowsNew[$i]['timeAverage'][]=$valueT;
						}
					}
				}
			}
		}
		$result = array('success'  => true, 'rows' => $rowsNew);
		echo CJSON::encode($result);
	}
}