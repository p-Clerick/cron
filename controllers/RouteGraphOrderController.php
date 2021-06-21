<?php
	class RouteGraphOrderController extends Controller
	{
		public function actionRead($id)//на посилання з гет
		{
			$routeid=Yii::app()->request->getParam('routeid');
			$idHis=Yii::app()->request->getParam('idHis');
			$newValuechange=Yii::app()->request->getParam('newValuechange');
			$oldValuechange=Yii::app()->request->getParam('oldValuechange');
			$exp=Yii::app()->request->getParam('exponent');
			$fieldChange=Yii::app()->request->getParam('fieldchange');
			$graphChange=Yii::app()->request->getParam('grchange');
			$typeView=Yii::app()->request->getParam('typeView');
			$action=Yii::app()->request->getParam('action');

			if ($action==1) {
				$data = array(
					Yii::app()->session['OutingNumber'],                     //0
					Yii::app()->session['CountShiftChange'],              //1
					Yii::app()->session['AttendanceText'],                        //2
					Yii::app()->session['OutingFromDepot'],         //3
					Yii::app()->session['TimeStartOnRoute'],//4
					Yii::app()->session['StopStartMoveOnRoute'],                                    //5
					Yii::app()->session['TimeFinishMoveOnRoute'],                                     //6
					Yii::app()->session['StopFinishMoveOnRoute'],                                 //7
					Yii::app()->session['OutingFromDepot'],                                                 //8
					Yii::app()->session['DurationMoveAttendanceOutingFromDepot'],                        //9
					Yii::app()->session['DurationMoveOutingFromDepotStartMove'],    //10
					Yii::app()->session['DurationMoveStartFinishMove'],//11
					Yii::app()->session['DurationMoveFinishOutingToDepot'],     //12
					Yii::app()->session['StopDinner'],        //13
					Yii::app()->session['TimeStartDinner'],    //14
					Yii::app()->session['TimeFinishDinner'], //15
					Yii::app()->session['DurationDinner'],     //16
					Yii::app()->session['StopDinner'],       //17
					Yii::app()->session['TimeStartDinner'],   //18
					Yii::app()->session['TimeFinishDinner'],//19
					Yii::app()->session['DurationDinner'],    //20
					Yii::app()->session['StopDinner'],         //21
					Yii::app()->session['TimeStartDinner'],     //22
					Yii::app()->session['TimeFinishDinner'],  //23
					Yii::app()->session['DurationDinner'],      //24
					Yii::app()->session['DurationWorkWithDinner'],  //25
					Yii::app()->session['WorkTimeWithoutDinner']//26
				);
				$countdata=count($data);

				$a = RouteHistoryIdAll::model()->findByAttributes(array('Id'=>$idHis));
				$N=$a->amount;

				$b = RouteTimeTable::model()->findAll(array(
					'condition'=> 'routes_history_id = :rhid',
					'params'   => array(':rhid' => $idHis),
					'order'    => 'Id'));
				foreach ($b as $bb)
				{
					$n=$bb->graphs_number;
					$fl=$bb->flights_number;
					$time=$bb->time;
					$stationid=$bb->stations_id;
					$stationScenarioid=$bb->stations_scenario_id;
					$stationNumber=$bb->number;
					$ds=$bb->dinner_start;
					$de=$bb->dinner_end;
					$ws=$bb->workshift_start;
					$arrayTime[]=array(
						'n'=>$n,
						'fl'=>$fl,
						'time'=>$time,
						'stationid'=>$stationid,
						'stationScenarioid'=>$stationScenarioid,
						'$stationNumber'=>$stationNumber,
						'ds'=>$ds,
						'de'=>$de,
						'ws'=>$ws,
						'shiftNumber'=>1
					);
				}
				$countAT=count($arrayTime)-1;

				for ($i=0; $i<$N; $i++)
				{
					foreach ($data as $key => $value)
					{
						$rows[]=array('graphNumber'=>$i+1,'exponent' => $value);
					}
				}

				//вставляємо іменні назви зупинок
				$e = Stations::model()->findAll();
				foreach ($e as $f) {
					$arrName[]=array($f->name,$f->id);
				}
				$cn=count($arrName);
				for ($i=0; $i < $countAT; $i++) {
					for ($a=0; $a <$cn ; $a++) {
						if ($arrayTime[$i]['stationid']==$arrName[$a][1]) {
							$arrayTime[$i]['stationName']=$arrName[$a][0];
						}
					}
				}

				for ($i=0; $i < $countAT; $i++) {
					if (isset($arrayTime[$i]['ws'])) {
						$n=$arrayTime[$i]['n'];
						for ($a=$i+1; $a < $countAT; $a++) { 
							if ($n==$arrayTime[$a]['n']) {
								$arrayTime[$a]['shiftNumber']=2;
							}
						}
					}
				}

				for ($i=0; $i < $countAT; $i++) {
					$g=$countdata*($arrayTime[$i]['n']-1);
                    if (isset($arrayTime[$i-1])) {
                        if ($arrayTime[$i]['n'] != $arrayTime[$i - 1]['n']) {
                            $rows[4 + $g]['first'] = $arrayTime[$i]['time'];
                            $rows[5 + $g]['first'] = $arrayTime[$i]['stationName'];
                        } else {
                            if (isset($arrayTime[$i]['ws'])) {
                                $rows[1 + $g]['amount'] = 2;
                                $rows[4 + $g]['second'] = $arrayTime[$i]['time'];
                                $rows[5 + $g]['second'] = $arrayTime[$i]['stationName'];
                                $rows[6 + $g]['first'] = $arrayTime[$i]['time'];
                                $rows[7 + $g]['first'] = $arrayTime[$i]['stationName'];
                            }
                            if ($arrayTime[$i]['n'] != $arrayTime[$i + 1]['n']) {
                                if ($arrayTime[$i]['shiftNumber'] == 2) {
                                    $rows[6 + $g]['second'] = $arrayTime[$i]['time'];
                                    $rows[7 + $g]['second'] = $arrayTime[$i]['stationName'];
                                }
                                if ($arrayTime[$i]['shiftNumber'] == 1) {
                                    $rows[6 + $g]['first'] = $arrayTime[$i]['time'];
                                    $rows[7 + $g]['first'] = $arrayTime[$i]['stationName'];
                                }
                            }
                        }
                    }
				}

				for ($i=0; $i < $countAT; $i++) {
					if (isset($arrayTime[$i]['ds'])) {
						$k[$arrayTime[$i]['n']][$arrayTime[$i]['shiftNumber']]=$k[$arrayTime[$i]['n']][$arrayTime[$i]['shiftNumber']]+1;
						$arrayTime[$i]['numberdin']=$k[$arrayTime[$i]['n']][$arrayTime[$i]['shiftNumber']];
					}
				}

				for ($i=0; $i < $countAT; $i++) {
					$g=$countdata*($arrayTime[$i]['n']-1);
					if (isset($arrayTime[$i]['ds'])) {
						if ($arrayTime[$i]['numberdin']==1) {
							if ($arrayTime[$i]['shiftNumber']==1) {
								$rows[13+$g]['first']=$arrayTime[$i]['stationName'];
								$rows[14+$g]['first']=$arrayTime[$i]['ds'];
								$rows[15+$g]['first']=$arrayTime[$i]['de'];
								$rows[16+$g]['first']=$arrayTime[$i]['de']-$arrayTime[$i]['ds'];
							}
							if ($arrayTime[$i]['shiftNumber']==2) {
								$rows[13+$g]['second']=$arrayTime[$i]['stationName'];
								$rows[14+$g]['second']=$arrayTime[$i]['ds'];
								$rows[15+$g]['second']=$arrayTime[$i]['de'];
								$rows[16+$g]['second']=$arrayTime[$i]['de']-$arrayTime[$i]['ds'];
							}
						}
						if ($arrayTime[$i]['numberdin']==2) {
							if ($arrayTime[$i]['shiftNumber']==1) {
								$rows[17+$g]['first']=$arrayTime[$i]['stationName'];
								$rows[18+$g]['first']=$arrayTime[$i]['ds'];
								$rows[19+$g]['first']=$arrayTime[$i]['de'];
								$rows[20+$g]['first']=$arrayTime[$i]['de']-$arrayTime[$i]['ds'];
							}
							if ($arrayTime[$i]['shiftNumber']==2) {
								$rows[17+$g]['second']=$arrayTime[$i]['stationName'];
								$rows[18+$g]['second']=$arrayTime[$i]['ds'];
								$rows[19+$g]['second']=$arrayTime[$i]['de'];
								$rows[20+$g]['second']=$arrayTime[$i]['de']-$arrayTime[$i]['ds'];
							}
						}
						if ($arrayTime[$i]['numberdin']==3) {
							if ($arrayTime[$i]['shiftNumber']==1) {
								$rows[21+$g]['first']=$arrayTime[$i]['stationName'];
								$rows[22+$g]['first']=$arrayTime[$i]['ds'];
								$rows[23+$g]['first']=$arrayTime[$i]['de'];
								$rows[24+$g]['first']=$arrayTime[$i]['de']-$arrayTime[$i]['ds'];
							}
							if ($arrayTime[$i]['shiftNumber']==2) {
								$rows[21+$g]['second']=$arrayTime[$i]['stationName'];
								$rows[22+$g]['second']=$arrayTime[$i]['ds'];
								$rows[23+$g]['second']=$arrayTime[$i]['de'];
								$rows[24+$g]['second']=$arrayTime[$i]['de']-$arrayTime[$i]['ds'];
							}
						}
					}
				}

				//шукаемо даны в таблицы ordergraf

				$t= RouteGraphOrderTimeTable::model()->findAll(array(
					'condition' => 'historys_id = :hid',
					'params' => array(':hid' => $idHis),
					'order' => 'Id'));
				foreach ($t as $tt) {
					$arrayOrderData[]=array(
						'n'=>$tt->graphs_number,
						'attendance'=>$tt->attendance,
						'depotTo'=>$tt->depotTo,
						'depotAfter'=>$tt->depotAfter
					);
				}
				if (isset($arrayOrderData)) {
					
				}
				if (!isset($arrayOrderData)) {
					for ($i=0; $i < $N; $i++) { 
						
							
								$t = new RouteGraphOrderTimeTable;
								$t->routes_id=$routeid;
								$t->historys_id=$idHis;
								$t->graphs_number=$i+1;
								$t->attendance=15*60;
								$t->depotTo=15*60;
								$t->depotAfter=15*60;
								$t->typeWork=1;
								$t->save();
							
							
						$arrayOrderData[]=array(
							'n'=>$t->graphs_number,
							'attendance'=>$t->attendance,
							'depotTo'=>$t->depotTo,
							'depotAfter'=>$t->depotAfter
						);
					}
				}

				for ($i=0; $i < $N; $i++) { 
					$rows[9+($countdata*($arrayOrderData[$i]['n']-1))]['amount']=$arrayOrderData[$i]['attendance'];
					$rows[10+($countdata*($arrayOrderData[$i]['n']-1))]['amount']=$arrayOrderData[$i]['depotTo'];
					$rows[12+($countdata*($arrayOrderData[$i]['n']-1))]['amount']=$arrayOrderData[$i]['depotAfter'];
				}
				for ($i=0; $i < $N; $i++) { 
					if ($rows[1+($countdata*($arrayOrderData[$i]['n']-1))]['amount']==2) {
						$rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$rows[6+($countdata*($arrayOrderData[$i]['n']-1))]['first']-$rows[4+($countdata*($arrayOrderData[$i]['n']-1))]['first'];
						$rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$rows[6+($countdata*($arrayOrderData[$i]['n']-1))]['second']-$rows[4+($countdata*($arrayOrderData[$i]['n']-1))]['second'];
						if ($rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['first']<0) {
							$rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['first']+24*3600;
						}
						if ($rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['second']<0) {
							$rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['second']+24*3600;
						}
						$rows[8+($countdata*($arrayOrderData[$i]['n']-1))]['amount']=$rows[6+($countdata*($arrayOrderData[$i]['n']-1))]['second']+$rows[12+($countdata*($arrayOrderData[$i]['n']-1))]['amount'];
					}
					if ($rows[1+($countdata*($arrayOrderData[$i]['n']-1))]['amount']!=2) {
						$rows[1+($countdata*($arrayOrderData[$i]['n']-1))]['amount']=1;
						$rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$rows[6+($countdata*($arrayOrderData[$i]['n']-1))]['first']-$rows[4+($countdata*($arrayOrderData[$i]['n']-1))]['first'];
						if ($rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['first']<0) {
							$rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['first']+24*3600;
						}
						$rows[8+($countdata*($arrayOrderData[$i]['n']-1))]['amount']=$rows[6+($countdata*($arrayOrderData[$i]['n']-1))]['first']+$rows[12+($countdata*($arrayOrderData[$i]['n']-1))]['amount'];
						
					}
					$rows[3+($countdata*($arrayOrderData[$i]['n']-1))]['amount']=$rows[4+($countdata*($arrayOrderData[$i]['n']-1))]['first']-$rows[10+($countdata*($arrayOrderData[$i]['n']-1))]['amount'];
					$rows[2+($countdata*($arrayOrderData[$i]['n']-1))]['amount']=$rows[3+($countdata*($arrayOrderData[$i]['n']-1))]['amount']-$rows[9+($countdata*($arrayOrderData[$i]['n']-1))]['amount'];
					$arrOut[$arrayOrderData[$i]['n']]=$rows[2+($countdata*($arrayOrderData[$i]['n']-1))]['amount'];
				}
				for ($i=0; $i < $N; $i++) {
					if ($rows[1+($countdata*($arrayOrderData[$i]['n']-1))]['amount']==1) {
						$rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$rows[8+($countdata*($arrayOrderData[$i]['n']-1))]['amount']-$rows[2+($countdata*($arrayOrderData[$i]['n']-1))]['amount'];
						if ($rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['first']<0) {
							$rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['first']+24*3600;
						}
						$rows[26+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['first']-$rows[16+($countdata*($arrayOrderData[$i]['n']-1))]['first']-$rows[20+($countdata*($arrayOrderData[$i]['n']-1))]['first']-$rows[24+($countdata*($arrayOrderData[$i]['n']-1))]['first'];
					}
					if ($rows[1+($countdata*($arrayOrderData[$i]['n']-1))]['amount']==2) {
						$rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$rows[6+($countdata*($arrayOrderData[$i]['n']-1))]['first']-$rows[2+($countdata*($arrayOrderData[$i]['n']-1))]['amount'];
						if ($rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['first']<0) {
							$rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['first']+24*3600;
						}
						$rows[26+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['first']-$rows[16+($countdata*($arrayOrderData[$i]['n']-1))]['first']-$rows[20+($countdata*($arrayOrderData[$i]['n']-1))]['first']-$rows[24+($countdata*($arrayOrderData[$i]['n']-1))]['first'];
						$rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$rows[8+($countdata*($arrayOrderData[$i]['n']-1))]['amount']-$rows[4+($countdata*($arrayOrderData[$i]['n']-1))]['second'];
						if ($rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['second']<0) {
							$rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['second']+24*3600;
						}
						$rows[26+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['second']-$rows[16+($countdata*($arrayOrderData[$i]['n']-1))]['second']-$rows[20+($countdata*($arrayOrderData[$i]['n']-1))]['second']-$rows[24+($countdata*($arrayOrderData[$i]['n']-1))]['second'];
					}
				}
//шукаемо номер виходу
				$a = array_flip($arrOut);
				ksort($a);
				foreach ($a as $key => $value) {
					$r=$r+1;
					$newOut[$value]=array($key,$r);
				}
				ksort($newOut);
				for ($i=0; $i < $N; $i++) {
					$rows[0+($countdata*($arrayOrderData[$i]['n']-1))]['amount']=$newOut[$i+1][1];
				}
				
				
				//переводимо у зручний формат
				for ($i=0; $i < $N; $i++) {
					if (isset($rows[2+($countdata*($arrayOrderData[$i]['n']-1))]['amount'])) {
						$t = new Time($rows[2+($countdata*($arrayOrderData[$i]['n']-1))]['amount']);
						$rows[2+($countdata*($arrayOrderData[$i]['n']-1))]['amount']=$t->getFormattedTime();
					}
					if (isset($rows[3+($countdata*($arrayOrderData[$i]['n']-1))]['amount'])) {
						$t = new Time($rows[3+($countdata*($arrayOrderData[$i]['n']-1))]['amount']);
						$rows[3+($countdata*($arrayOrderData[$i]['n']-1))]['amount']=$t->getFormattedTime();
					}
					if (isset($rows[4+($countdata*($arrayOrderData[$i]['n']-1))]['first'])) {
						$t = new Time($rows[4+($countdata*($arrayOrderData[$i]['n']-1))]['first']);
						$rows[4+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$t->getFormattedTime();
					}
					if (isset($rows[4+($countdata*($arrayOrderData[$i]['n']-1))]['second'])) {
						$t = new Time($rows[4+($countdata*($arrayOrderData[$i]['n']-1))]['second']);
						$rows[4+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$t->getFormattedTime();
					}
					if (isset($rows[6+($countdata*($arrayOrderData[$i]['n']-1))]['first'])) {
						$t = new Time($rows[6+($countdata*($arrayOrderData[$i]['n']-1))]['first']);
						$rows[6+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$t->getFormattedTime();
					}
					if (isset($rows[6+($countdata*($arrayOrderData[$i]['n']-1))]['second'])) {
						$t = new Time($rows[6+($countdata*($arrayOrderData[$i]['n']-1))]['second']);
						$rows[6+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$t->getFormattedTime();
					}
					if (isset($rows[8+($countdata*($arrayOrderData[$i]['n']-1))]['amount'])) {
						$t = new Time($rows[8+($countdata*($arrayOrderData[$i]['n']-1))]['amount']);
						$rows[8+($countdata*($arrayOrderData[$i]['n']-1))]['amount']=$t->getFormattedTime();
					}
					if (isset($rows[9+($countdata*($arrayOrderData[$i]['n']-1))]['amount'])) {
						$rows[9+($countdata*($arrayOrderData[$i]['n']-1))]['amount']=round($rows[9+($countdata*($arrayOrderData[$i]['n']-1))]['amount']/60);
						$rows[9+($countdata*($arrayOrderData[$i]['n']-1))]['amount']=$rows[9+($countdata*($arrayOrderData[$i]['n']-1))]['amount']." ".Yii::app()->session['MinutesText']." ";
							
					}
					if (isset($rows[10+($countdata*($arrayOrderData[$i]['n']-1))]['amount'])) {
						$rows[10+($countdata*($arrayOrderData[$i]['n']-1))]['amount']=round($rows[10+($countdata*($arrayOrderData[$i]['n']-1))]['amount']/60);
						$rows[10+($countdata*($arrayOrderData[$i]['n']-1))]['amount']=$rows[10+($countdata*($arrayOrderData[$i]['n']-1))]['amount']." ".Yii::app()->session['MinutesText']." ";
							
					}
					if (isset($rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['first'])) {
						$t = new Time($rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['first']);
						$rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$t->getFormattedTime();
						$time=explode(":", $rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['first']);
						$result =  mktime($time[0], $time[1],$time[2]);
						$rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$time[0]." ".Yii::app()->session['HoursText'] ." ".$time[1]." ".Yii::app()->session['MinutesText']." ".$time[2]." ".Yii::app()->session['SecondText']." ";
							
					}
					if (isset($rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['second'])) {
						$t = new Time($rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['second']);
						$rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$t->getFormattedTime();
						$time=explode(":", $rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['second']);
						$result =  mktime($time[0], $time[1],$time[2]);
						$rows[11+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$time[0]." ".Yii::app()->session['HoursText'] ." ".$time[1]." ".Yii::app()->session['MinutesText']." ".$time[2]." ".Yii::app()->session['SecondText']." ";
							
					}
					if (isset($rows[12+($countdata*($arrayOrderData[$i]['n']-1))]['amount'])) {
						$rows[12+($countdata*($arrayOrderData[$i]['n']-1))]['amount']=round($rows[12+($countdata*($arrayOrderData[$i]['n']-1))]['amount']/60);
						$rows[12+($countdata*($arrayOrderData[$i]['n']-1))]['amount']=$rows[12+($countdata*($arrayOrderData[$i]['n']-1))]['amount']." ".Yii::app()->session['MinutesText']." ";
							
					}
					if (isset($rows[14+($countdata*($arrayOrderData[$i]['n']-1))]['first'])) {
						$t = new Time($rows[14+($countdata*($arrayOrderData[$i]['n']-1))]['first']);
						$rows[14+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$t->getFormattedTime();
					}
					if (isset($rows[14+($countdata*($arrayOrderData[$i]['n']-1))]['second'])) {
						$t = new Time($rows[14+($countdata*($arrayOrderData[$i]['n']-1))]['second']);
						$rows[14+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$t->getFormattedTime();
					}
					if (isset($rows[15+($countdata*($arrayOrderData[$i]['n']-1))]['first'])) {
						$t = new Time($rows[15+($countdata*($arrayOrderData[$i]['n']-1))]['first']);
						$rows[15+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$t->getFormattedTime();
					}
					if (isset($rows[15+($countdata*($arrayOrderData[$i]['n']-1))]['second'])) {
						$t = new Time($rows[15+($countdata*($arrayOrderData[$i]['n']-1))]['second']);
						$rows[15+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$t->getFormattedTime();
					}
					if (isset($rows[16+($countdata*($arrayOrderData[$i]['n']-1))]['first'])) {
						$t = new Time($rows[16+($countdata*($arrayOrderData[$i]['n']-1))]['first']);
						$rows[16+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$t->getFormattedTime();
						$time=explode(":", $rows[16+($countdata*($arrayOrderData[$i]['n']-1))]['first']);
						$result =  mktime($time[0], $time[1],$time[2]);
						$rows[16+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$time[0]." ".Yii::app()->session['HoursText'] ." ".$time[1]." ".Yii::app()->session['MinutesText']." ".$time[2]." ".Yii::app()->session['SecondText']." ";
							
					}
					if (isset($rows[16+($countdata*($arrayOrderData[$i]['n']-1))]['second'])) {
						$t = new Time($rows[16+($countdata*($arrayOrderData[$i]['n']-1))]['second']);
						$rows[16+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$t->getFormattedTime();
						$time=explode(":", $rows[16+($countdata*($arrayOrderData[$i]['n']-1))]['second']);
						$result =  mktime($time[0], $time[1],$time[2]);
						$rows[16+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$time[0]." ".Yii::app()->session['HoursText'] ." ".$time[1]." ".Yii::app()->session['MinutesText']." ".$time[2]." ".Yii::app()->session['SecondText']." ";
							
					}

					if (isset($rows[18+($countdata*($arrayOrderData[$i]['n']-1))]['first'])) {
						$t = new Time($rows[18+($countdata*($arrayOrderData[$i]['n']-1))]['first']);
						$rows[18+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$t->getFormattedTime();
					}
					if (isset($rows[18+($countdata*($arrayOrderData[$i]['n']-1))]['second'])) {
						$t = new Time($rows[18+($countdata*($arrayOrderData[$i]['n']-1))]['second']);
						$rows[18+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$t->getFormattedTime();
					}
					if (isset($rows[19+($countdata*($arrayOrderData[$i]['n']-1))]['first'])) {
						$t = new Time($rows[19+($countdata*($arrayOrderData[$i]['n']-1))]['first']);
						$rows[19+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$t->getFormattedTime();
					}
					if (isset($rows[19+($countdata*($arrayOrderData[$i]['n']-1))]['second'])) {
						$t = new Time($rows[19+($countdata*($arrayOrderData[$i]['n']-1))]['second']);
						$rows[19+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$t->getFormattedTime();
					}
					if (isset($rows[20+($countdata*($arrayOrderData[$i]['n']-1))]['first'])) {
						$t = new Time($rows[20+($countdata*($arrayOrderData[$i]['n']-1))]['first']);
						$rows[20+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$t->getFormattedTime();
						$time=explode(":", $rows[20+($countdata*($arrayOrderData[$i]['n']-1))]['first']);
						$result =  mktime($time[0], $time[1],$time[2]);
						$rows[20+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$time[0]." ".Yii::app()->session['HoursText'] ." ".$time[1]." ".Yii::app()->session['MinutesText']." ".$time[2]." ".Yii::app()->session['SecondText']." ";
							
					}
					if (isset($rows[20+($countdata*($arrayOrderData[$i]['n']-1))]['second'])) {
						$t = new Time($rows[20+($countdata*($arrayOrderData[$i]['n']-1))]['second']);
						$rows[20+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$t->getFormattedTime();
						$time=explode(":", $rows[20+($countdata*($arrayOrderData[$i]['n']-1))]['second']);
						$result =  mktime($time[0], $time[1],$time[2]);
						$rows[20+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$time[0]." ".Yii::app()->session['HoursText'] ." ".$time[1]." ".Yii::app()->session['MinutesText']." ".$time[2]." ".Yii::app()->session['SecondText']." ";
							
					}

					if (isset($rows[22+($countdata*($arrayOrderData[$i]['n']-1))]['first'])) {
						$t = new Time($rows[22+($countdata*($arrayOrderData[$i]['n']-1))]['first']);
						$rows[22+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$t->getFormattedTime();
					}
					if (isset($rows[22+($countdata*($arrayOrderData[$i]['n']-1))]['second'])) {
						$t = new Time($rows[22+($countdata*($arrayOrderData[$i]['n']-1))]['second']);
						$rows[22+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$t->getFormattedTime();
					}
					if (isset($rows[23+($countdata*($arrayOrderData[$i]['n']-1))]['first'])) {
						$t = new Time($rows[23+($countdata*($arrayOrderData[$i]['n']-1))]['first']);
						$rows[23+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$t->getFormattedTime();
					}
					if (isset($rows[23+($countdata*($arrayOrderData[$i]['n']-1))]['second'])) {
						$t = new Time($rows[23+($countdata*($arrayOrderData[$i]['n']-1))]['second']);
						$rows[23+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$t->getFormattedTime();
					}
					if (isset($rows[24+($countdata*($arrayOrderData[$i]['n']-1))]['first'])) {
						$t = new Time($rows[24+($countdata*($arrayOrderData[$i]['n']-1))]['first']);
						$rows[24+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$t->getFormattedTime();
						$time=explode(":", $rows[24+($countdata*($arrayOrderData[$i]['n']-1))]['first']);
						$result =  mktime($time[0], $time[1],$time[2]);
						$rows[24+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$time[0]." ".Yii::app()->session['HoursText'] ." ".$time[1]." ".Yii::app()->session['MinutesText']." ".$time[2]." ".Yii::app()->session['SecondText']." ";
							
					}
					if (isset($rows[24+($countdata*($arrayOrderData[$i]['n']-1))]['second'])) {
						$t = new Time($rows[24+($countdata*($arrayOrderData[$i]['n']-1))]['second']);
						$rows[24+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$t->getFormattedTime();
						$time=explode(":", $rows[24+($countdata*($arrayOrderData[$i]['n']-1))]['second']);
						$result =  mktime($time[0], $time[1],$time[2]);
						$rows[24+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$time[0]." ".Yii::app()->session['HoursText'] ." ".$time[1]." ".Yii::app()->session['MinutesText']." ".$time[2]." ".Yii::app()->session['SecondText']." ";
							
					}
					if (isset($rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['first'])) {
						$t = new Time($rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['first']);
						$rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$t->getFormattedTime();
					}
					if (isset($rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['second'])) {
						$t = new Time($rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['second']);
						$rows[25+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$t->getFormattedTime();
					}
					if (isset($rows[26+($countdata*($arrayOrderData[$i]['n']-1))]['first'])) {
						$t = new Time($rows[26+($countdata*($arrayOrderData[$i]['n']-1))]['first']);
						$rows[26+($countdata*($arrayOrderData[$i]['n']-1))]['first']=$t->getFormattedTime();
					}
					if (isset($rows[26+($countdata*($arrayOrderData[$i]['n']-1))]['second'])) {
						$t = new Time($rows[26+($countdata*($arrayOrderData[$i]['n']-1))]['second']);
						$rows[26+($countdata*($arrayOrderData[$i]['n']-1))]['second']=$t->getFormattedTime();
					}
				}
				$countRows=count($rows);

				for ($i=0; $i < $countRows; $i++) { 
					if ((isset($rows[$i]['amount'])) || (isset($rows[$i]['first'])) || (isset($rows[$i]['second'])) ) {
						$rowsN[]=$rows[$i];
					}
				}
				$countRowN=count($rowsN);
				if ($typeView!=Yii::app()->session['AllText'])
				{
					$newType=explode(" ", $typeView);
					$Type=$newType[0];
					for ($i=0; $i<$countRowN; $i++)
					{
						if ($rowsN[$i]['graphNumber']==$Type)
						{
							$rowsToView[]=$rowsN[$i];
						}
					}
					
				}
				if ($typeView==Yii::app()->session['AllText']) {
					for ($i=0; $i<$countRowN; $i++)
					{
						$rowsToView[]=$rowsN[$i];
					}
				}	

				//print_r($arrayTime);
				$result = array('success' => true, 'rows' => $rowsToView );
				echo CJSON::encode($result);
			}//if ($action==1)

			if ($action==2) {
				$t = explode(" ", $newValuechange);
				$newInSecond=$t[0]*60;

				if ($exp==Yii::app()->session['DurationMoveAttendanceOutingFromDepot'])
				{
					$key="attendance";
				}
				if ($exp==Yii::app()->session['DurationMoveOutingFromDepotStartMove'])
				{
					$key="depotTo";
				}
				if ($exp==Yii::app()->session['DurationMoveFinishOutingToDepot'])
				{
					$key="depotAfter";
				}
				$ktt = RouteGraphOrderTimeTable::model()->findByAttributes(array(
					'historys_id'=>$idHis,
					'graphs_number'=>$graphChange));
				$ktt->$key=$newInSecond;
				$ktt->save();

				$result = array('success' => true);
				echo CJSON::encode($result);
			}
			
		}//actionRead
	}//class
?>