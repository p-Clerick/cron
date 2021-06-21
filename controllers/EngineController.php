<?php
class EngineController extends Controller {

	public function actionCreate($id)//на посилання з пост
	{
/////////////////////////////вхідні дані///////////////////////////////////////////////////		
		$routeid=Yii::app()->request->getParam('routeid');
		$idHis=Yii::app()->request->getParam('idHis');
		$graphChangeNumber=Yii::app()->request->getParam('graphChangeNumber');
		$newValueChange=Yii::app()->request->getParam('newValueChange');
		$oldValueChange=Yii::app()->request->getParam('oldValueChange');
		$nameColumnChange=Yii::app()->request->getParam('nameColumnChange');
		$durationDinner=Yii::app()->request->getParam('durationDinner');
		$action=Yii::app()->request->getParam('action');
		/*
		1-копіювання розкладу
		2-додавання перезміни         +
		3-delete shiftchange          +		
		4-add dinner                  +
		5-delete dinner               +
		6-profilaktika add            +
		7-delete profilaktika         +
		8-edit yes                    +
		9-deleete one                 +
		10-delete to                  +
		11-delete after без перерах   +
		12-recalc if delete after     +
		13-add flight                 +
		14-add flight with recalc     +
		*/
		
		
		
		
/////////////////////////////////////////////////////////////////////////////////////////
		
	
		if ($newValueChange!=null) {
			//переводимо час в секунди newValue
			$time1=explode(":", $newValueChange);
			$res =  mktime($time1[0], $time1[1],$time1[2]);
			$newTimeChange=$time1[0]*60*60+$time1[1]*60+$time1[2];
		}
		if ($oldValueChange!=null) {
			//переводимо час в секунди oldValue
			$time2=explode(":", $oldValueChange);
			$res =  mktime($time2[0], $time2[1],$time2[2]);
			$oldTimeChange=$time2[0]*60*60+$time2[1]*60+$time2[2];
		}

////////////////////////////////////////////////////////////////////////////////////////

		if ($action==2) //додавання перезміни
		{
			//визначаемо рейс зміни
			$reys = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn AND time =:t',
				'params'    => array(
					':rhid' => $idHis, 
					':gn'=>$graphChangeNumber,
					':t'=>$newTimeChange),
				'order'     => 'Id'));
			foreach ($reys as $r) 
			{
				$flightChange=$r->flights_number;
				$pcnumberChange=$r->number;
			}

			$a = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn',
				'params'    => array(':rhid' => $idHis, ':gn'=>$graphChangeNumber),
				'order'     => 'Id'));
			foreach ($a as $aa) {
				$timeOldTable=$aa->time;
				$pcid=$aa->stations_id;
				$pcsid=$aa->stations_scenario_id;
				$pcnumber=$aa->number;
				$ws=$aa->workshift_start;
				$we=$aa->workshift_end;
				if (isset($ws)) 
				{
					$aa->workshift_start=null;
					$aa->workshift_end=null;
				}
				if (($timeOldTable==$newTimeChange) && ($pcnumber==$pcnumberChange))
				{
					$aa->workshift_start=$newTimeChange;
					$aa->workshift_end=$newTimeChange;
				}
				$aa->save();
			}

			

			$text="add new shiftchange and delete old shiftchange";
			$result = array('success' => true, 'rows' => $text  );
			echo CJSON::encode($result);
		}//додавання перезміни
///////////////////////////////////////////////////////////////////////////////////////////////

		if ($action==3) //delete перезміни
		{
			//визначаемо рейс зміни
			$reys = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn AND time =:t',
				'params'    => array(
					':rhid' => $idHis, 
					':gn'=>$graphChangeNumber,
					':t'=>$newTimeChange),
				'order'     => 'Id'));
			foreach ($reys as $r) 
			{
				$flightChange=$r->flights_number;
				$pcnumberChange=$r->number;
			}

			$a = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn',
				'params'    => array(':rhid' => $idHis, ':gn'=>$graphChangeNumber),
				'order'     => 'Id'));
			foreach ($a as $aa) {
				$timeOldTable=$aa->time;
				$pcid=$aa->stations_id;
				$pcsid=$aa->stations_scenario_id;
				$pcnumber=$aa->number;
				$ws=$aa->workshift_start;
				$we=$aa->workshift_end;
				if (isset($ws)) 
				{
					if (($timeOldTable==$newTimeChange) && ($pcnumber==$pcnumberChange))
					{
						$aa->workshift_start=null;
						$aa->workshift_end=null;
					}
				}
				$aa->save();
			}


			$text="delete shiftchange";
			$result = array('success' => true, 'rows' => $text  );
			echo CJSON::encode($result);
		}//delete перезміни
/////////////////////////////////////////////////////////////////////////////////////////////

		if ($action==4) //add dinner
		{
			//розділяємо обід якщо його значення дробове
			$e=explode(",", $durationDinner);
			$durationDinner=$e[0]+($e[1]/10);
			$durDinInSecond=$durationDinner*60;

			//визначаемо рейс зміни
			$reys = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn AND time =:t',
				'params'    => array(
					':rhid' => $idHis, 
					':gn'=>$graphChangeNumber,
					':t'=>$newTimeChange),
				'order'     => 'Id'));
			foreach ($reys as $r) 
			{
				$flightChange[]=$r->flights_number;
				$pcnumberChange[]=$r->number;
			}

			$a = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn',
				'params'    => array(':rhid' => $idHis, ':gn'=>$graphChangeNumber),
				'order'     => 'Id'));
			foreach ($a as $aa)
			{
				$timeOldTable=$aa->time;
				$flight=$aa->flights_number;
				$pcid=$aa->stations_id;
				$pcsid=$aa->stations_scenario_id;
				$pcnumber=$aa->number;
				$ds=$aa->dinner_start;
				$de=$aa->dinner_end;
				$ws=$aa->workshift_start;
				$we=$aa->workshift_end;
				if ($flight<$flightChange[0]) {
					$timeNewTable=$timeOldTable;
					$dinnerStart=$ds;
					$dinnerEnd=$de;
					$workshiftStart=$ws;
					$workshiftEnd=$we;
				}
				if ($flight==$flightChange[0]) {
					if ($pcnumber<$pcnumberChange[0]) {
						$timeNewTable=$timeOldTable;
						$dinnerStart=$ds;
						$dinnerEnd=$de;
						$workshiftStart=$ws;
						$workshiftEnd=$we;
					}
					if ($pcnumber==$pcnumberChange[0]) {
						$timeNewTable=$timeOldTable;
						$forMinusTime=$timeOldTable;
						$dinnerStart=$timeNewTable;
						$dinnerEnd=$timeNewTable+$durDinInSecond;
						$workshiftStart=$ws;
						$workshiftEnd=$we;
					}
					if ($pcnumber>$pcnumberChange[0]) {
						$sumrizn[]=$timeOldTable-$forMinusTime;
						$timeNewTable=$timeOldTable+$durDinInSecond-$sumrizn[0];
						if (isset($ds)) {
							$dinnerStart=$timeNewTable;
							$dinnerEnd=$timeNewTable+$de-$ds;
						}
						if (!isset($ds)) {
							$dinnerStart=$ds;
							$dinnerEnd=$de;
						}
						if (isset($ws)) {
							$workshiftStart=$timeNewTable;
							$workshiftEnd=$timeNewTable;
						}
						if (!isset($ws)) {
							$workshiftStart=$ws;
							$workshiftEnd=$we;
						}
					}
				}
				if ($flight>$flightChange[0]) {
					$sumrizn[]=$timeOldTable-$forMinusTime;
					$timeNewTable=$timeOldTable+$durDinInSecond-$sumrizn[0];
					if (isset($ds)) {
							$dinnerStart=$timeNewTable;
							$dinnerEnd=$timeNewTable+$de-$ds;
						}
						if (!isset($ds)) {
							$dinnerStart=$ds;
							$dinnerEnd=$de;
						}
						if (isset($ws)) {
							$workshiftStart=$timeNewTable;
							$workshiftEnd=$timeNewTable;
						}
						if (!isset($ws)) {
							$workshiftStart=$ws;
							$workshiftEnd=$we;
						}
				}

				if ($timeNewTable>24*3600) {$timeNewTable=$timeNewTable-24*3600;}
				if ($timeNewTable<0) {$timeNewTable=$timeNewTable+24*3600;}

				if ($dinnerStart>24*3600) {$dinnerStart=$dinnerStart-24*3600;}
				if ($dinnerStart<0) {$dinnerStart=$dinnerStart+24*3600;}

				if ($dinnerEnd>24*3600) {$dinnerEnd=$dinnerEnd-24*3600;}
				if ($dinnerEnd<0) {$dinnerEnd=$dinnerEnd+24*3600;}

				if ($workshiftStart>24*3600) {$workshiftStart=$workshiftStart-24*3600;}
				if ($workshiftStart<0) {$workshiftStart=$workshiftStart+24*3600;}

				if ($workshiftEnd>24*3600) {$workshiftEnd=$workshiftEnd-24*3600;}
				if ($workshiftEnd<0) {$workshiftEnd=$workshiftEnd+24*3600;}

				$aa->time=$timeNewTable;
				$aa->dinner_start=$dinnerStart;
				$aa->dinner_end=$dinnerEnd;
				$aa->workshift_start=$workshiftStart;
				$aa->workshift_end=$workshiftEnd;
				$aa->save();
			}

			$text="add dinner";
			$result = array('success' => true, 'rows' => $text  );
			echo CJSON::encode($result);
		}//add dinner

/////////////////////////////////////////////////////////////////////////////////////////

		if ($action==5) //delete dinner
		{
			//визначаемо рейс зміни
			$reys = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn AND time =:t',
				'params'    => array(
					':rhid' => $idHis, 
					':gn'=>$graphChangeNumber,
					':t'=>$newTimeChange),
				'order'     => 'Id'));
			foreach ($reys as $r) 
			{
				$flightChange[]=$r->flights_number;
				$pcnumberChange[]=$r->number;
			}

			$a = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn',
				'params'    => array(':rhid' => $idHis, ':gn'=>$graphChangeNumber),
				'order'     => 'Id'));
			foreach ($a as $aa)
			{
				$timeOldTable=$aa->time;
				$flight=$aa->flights_number;
				$pcid=$aa->stations_id;
				$pcsid=$aa->stations_scenario_id;
				$pcnumber=$aa->number;
				$ds=$aa->dinner_start;
				$de=$aa->dinner_end;
				$ws=$aa->workshift_start;
				$we=$aa->workshift_end;
				
				if ($flight<$flightChange[0]) {
					$timeNewTable=$timeOldTable;
					$dinnerStart=$ds;
					$dinnerEnd=$de;
					$workshiftStart=$ws;
					$workshiftEnd=$we;
				}
				if ($flight==$flightChange[0]) {
					if ($pcnumber<$pcnumberChange[0]) {
						$timeNewTable=$timeOldTable;
						$dinnerStart=$ds;
						$dinnerEnd=$de;
						$workshiftStart=$ws;
						$workshiftEnd=$we;
					}
					if ($pcnumber==$pcnumberChange[0]) {
						$timeNewTable=$timeOldTable;
						$dinnerStart=null;
						$dinnerEnd=null;
						$oldDurDin=$de-$ds;
						$workshiftStart=$ws;
						$workshiftEnd=$we;
					}
					if ($pcnumber>$pcnumberChange[0]) {
						$timeNewTable=$timeOldTable-$oldDurDin+5*60;
						if (isset($ds)) {
							$dinnerStart=$ds-$oldDurDin+5*60;
							$dinnerEnd=$de-$oldDurDin+5*60;
						}
						if (!isset($ds)) {
							$dinnerStart=$ds;
							$dinnerEnd=$de;
						}
						if (isset($ws)) {
							$workshiftStart=$ws-$oldDurDin+5*60;
							$workshiftEnd=$we-$oldDurDin+5*60;
						}
						if (!isset($ws)) {
							$workshiftStart=$ws;
							$workshiftEnd=$we;
						}
					}
				}	
				if ($flight>$flightChange[0]) {
					$timeNewTable=$timeOldTable-$oldDurDin+5*60;
					if (isset($ds)) {
						$dinnerStart=$ds-$oldDurDin+5*60;
						$dinnerEnd=$de-$oldDurDin+5*60;
					}
					if (!isset($ds)) {
						$dinnerStart=$ds;
						$dinnerEnd=$de;
					}
					if (isset($ws)) {
						$workshiftStart=$ws-$oldDurDin+5*60;
						$workshiftEnd=$we-$oldDurDin+5*60;
					}
					if (!isset($ws)) {
						$workshiftStart=$ws;
						$workshiftEnd=$we;
					}
				}
				

				if ($timeNewTable>24*3600) {$timeNewTable=$timeNewTable-24*3600;}
				if ($timeNewTable<0) {$timeNewTable=$timeNewTable+24*3600;}

				if ($dinnerStart>24*3600) {$dinnerStart=$dinnerStart-24*3600;}
				if ($dinnerStart<0) {$dinnerStart=$dinnerStart+24*3600;}

				if ($dinnerEnd>24*3600) {$dinnerEnd=$dinnerEnd-24*3600;}
				if ($dinnerEnd<0) {$dinnerEnd=$dinnerEnd+24*3600;}

				if ($workshiftStart>24*3600) {$workshiftStart=$workshiftStart-24*3600;}
				if ($workshiftStart<0) {$workshiftStart=$workshiftStart+24*3600;}

				if ($workshiftEnd>24*3600) {$workshiftEnd=$workshiftEnd-24*3600;}
				if ($workshiftEnd<0) {$workshiftEnd=$workshiftEnd+24*3600;}

				$aa->time=$timeNewTable;
				$aa->dinner_start=$dinnerStart;
				$aa->dinner_end=$dinnerEnd;
				$aa->workshift_start=$workshiftStart;
				$aa->workshift_end=$workshiftEnd;
				$aa->save();
			}

			$text="delete dinner";
			$result = array('success' => true, 'rows' => $text  );
			echo CJSON::encode($result);
		}//delete dinner	

////////////////////////////////////////////////////////////////////////////////////

		if ($action==6)//add profilactika
		{
			//визначаемо рейс зміни
			$reys = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn AND time =:t',
				'params'    => array(
					':rhid' => $idHis, 
					':gn'=>$graphChangeNumber,
					':t'=>$newTimeChange),
				'order'     => 'Id'));
			foreach ($reys as $r) 
			{
				$flightChange=$r->flights_number;
				$pcnumberChange=$r->number;
			}
			$a = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn',
				'params'    => array(':rhid' => $idHis, ':gn'=>$graphChangeNumber),
				'order'     => 'Id'));
			foreach ($a as $aa)
			{
				$timeOldTable=$aa->time;
				$flight=$aa->flights_number;
				$pcid=$aa->stations_id;
				$pcsid=$aa->stations_scenario_id;
				$pcnumber=$aa->number;
				if ($flight==$flightChange) {
					if ($pcnumber==$pcnumberChange) {
						$aa->prevention=1;
				        $aa->save();
					}
				}
			} 

			$text="add profilaktika";
			$result = array('success' => true, 'rows' => $text  );
			echo CJSON::encode($result);
		}//add profilactika

////////////////////////////////////////////////////////////////////////////////////


		if ($action==7)//delete profilactika
		{
			//визначаемо рейс зміни
			$reys = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn AND time =:t',
				'params'    => array(
					':rhid' => $idHis, 
					':gn'=>$graphChangeNumber,
					':t'=>$newTimeChange),
				'order'     => 'Id'));
			foreach ($reys as $r) 
			{
				$flightChange=$r->flights_number;
				$pcnumberChange=$r->number;
			}
			$a = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn',
				'params'    => array(':rhid' => $idHis, ':gn'=>$graphChangeNumber),
				'order'     => 'Id'));
			foreach ($a as $aa)
			{
				$timeOldTable=$aa->time;
				$flight=$aa->flights_number;
				$pcid=$aa->stations_id;
				$pcsid=$aa->stations_scenario_id;
				$pcnumber=$aa->number;
				if ($flight==$flightChange) {
					if ($pcnumber==$pcnumberChange) {
						$aa->prevention=null;
				        $aa->save();
					}
				}
			} 
			
			$text="delete profilaktika";
			$result = array('success' => true, 'rows' => $text  );
			echo CJSON::encode($result);
		}//delete profilactika


//////////////////////////////////////////////////////////////////////////////////////


		if ($action==9)//delete one
		{
			$a=RouteTimeTable::model()->findByAttributes(array(
				'routes_history_id'=>$idHis, 
				'graphs_number' => $graphChangeNumber, 
				'time'=>$oldTimeChange));
			$a->delete();

			$text="delete one";
			$result = array('success' => true, 'rows' => $text  );
			echo CJSON::encode($result);
		}//delete one

/////////////////////////////////////////////////////////////////////////////////////////

		if ($action==10)//delete to
		{
			//визначаемо рейс зміни
			$reys = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn AND time =:t',
				'params'    => array(
					':rhid' => $idHis, 
					':gn'=>$graphChangeNumber,
					':t'=>$oldTimeChange),
				'order'     => 'Id'));
			foreach ($reys as $r) 
			{
				$flightChange[]=$r->flights_number;
				$pcnumberChange[]=$r->number;
			}

			$a = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn',
				'params'    => array(':rhid' => $idHis, ':gn'=>$graphChangeNumber),
				'order'     => 'Id'));
			foreach ($a as $aa)
			{
				$timeOldTable=$aa->time;
				$flight=$aa->flights_number;
				$pcid=$aa->stations_id;
				$pcsid=$aa->stations_scenario_id;
				$pcnumber=$aa->number;
				$ds=$aa->dinner_start;
				$de=$aa->dinner_end;
				$ws=$aa->workshift_start;
				$we=$aa->workshift_end;

				if ($flight<$flightChange[0]) {
					$aa->delete();
				}
				if ($flight==$flightChange[0]) {
					if ($pcnumber<$pcnumberChange[0]) {
						$aa->delete();
					}
				}
			}

			$text="delete before";
			$result = array('success' => true, 'rows' => $text  );
			echo CJSON::encode($result);
		}//delete before

/////////////////////////////////////////////////////////////////////////////////////

		if ($action==11)//delete after
		{
			//визначаемо рейс зміни
			$reys = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn AND time =:t',
				'params'    => array(
					':rhid' => $idHis, 
					':gn'=>$graphChangeNumber,
					':t'=>$oldTimeChange),
				'order'     => 'Id'));
			foreach ($reys as $r) 
			{
				$flightChange[]=$r->flights_number;
				$pcnumberChange[]=$r->number;
			}

			$a = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn',
				'params'    => array(':rhid' => $idHis, ':gn'=>$graphChangeNumber),
				'order'     => 'Id'));
			foreach ($a as $aa)
			{
				$timeOldTable=$aa->time;
				$flight=$aa->flights_number;
				$pcid=$aa->stations_id;
				$pcsid=$aa->stations_scenario_id;
				$pcnumber=$aa->number;
				$ds=$aa->dinner_start;
				$de=$aa->dinner_end;
				$ws=$aa->workshift_start;
				$we=$aa->workshift_end;

				if ($flight>$flightChange[0]) {
					$aa->delete();
				}
				if ($flight==$flightChange[0]) {
					if ($pcnumber>$pcnumberChange[0]) {
						$aa->delete();
					}
				}
			}

			$text="delete after without recalc";
			$result = array('success' => true, 'rows' => $text  );
			echo CJSON::encode($result);
		}//delete after

////////////////////////////////////////////////////////////////////////////////////////

		if ($action==12)//recalc if delete after
		{
			$b = RouteHistoryIdAll::model()->findByAttributes(array('Id'=>$idHis));
			$N=$b->amount;
			//визначаемо рейс зміни
			$reys = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn AND time =:t',
				'params'    => array(
					':rhid' => $idHis, 
					':gn'=>$graphChangeNumber,
					':t'=>$oldTimeChange),
				'order'     => 'Id'));
			foreach ($reys as $r) 
			{
				$flightChange[]=$r->flights_number;
				$pcnumberChange[]=$r->number;
			}
			$d = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid',
				'params'    => array(':rhid' => $idHis),
				'order'     => 'Id'));
			foreach ($d as $dd) {
				$t=$dd->time;
				$n=$dd->graphs_number;
				$fl=$dd->flights_number;
				$stationNum=$dd->number;
				$amountSt[]=$dd->number;
				if ($n!=$graphChangeNumber) {
					if (($fl==$flightChange[0]) &&($stationNum>$pcnumberChange[0])){
						$newN[$n]=$n;
					}
					if ($fl>$flightChange[0]) {
						$newN[$n]=$n;
					}
				}
			}
			ksort($newN);
			$chg=0;
			$countNewN=count($newN);
			for ($i=1; $i <=$N ; $i++) { 
				if ($i==$graphChangeNumber) {
					$newN[$i]=$chg;
					$chg++;
				}
			}
			for ($i=1; $i <=$N ; $i++) { 
				if ($newN[$i]!=null) {
					if ($i>$graphChangeNumber) {
						$newN[$i]=$chg;
						$chg++;
					}
				}
			}
			for ($i=1; $i <=$N ; $i++) { 
				if ($newN[$i]!=null) {
					if ($i<$graphChangeNumber) {
						$newN[$i]=$chg;
						$chg++;
					}
				}
			}

			$countAmount=count($amountSt);
			$maxSt=0;
			for ($i=0; $i < $countAmount; $i++) { 
				if ($amountSt[$i]>$maxSt) {
					$maxSt=$amountSt[$i];
				}
			}
			$a = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid',
				'params'    => array(
					':rhid' => $idHis),
				'order'     => 'Id'));
			foreach ($a as $aa) 
			{
				$n=$aa->graphs_number;
				$timeOldTable=$aa->time;
				$flight=$aa->flights_number;
				$pcid=$aa->stations_id;
				$pcsid=$aa->stations_scenario_id;
				$pcnumber=$aa->number;
				$oborot=$aa->duration_flight;
				$ds=$aa->dinner_start;
				$de=$aa->dinner_end;
				$ws=$aa->workshift_start;
				$we=$aa->workshift_end;
				$intervNew=round($oborot/($countNewN-1),0);
				$intervOld=round($oborot/$countNewN,0);
				$rizn=$intervNew-$intervOld;
				if (($timeOldTable<5*3600) && ($flight>4)) {
					$timeOldTable=$timeOldTable+24*3600;
				}
				if ($n==$graphChangeNumber) {
					if ($flight==$flightChange[0]) {
						if ($pcnumber>$pcnumberChange[0]) {
							$aa->delete();
						}
					}
					if ($flight>$flightChange[0]) {
						$aa->delete();
					}
				}
				if ($n!=$graphChangeNumber) {
					if ($timeOldTable>=$oldTimeChange) {
						if ($pcnumber==1) {
							$doing[$n]=1;
						}
						if ($doing[$n]==1) {
							$u=$timeOldTable+$rizn*($newN[$n]-1);
							if ($u>24*3600) {
								$u=$u-24*3600;
							}
							$aa->time=$u;
							if (isset($ds)) {
								$y=$ds+$rizn*($newN[$n]-1);
								$yy=$de+$rizn*($newN[$n]-1);
								if ($y>24*3600) {
									$y=$y-24*3600;
								}
								if ($yy>24*3600) {
									$yy=$yy-24*3600;
								}
								$aa->dinner_start=$y;
								$aa->dinner_end=$yy;
							}
							if (isset($ws)) {
								$p=$ws+$rizn*($newN[$n]-1);
								$pp=$we+$rizn*($newN[$n]-1);
								if ($p>24*3600) {
									$p=$p-24*3600;
								}
								if ($pp>24*3600) {
									$pp=$pp-24*3600;
								}
								$aa->workshift_start=$p;
								$aa->workshift_end=$pp;
							}
						}
					}
				}
				$aa->save();
			}
			$text="delete after with recalc";
			$result = array('success' => true, 'rows' => $text  );
			echo CJSON::encode($result);
		}

////////////////////////////////////////////////////////////////////////////////////////		


		if ($action==8)//редагування + або - по конкретній точці з подальшим перерахунком усього графіка
		{
			if ($nameColumnChange=='depotTo') {
				$differenseTimeDepoTo=$oldTimeChange-$newTimeChange;
				$a=RouteGraphOrderTimeTable::model()->findAll(array(
					'condition' => 'historys_id = :hid AND graphs_number = :gn',
					'params' => array(':hid' => $idHis, ':gn'=>$graphChangeNumber),
					'order' => 'Id'
				));
				foreach ($a as $aa) {
					$oldDepoTo=$aa->depotTo;
					$aa->depotTo=$oldDepoTo+$differenseTimeDepoTo;
					$aa->save();
				}

				$text="edit depotTo";
				$result = array('success' => true, 'rows' => $text  );
				echo CJSON::encode($result);
			}//if ($nameColumnChange=='depotTo')

			else if ($nameColumnChange=='depotAfter') {
				$differenseTimeDepoAfter=$oldTimeChange-$newTimeChange;
				$a=RouteGraphOrderTimeTable::model()->findAll(array(
					'condition' => 'historys_id = :hid AND graphs_number = :gn',
					'params' => array(':hid' => $idHis, ':gn'=>$graphChangeNumber),
					'order' => 'Id'
				));
				foreach ($a as $aa) {
					$oldDepoAfter=$aa->depotAfter;
					$aa->depotAfter=$oldDepoAfter-$differenseTimeDepoAfter;
					$aa->save();
				}
				$text="edit depotAfter";
				$result = array('success' => true, 'rows' => $text  );
				echo CJSON::encode($result);
			}//else if ($nameColumnChange=='depotAfter')

			else if ($nameColumnChange=='presenceTo') {
				$differenseTimeDepoTo=$oldTimeChange-$newTimeChange;
				$a=RouteGraphOrderTimeTable::model()->findAll(array(
					'condition' => 'historys_id = :hid AND graphs_number = :gn ',
					'params' => array(':hid' => $idHis, ':gn'=>$graphChangeNumber),
					'order' => 'Id'
				));
				foreach ($a as $aa) {
					$presenceTo=$aa->attendance;
					$aa->attendance=$presenceTo+$differenseTimeDepoTo;
					$aa->save();
				}

				$text="edit presenceTo";
				$result = array('success' => true, 'rows' => $text  );
				echo CJSON::encode($result);
			}//else if ($nameColumnChange=='presenceTo')

			else if ($oldValueChange==null) {
				$a = RouteTimeTable::model()->findAll(array(
					'condition' => 'routes_history_id = :rhid AND graphs_number = :gn',
					'params'    => array(':rhid' => $idHis, ':gn'=>$graphChangeNumber),
					'order'     => 'Id'));
				foreach ($a as $aa) {
					$timeOldTable=$aa->time;
					$flight=$aa->flights_number;
					$pcid=$aa->stations_id;
					$pcsid=$aa->stations_scenario_id;
					$pcnumber=$aa->number;
					$id=$aa->Id;
					if (($flight>4) && ($timeOldTable<3*3600)) {
						$timeOldTable=$timeOldTable+24*3600;
					}
					$arrTNew[]=array($id,$timeOldTable,$flight,$pcid,$pcsid,$pcnumber);
				}
				$countATN=count($arrTNew);
				$maxPcN=0;
				for ($i=0; $i < $countATN; $i++) { 
					if ($arrTNew[$i][1]<=$newTimeChange) {
						$min=$arrTNew[$i];
					}
					if ($arrTNew[$i][1]>=$newTimeChange) {
						$max[]=$arrTNew[$i];
					}
					if ($arrTNew[$i][5]==$nameColumnChange) {
						$statN=$arrTNew[$i][5];
						$statId=$arrTNew[$i][3];
						$statScen=$arrTNew[$i][4];
					}
					if ($arrTNew[$i][5]>$maxPcN) {
						$maxPcN=$arrTNew[$i][5];
					}
				}
				if (isset($min)) {
					$rizn=$statN-$min[5];
					if ($rizn>0) {
						$f=$min[2];
						$t = new RouteTimeTable;
						$t->routes_history_id=$idHis;
						$t->graphs_number=$graphChangeNumber;
						$t->flights_number=$f;
						$t->time=$newTimeChange;
						$t->stations_id=$statId;
						$t->stations_scenario_id=$statScen;
						$t->number=$statN;
						$t->save();
						$idSave=$t->Id;
						$t->Id=$min[0]+$rizn;
						$t->save();
					}
					if ($rizn<0) {
						$rizn=$maxPcN-$min[5]+$statN;
						$f=$min[2]+1;
						$t = new RouteTimeTable;
						$t->routes_history_id=$idHis;
						$t->graphs_number=$graphChangeNumber;
						$t->flights_number=$f;
						$t->time=$newTimeChange;
						$t->stations_id=$statId;
						$t->stations_scenario_id=$statScen;
						$t->number=$statN;
						$t->save();
						$idSave=$t->Id;
						$t->Id=$min[0]+$rizn;
						$t->save();
					}
					if ($rizn==0) {
						$rizn=$maxPcN;
						$f=$min[2]+1;
						$t = new RouteTimeTable;
						$t->routes_history_id=$idHis;
						$t->graphs_number=$graphChangeNumber;
						$t->flights_number=$f;
						$t->time=$newTimeChange;
						$t->stations_id=$statId;
						$t->stations_scenario_id=$statScen;
						$t->number=$statN;
						$t->save();
						$idSave=$t->Id;
						$t->Id=$min[0]+$rizn;
						$t->save();
					}
				}
				if (!isset($min)) {
					if (isset($max[0])) {
						$rizn=$statN-$max[0][5];
						if ($rizn==0) {
							$rizn=$maxPcN;
							$f=$max[0][2]-1;
							$t = new RouteTimeTable;
							$t->routes_history_id=$idHis;
							$t->graphs_number=$graphChangeNumber;
							$t->flights_number=$f;
							$t->time=$newTimeChange;
							$t->stations_id=$statId;
							$t->stations_scenario_id=$statScen;
							$t->number=$statN;
							$t->save();
							$idSave=$t->Id;
							$t->Id=$max[0][0]-$rizn;
							$t->save();
						}
						if ($rizn>0) {
							$f=$max[0][2]-1;
							$t = new RouteTimeTable;
							$t->routes_history_id=$idHis;
							$t->graphs_number=$graphChangeNumber;
							$t->flights_number=$f;
							$t->time=$newTimeChange;
							$t->stations_id=$statId;
							$t->stations_scenario_id=$statScen;
							$t->number=$statN;
							$t->save();
							$idSave=$t->Id;
							$t->Id=$max[0][0]-$maxPcN+$max[0][5];
							$t->save();
						}
						if ($rizn<0) {
							$f=$max[0][2];
							$t = new RouteTimeTable;
							$t->routes_history_id=$idHis;
							$t->graphs_number=$graphChangeNumber;
							$t->flights_number=$f;
							$t->time=$newTimeChange;
							$t->stations_id=$statId;
							$t->stations_scenario_id=$statScen;
							$t->number=$statN;
							$t->save();
							$idSave=$t->Id;
							$t->Id=$max[0][0]+$rizn;
							$t->save();
						}
					}
				}

				$text="edit if old value = null";
				$result = array('success' => true, 'rows' => $text  );
				echo CJSON::encode($result);
			}//edit if old value = null
			 else 
			{
				$differenseTime=$oldTimeChange-$newTimeChange;
				//визначаемо рейс зміни
				$reys = RouteTimeTable::model()->findAll(array(
					'condition' => 'routes_history_id = :rhid AND graphs_number = :gn AND time =:t',
					'params'    => array(
						':rhid' => $idHis, 
						':gn'=>$graphChangeNumber,
						':t'=>$oldTimeChange),
					'order'     => 'Id'));
				foreach ($reys as $r) 
				{
					$flightChange[]=$r->flights_number;
					$pcnumberChange[]=$r->number;
				}
				$a = RouteTimeTable::model()->findAll(array(
					'condition' => 'routes_history_id = :rhid AND graphs_number = :gn',
					'params'    => array(':rhid' => $idHis, ':gn'=>$graphChangeNumber),
					'order'     => 'Id'));
				foreach ($a as $aa)
				{
					$timeOldTable=$aa->time;
					$flight=$aa->flights_number;
					$pcid=$aa->stations_id;
					$pcsid=$aa->stations_scenario_id;
					$pcnumber=$aa->number;
					$ds=$aa->dinner_start;
					$de=$aa->dinner_end;
					$ws=$aa->workshift_start;
					$we=$aa->workshift_end;
					if ($flight<$flightChange[0]) {
						$timeNewTable=$timeOldTable;
						$dinnerStart=$ds;
						$dinnerEnd=$de;
						$workshiftStart=$ws;
						$workshiftEnd=$we;
					}
					if ($flight==$flightChange[0]) {
						if ($pcnumber<$pcnumberChange[0]) {
							$timeNewTable=$timeOldTable;
							$dinnerStart=$ds;
							$dinnerEnd=$de;
							$workshiftStart=$ws;
							$workshiftEnd=$we;
						}
						if ($pcnumber>=$pcnumberChange[0]) {
							$timeNewTable=$timeOldTable-$differenseTime;
							if (isset($ds)) {
								$dinnerStart=$ds-$differenseTime;
								$dinnerEnd=$de-$differenseTime;
							}
							if (!isset($ds)) {
								$dinnerStart=$ds;
								$dinnerEnd=$de;
							}
							if (isset($ws)) {
								$workshiftStart=$ws-$differenseTime;
								$workshiftEnd=$ws-$differenseTime;
							}
							if (!isset($ws)) {
								$workshiftStart=$ws;
								$workshiftEnd=$we;
							}
						}
					}
					if ($flight>$flightChange[0]) {
						$timeNewTable=$timeOldTable-$differenseTime;
						if (isset($ds)) {
							$dinnerStart=$ds-$differenseTime;
							$dinnerEnd=$de-$differenseTime;
						}
						if (!isset($ds)) {
							$dinnerStart=$ds;
							$dinnerEnd=$de;
						}
						if (isset($ws)) {
							$workshiftStart=$ws-$differenseTime;
							$workshiftEnd=$ws-$differenseTime;
						}
						if (!isset($ws)) {
							$workshiftStart=$ws;
							$workshiftEnd=$we;
						}
					}
					if ($timeNewTable>24*3600) {$timeNewTable=$timeNewTable-24*3600;}
					if ($timeNewTable<0) {$timeNewTable=$timeNewTable+24*3600;}

					if ($dinnerStart>24*3600) {$dinnerStart=$dinnerStart-24*3600;}
					if ($dinnerStart<0) {$dinnerStart=$dinnerStart+24*3600;}

					if ($dinnerEnd>24*3600) {$dinnerEnd=$dinnerEnd-24*3600;}
					if ($dinnerEnd<0) {$dinnerEnd=$dinnerEnd+24*3600;}

					if ($workshiftStart>24*3600) {$workshiftStart=$workshiftStart-24*3600;}
					if ($workshiftStart<0) {$workshiftStart=$workshiftStart+24*3600;}

					if ($workshiftEnd>24*3600) {$workshiftEnd=$workshiftEnd-24*3600;}
					if ($workshiftEnd<0) {$workshiftEnd=$workshiftEnd+24*3600;}

					$aa->time=$timeNewTable;
					$aa->dinner_start=$dinnerStart;
					$aa->dinner_end=$dinnerEnd;
					$aa->workshift_start=$workshiftStart;
					$aa->workshift_end=$workshiftEnd;
					$aa->save();
				}

				$text="edit all";
				$result = array('success' => true, 'rows' => $text  );
				echo CJSON::encode($result);
			}//+ або - по конкретній точці з подальшим перерахунком усього графіка
		}//редагування 
////////////////////////////////////////////////////////////////////////////////////////////


		if ($action==13)//add flight
		{
			$typeSched = RouteHistoryIdAll::model()->findByAttributes(array('Id'=>$idHis));
			$schedTypeIdWork=$typeSched->schedules_type_id;

			//шукаемо перыоди доби що належать маршрутовы за типом робочого дня
			$a = DayIntervalRoute::model()->findAll(array(
				'condition' => 'routes_id = :rid',
				'params'    => array(':rid' => $routeid),
				'order'     => 'id'));
			foreach ($a as $aa) {
				$day[]=$aa->day_interval_city_id;
			}
			//print_r($day);
			$cD=count($day);
			for ($i=0; $i < $cD; $i++) { 
				$b=DayIntervalCity::model()->findByAttributes(array('id'=>$day[$i]));
				$schTypeId=$b->schedules_type_id;
				if ($schTypeId==$schedTypeIdWork) {
					$arrayDayNeed[]=array(
						'id'=>$day[$i],
						'start'=>$b->start_time,
						'end'=>$b->end_time
					);
				}
			}
			//print_r($arrayDayNeed);
			$cdan=count($arrayDayNeed);
			$c = StationsScenario::model()->findAll(array(
				'condition' => 'routes_id = :rid',
				'params'    => array(':rid' => $routeid),
				'order'     => 'number'));
			foreach ($c as $cc) {
				$stationId=$cc->stations_id;
				$stationNumber=$cc->number;
				$arrayStation[]=array(
					'stationId'=>$stationId,
					'stationNumber'=>$stationNumber,
					'stationScenarioId'=>$cc->id
				);
			}

			$countAS=count($arrayStation);
			$arrayStation[]=$arrayStation[0];
			//print_r($arrayStation);
			for ($a=0; $a < $cdan; $a++) { 
				for ($i=0; $i < $countAS; $i++) { 
					$d=DayIntervalStations::model()->findAll(array(
						'condition' => 'day_interval_city_id = :did AND stations_id_from = :from AND stations_id_to =:to',
						'params'    => array(':did' => $arrayDayNeed[$a]['id'], ':from'=>$arrayStation[$i]['stationId'], ':to'=>$arrayStation[$i+1]['stationId']),
						'order'     => 'id'));
					foreach ($d as $dd) {
						$arrayIntervals[$a][$arrayStation[$i]['stationId']]=$dd->interval;
					}
				}
			}
			//print_r($arrayIntervals);
			$d = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn',
				'params'    => array(
					':rhid' => $idHis, 
					':gn'=>$graphChangeNumber),
				'order'     => 'Id'));
			foreach ($d as $dd) {
				$idrec=$dd->Id;
				$flight=$dd->flights_number;
				$time=$dd->time;
				$numberpcOld=$dd->number;
				$oborot=$dd->duration_flight;
			}
			$newtime=$time;
			if ($numberpcOld!=$countAS)//якщо ноиер точки не = останный
			{
				for ($i=0; $i < $cdan; $i++)//Для кожного запису періоду доби
				{
					if (($time>=$arrayDayNeed[$i]['start']) && ($time<$arrayDayNeed[$i]['end'])) {
						for ($a=0; $a < $countAS; $a++)//Для кожної зупинки
						{
							if ($arrayStation[$a]['stationNumber']>$numberpcOld) {
								$newtime=$newtime+$arrayIntervals[$i][$arrayStation[$a-1]['stationId']];
								if ($newtime>24*3600) {
									$newtime=$newtime-24*3600;
								}
								$idrec=$idrec+1;
								$r= new RouteTimeTable;
								$r->Id=$idrec;
								$r->routes_history_id=$idHis;
								$r->graphs_number=$graphChangeNumber;
								$r->flights_number=$flight;
								$r->time=$newtime;
								$r->stations_id=$arrayStation[$a]['stationId'];
								$r->stations_scenario_id=$arrayStation[$a]['stationScenarioId'];
								$r->number=$arrayStation[$a]['stationNumber'];
								$r->duration_flight=$oborot;
								$r->save();
							}
						}
					} 
				}
			}

			if ($numberpcOld==$countAS)//якщо ноиер точки  = останный
			{
				for ($i=0; $i < $cdan; $i++)//Для кожного запису періоду доби
				{
					if (($time>=$arrayDayNeed[$i]['start']) && ($time<$arrayDayNeed[$i]['end'])) {
						for ($a=0; $a < $countAS; $a++)//Для кожної зупинки
						{
							if ($a==0) {
								$newtime=$newtime+$arrayIntervals[$i][$arrayStation[$countAS-1]['stationId']];
								if ($newtime>24*3600) {
									$newtime=$newtime-24*3600;
								}
								$idrec=$idrec+1;
								$r= new RouteTimeTable;
								$r->Id=$idrec;
								$r->routes_history_id=$idHis;
								$r->graphs_number=$graphChangeNumber;
								$r->flights_number=$flight+1;
								$r->time=$newtime;
								$r->stations_id=$arrayStation[$a]['stationId'];
								$r->stations_scenario_id=$arrayStation[$a]['stationScenarioId'];
								$r->number=$arrayStation[$a]['stationNumber'];
								$r->duration_flight=$oborot;
								$r->save();
							}
							else {
								$newtime=$newtime+$arrayIntervals[$i][$arrayStation[$a-1]['stationId']];
								if ($newtime>24*3600) {
									$newtime=$newtime-24*3600;
								}
								$idrec=$idrec+1;
								$r= new RouteTimeTable;
								$r->Id=$idrec;
								$r->routes_history_id=$idHis;
								$r->graphs_number=$graphChangeNumber;
								$r->flights_number=$flight+1;
								$r->time=$newtime;
								$r->stations_id=$arrayStation[$a]['stationId'];
								$r->stations_scenario_id=$arrayStation[$a]['stationScenarioId'];
								$r->number=$arrayStation[$a]['stationNumber'];
								$r->duration_flight=$oborot;
								$r->save();
							}
						}
					} 
				}
			}
			$text="add flight";
			$result = array('success' => true, 'rows' => $text  );
			echo CJSON::encode($result);
		}

//////////////////////////////////////////////////////////////////////////////////////
		if ($action==14)//add flight with recalc 
		{
			$typeSched = RouteHistoryIdAll::model()->findByAttributes(array('Id'=>$idHis));
			$schedTypeIdWork=$typeSched->schedules_type_id;
			$N=$typeSched->amount;

			$a = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn',
				'params'    => array(
					':rhid' => $idHis, 
					':gn'=>$graphChangeNumber),
				'order'     => 'Id'));
			foreach ($a as $aa) {
				$idrec=$aa->Id;
				$flightChange=$aa->flights_number;
				$pcnumberChange=$aa->number;
				$oldTimeChange=$aa->time;
				$oborot=$aa->duration_flight;
			}



			$d = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid',
				'params'    => array(':rhid' => $idHis),
				'order'     => 'Id'));
			foreach ($d as $dd) {
				$t=$dd->time;
				$n=$dd->graphs_number;
				$fl=$dd->flights_number;
				$stationNum=$dd->number;
				$amountSt[]=$dd->number;
				if ($n!=$graphChangeNumber) {
					if (($fl==$flightChange) &&($stationNum>$pcnumberChange)){
						$newN[$n]=$n;
					}
					if ($fl>$flightChange) {
						$newN[$n]=$n;
					}
				}
			}
			ksort($newN);
			$chg=0;
			$countNewN=count($newN);
			for ($i=1; $i <=$N ; $i++) { 
				if ($i==$graphChangeNumber) {
					$newN[$i]=$chg;
					$chg++;
				}
			}
			for ($i=1; $i <=$N ; $i++) { 
				if ($newN[$i]!=null) {
					if ($i>$graphChangeNumber) {
						$newN[$i]=$chg;
						$chg++;
					}
				}
			}
			for ($i=1; $i <=$N ; $i++) { 
				if ($newN[$i]!=null) {
					if ($i<$graphChangeNumber) {
						$newN[$i]=$chg;
						$chg++;
					}
				}
			}

			$countAmount=count($amountSt);
			$maxSt=0;
			for ($i=0; $i < $countAmount; $i++) { 
				if ($amountSt[$i]>$maxSt) {
					$maxSt=$amountSt[$i];
				}
			}

			$a = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid',
				'params'    => array(
					':rhid' => $idHis),
				'order'     => 'Id'));
			foreach ($a as $aa) 
			{
				$n=$aa->graphs_number;
				$timeOldTable=$aa->time;
				$flight=$aa->flights_number;
				$pcid=$aa->stations_id;
				$pcsid=$aa->stations_scenario_id;
				$pcnumber=$aa->number;
				$oborot=$aa->duration_flight;
				$ds=$aa->dinner_start;
				$de=$aa->dinner_end;
				$ws=$aa->workshift_start;
				$we=$aa->workshift_end;
				$intervNew=round($oborot/($countNewN+1),0);
				$intervOld=round($oborot/$countNewN,0);
				$rizn=$intervNew-$intervOld;
				if (($timeOldTable<5*3600) && ($flight>4)) {
					$timeOldTable=$timeOldTable+24*3600;
				}
				if ($n!=$graphChangeNumber) {
					if ($timeOldTable>=$oldTimeChange) {
						if ($pcnumber==1) {
							$doing[$n]=1;
						}
						if ($doing[$n]==1) {
							$u=$timeOldTable+$rizn*($newN[$n]);
							if ($u>24*3600) {
								$u=$u-24*3600;
							}
							$aa->time=$u;
							if (isset($ds)) {
								$y=$ds+$rizn*($newN[$n]);
								$yy=$de+$rizn*($newN[$n]);
								if ($y>24*3600) {
									$y=$y-24*3600;
								}
								if ($yy>24*3600) {
									$yy=$yy-24*3600;
								}
								$aa->dinner_start=$y;
								$aa->dinner_end=$yy;
							}
							if (isset($ws)) {
								$p=$ws+$rizn*($newN[$n]);
								$pp=$we+$rizn*($newN[$n]);
								if ($p>24*3600) {
									$p=$p-24*3600;
								}
								if ($pp>24*3600) {
									$pp=$pp-24*3600;
								}
								$aa->workshift_start=$p;
								$aa->workshift_end=$pp;
							}
						}
					}
				}
				$aa->save();
			}

			//шукаемо перыоди доби що належать маршрутовы за типом робочого дня
			$a = DayIntervalRoute::model()->findAll(array(
				'condition' => 'routes_id = :rid',
				'params'    => array(':rid' => $routeid),
				'order'     => 'id'));
			foreach ($a as $aa) {
				$day[]=$aa->day_interval_city_id;
			}
			//print_r($day);
			$cD=count($day);
			for ($i=0; $i < $cD; $i++) { 
				$b=DayIntervalCity::model()->findByAttributes(array('id'=>$day[$i]));
				$schTypeId=$b->schedules_type_id;
				if ($schTypeId==$schedTypeIdWork) {
					$arrayDayNeed[]=array(
						'id'=>$day[$i],
						'start'=>$b->start_time,
						'end'=>$b->end_time
					);
				}
			}
			//print_r($arrayDayNeed);
			$cdan=count($arrayDayNeed);
			$c = StationsScenario::model()->findAll(array(
				'condition' => 'routes_id = :rid',
				'params'    => array(':rid' => $routeid),
				'order'     => 'number'));
			foreach ($c as $cc) {
				$stationId=$cc->stations_id;
				$stationNumber=$cc->number;
				$arrayStation[]=array(
					'stationId'=>$stationId,
					'stationNumber'=>$stationNumber,
					'stationScenarioId'=>$cc->id
				);
			}

			$countAS=count($arrayStation);
			$arrayStation[]=$arrayStation[0];
			//print_r($arrayStation);
			for ($a=0; $a < $cdan; $a++) { 
				for ($i=0; $i < $countAS; $i++) { 
					$d=DayIntervalStations::model()->findAll(array(
						'condition' => 'day_interval_city_id = :did AND stations_id_from = :from AND stations_id_to =:to',
						'params'    => array(':did' => $arrayDayNeed[$a]['id'], ':from'=>$arrayStation[$i]['stationId'], ':to'=>$arrayStation[$i+1]['stationId']),
						'order'     => 'id'));
					foreach ($d as $dd) {
						$arrayIntervals[$a][$arrayStation[$i]['stationId']]=$dd->interval;
					}
				}
			}
			//print_r($arrayIntervals);
			$d = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rhid AND graphs_number = :gn',
				'params'    => array(
					':rhid' => $idHis, 
					':gn'=>$graphChangeNumber),
				'order'     => 'Id'));
			foreach ($d as $dd) {
				$idrec=$dd->Id;
				$flight=$dd->flights_number;
				$time=$dd->time;
				$numberpcOld=$dd->number;
				$oborot=$dd->duration_flight;
			}
			$newtime=$time;
			if ($numberpcOld!=$countAS)//якщо ноиер точки не = останный
			{
				for ($i=0; $i < $cdan; $i++)//Для кожного запису періоду доби
				{
					if (($time>=$arrayDayNeed[$i]['start']) && ($time<$arrayDayNeed[$i]['end'])) {
						for ($a=0; $a < $countAS; $a++)//Для кожної зупинки
						{
							if ($arrayStation[$a]['stationNumber']>$numberpcOld) {
								$newtime=$newtime+$arrayIntervals[$i][$arrayStation[$a-1]['stationId']];
								if ($newtime>24*3600) {
									$newtime=$newtime-24*3600;
								}
								$idrec=$idrec+1;
								$r= new RouteTimeTable;
								$r->Id=$idrec;
								$r->routes_history_id=$idHis;
								$r->graphs_number=$graphChangeNumber;
								$r->flights_number=$flight;
								$r->time=$newtime;
								$r->stations_id=$arrayStation[$a]['stationId'];
								$r->stations_scenario_id=$arrayStation[$a]['stationScenarioId'];
								$r->number=$arrayStation[$a]['stationNumber'];
								$r->duration_flight=$oborot;
								$r->save();
							}
						}
					} 
				}
			}

			if ($numberpcOld==$countAS)//якщо ноиер точки  = останный
			{
				for ($i=0; $i < $cdan; $i++)//Для кожного запису періоду доби
				{
					if (($time>=$arrayDayNeed[$i]['start']) && ($time<$arrayDayNeed[$i]['end'])) {
						for ($a=0; $a < $countAS; $a++)//Для кожної зупинки
						{
							if ($a==0) {
								$newtime=$newtime+$arrayIntervals[$i][$arrayStation[$countAS-1]['stationId']];
								if ($newtime>24*3600) {
									$newtime=$newtime-24*3600;
								}
								$idrec=$idrec+1;
								$r= new RouteTimeTable;
								$r->Id=$idrec;
								$r->routes_history_id=$idHis;
								$r->graphs_number=$graphChangeNumber;
								$r->flights_number=$flight+1;
								$r->time=$newtime;
								$r->stations_id=$arrayStation[$a]['stationId'];
								$r->stations_scenario_id=$arrayStation[$a]['stationScenarioId'];
								$r->number=$arrayStation[$a]['stationNumber'];
								$r->duration_flight=$oborot;
								$r->save();
							} else {
								$newtime=$newtime+$arrayIntervals[$i][$arrayStation[$a-1]['stationId']];
								if ($newtime>24*3600) {
									$newtime=$newtime-24*3600;
								}
								$idrec=$idrec+1;
								$r= new RouteTimeTable;
								$r->Id=$idrec;
								$r->routes_history_id=$idHis;
								$r->graphs_number=$graphChangeNumber;
								$r->flights_number=$flight+1;
								$r->time=$newtime;
								$r->stations_id=$arrayStation[$a]['stationId'];
								$r->stations_scenario_id=$arrayStation[$a]['stationScenarioId'];
								$r->number=$arrayStation[$a]['stationNumber'];
								$r->duration_flight=$oborot;
								$r->save();
							}
						}
					} 
				}
			}
			
			$text="add flight with recalc";
			$result = array('success' => true, 'rows' => $text  );
			echo CJSON::encode($result);
		}
	}	
}
?>