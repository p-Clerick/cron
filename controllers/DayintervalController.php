<?php

class DayintervalController extends CController
{  /*
	public function actionRead($id){
		
		$route = Route::model()->with('dayintervals', 'controlPointScenarios')->findByPk($id);
		$metaData = array(
			'idProperty' => 'id',
			'root' => 'rows',
			'totalProperty' => 'results',
			'successProperty' => 'success',				
		);
		$fields = array(
			array('name' => 'from'),
			array('name' => 'to'),
		);

		// Контрольні точки
		

		if($allControlPointsScenario = $route->controlPointScenarios){
	        foreach($allControlPointsScenario as $ctlPointScenario){ 
	        	$ctrlPointIntervals = $ctlPointScenario->intervals; 
	        	$fields[] = array(
					'name' => $ctlPointScenario->id,
				);
	           
	        }

	        $result = array(
	        	'success' => true,
		        'rows' => array(),
		    );
	       
	    } else {
	       	$result = array(
				'success' => false,
				'msg' => 'Маршрут не має контрольних точок',
			);
	    }	

		if($dayintervals = $route->dayintervals){
		    $temp = array();

		    foreach($dayintervals as $dint){
		        $temp[$dint->id]['id'] = $dint->id;
		        $temp[$dint->id]['from'] = $dint->getStartTime()->getFormattedTime();
		        $temp[$dint->id]['to'] = $dint->getEndTime()->getFormattedTime();
		    }

		    foreach($temp as $item){
	            $result['rows'][] = $item;
	        }

		    if($allControlPointsScenario){
		        foreach($allControlPointsScenario as $ctlPointScenario){ 
		        	$ctrlPointIntervals = $ctlPointScenario->intervals; 		        	
		            foreach($ctrlPointIntervals as $item){
		            	if($item->dayinterval->routeid == $route->id){
			                $temp[$item->dayinterval->id][$ctlPointScenario->id] 
			                    = $item->interval / 60;		             
			            }  
		            }
		        }
		        foreach($temp as $item){
		            $result['rows'][] = $item;
		        }
		    }
		} else {
			$result = array(
	        	'success' => true,
		        'rows' => array()
		    );
		}

		$metaData['fields'] = $fields;
		$result['metaData'] = $metaData;
	    echo CJSON::encode($result);	
	}

	

	public function actionCreate(){
		
		$result = array();
		if( Yii::app()->user->checkAccess('createDayInterval') ){
			$received = json_decode(stripslashes(file_get_contents('php://input')), true);
			$data = $received['rows'];
			$route = Route::model()->findByPk($received['routeid']);
			$dayInt = new DayInterval;		
				
			$startTime = Time::factory($data['from']);
			$endTime = Time::factory($data['to']);

			$dayInt->starttime = $startTime->getTimeInSeconds();
			$dayInt->endtime = $endTime->getTimeInSeconds();
			$dayInt->routeid = $route->id;
			$dayInt->save();

			$controlPointIntervalsData = $data;
			
			unset($controlPointIntervalsData['id'],$controlPointIntervalsData['from']);
			unset($controlPointIntervalsData['to']);			

			foreach($controlPointIntervalsData as $scenarioId => $interval){
				$dayInt->addControlPointInterval($scenarioId, $interval * 60);
			}

			$controlPointIntervals = $dayInt->intervals;			
			$result = array(
				'success' => true, 
				'rows' => array(
					'id' => $dayInt->id,
					'from' => $startTime->getFormattedTime(),
					'to' => $endTime->getFormattedTime(),
				),
			);
			foreach($controlPointIntervals as $item){
				$result['rows'][$item->point_scenario_id] = $item->interval / 60;
			}
		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}	
		echo CJSON::encode($result);
	}

	public function actionUpdate($id){
		$result = array();
		if( Yii::app()->user->checkAccess('updateDayInterval') ){
			$received = json_decode(stripslashes(file_get_contents('php://input')), true);
			$data = $received['rows'];
			$route = Route::model()->findByPk($received['routeid']);
			$dayInt = DayInterval::model()->findByPk($data['id']);
			unset($data['id']);
			$rows = array();
			foreach($data as $pointScenarioId => $interval){
				if($pointScenarioId == 'from' || $pointScenarioId == 'to'){
					// Не змінюємо межі періода доби
					continue;
				} else {
					$rows[] = array(
						$pointScenarioId => $interval,
					);
					ControlPointInterval::model()->updateAll(array(
							'interval' => $interval * 60,	
						),
						'dayintervalid = :dId AND point_scenario_id = :pId', 
						array(
							':dId' => $dayInt->id,
							':pId' => $pointScenarioId,
					));
				}
			}		
			$result = array(
				'success' => true,
				'rows' => $rows,
			);
		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}	
		echo CJSON::encode($result);
	}

	public function actionDelete($id){
		$result = array();
		if( Yii::app()->user->checkAccess('deleteDayInterval') ){
			$dayInt = DayInterval::model()->findByPk($id);
			$pointIntervals = $dayInt->intervals;

			foreach($pointIntervals as $item){
				$item->delete();
			}

			if($dayInt->delete()){
				$result = array(
					'success' => true,
				);
			} else {
				$result = array(
					'success' => false,
					'msg' => 'Невдалось зберегти до бази даних',
				);
			}
		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}	
		echo CJSON::encode($result);
	}
*/
	///////////////////////////////////



	public function actionRead($id){
		$scheduleTypeId=Yii::app()->request->getParam('scheduleTypeId');
		$routeid=$id;
		

		$metaData = array(
			'idProperty' => 'id',
			'root' => 'rows',
			'totalProperty' => 'results',
			'successProperty' => 'success',				
		);
		$fields = array(
			array('name' => 'from'),
			array('name' => 'to'),
			array('name' => 'oborot'),
		);

		//контрольні точки

		$point = PointsControlScenario::model()->findAll(array(
			'condition'=> 'routes_id = :rid',
			'params'   => array(':rid' => $routeid),
			'order'    => 'id'));
		foreach ($point as $p) 
		{
			$pcsid=$p->id;
			$pcnumber=$p->number;
			$pcnameid=$p->points_control_id;
			$fields [] = array('name'=> $pcsid);
			$pointArray[]=array(
				'pcsid'=>$pcsid,
				'pcnumber'=>$pcnumber,
				'pcnameid'=>$pcnameid
			);
		}

		//шукаемо періоди доби
		$dayInt = DayInterval::model()->findAll(array(
			'condition'=> 'routeid = :rid AND schedule_type_id =:sti',
			'params'   => array(':rid' => $routeid, 'sti'=>$scheduleTypeId),
			'order'    => 'id'));
		foreach ($dayInt as $di) 
		{
			$dayIntId=$di->id;
			$dayIntStart=$di->starttime;
			$dayIntEnd=$di->endtime;
			$dayIntArray[]=array(
				'dayIntId'=>$dayIntId,
				'dayIntStart'=>$dayIntStart,
				'dayIntEnd'=>$dayIntEnd
			);
		}
		//шукаемо часи по періодах доби
		$countDayIntArray=count($dayIntArray);
		$countPointArray=count($pointArray);
		for ($i=0; $i<$countDayIntArray; $i++)
		{
			for ($a=0; $a<$countPointArray; $a++)
			{
				$timeD= ControlPointInterval::model()->findAll(array(
					'condition'=> 'dayintervalid = :did AND point_scenario_id =:pcid',
					'params'   => array(':did' => $dayIntArray[$i]['dayIntId'], 'pcid'=>$pointArray[$a]['pcsid']),
					'order'    => 'id'));
				foreach ($timeD as $t)
				{
					$int=$t->interval;
					$ArrayInt[$dayIntArray[$i]['dayIntId']][$pointArray[$a]['pcsid']]=round($int/60, 2);
				}
			}
		}
		for ($i=0; $i<$countDayIntArray; $i++)
		{
			$q= new Time($dayIntArray[$i]['dayIntStart']);
			$w= new Time($dayIntArray[$i]['dayIntEnd']);
			$rows[$i]=array(
				'id'=>$dayIntArray[$i]['dayIntId'],
				'from'=>$q->getFormattedTime(),
				'to'=>$w->getFormattedTime()
			);
			for ($a=0; $a<$countPointArray; $a++)
			{
				$rows[$i][$pointArray[$a]['pcsid']]=$ArrayInt[$dayIntArray[$i]['dayIntId']][$pointArray[$a]['pcsid']];
				$rows[$i]['oborot']=$rows[$i]['oborot']+$ArrayInt[$dayIntArray[$i]['dayIntId']][$pointArray[$a]['pcsid']];
			}
		}
		
		$result = array('success' => true, 'rows' => array(), );
		$metaData['fields'] = $fields;
		$result['metaData'] = $metaData;		
		$result['rows'] = $rows;
		
		echo CJSON::encode($result);
			
	}



	public function actionCreate(){

		foreach ($_POST as $key => $value) 
		{
		 	$data[$key]=$value;
		}
		//print_r($data);
		if (isset($data['addDayInterval']))//додавання періодів доби
		{
			
			$a = DayInterval::model()->findAll(array(
				'condition'=> 'routeid = :rid AND schedule_type_id = :stid',
				'params'   => array(':rid' => $data['routeid'], ':stid' =>$data['scheduleTypeId'] ),
				'order'    => 'id'));
			foreach ($a as $aa) 
			{
				$dayId=$aa->id;
				$dayStart=$aa->starttime;
				$dayEnd=$aa->endtime;
				$dayArray[]=array(
					'id'=>$dayId,
					'starttime'=>$dayStart,
					'endtime'=>$dayEnd
				);	
			}
			$countDay=count($dayArray);

			if ($countDay==0)
			{
				$b = new DayInterval;
				$b->starttime=0;
				$b->endtime=0;
				$b->routeid=$data['routeid'];
				$b->schedule_type_id=$data['scheduleTypeId'];
				$b->save();
				$newDayId=$b->id;
			}
			if ($countDay!=0)
			{
				$b = new DayInterval;
				$b->starttime=$dayArray[$countDay-1]['endtime'];
				$b->endtime=0;
				$b->routeid=$data['routeid'];
				$b->schedule_type_id=$data['scheduleTypeId'];
				$b->save();
				$newDayId=$b->id;
			}
			//шукаемо точки контролю
			$c = PointsControlScenario::model()->findAll(array(
				'condition'=> 'routes_id = :rid',
				'params'   => array(':rid' => $data['routeid']),
				'order'    => 'id'));
			foreach ($c as $cc) 
			{
				$pointScenarioId=$cc->id;
				$pointScenarioNumber=$cc->number;
				$pointNameId=$cc->points_control_id;
				$point[$pointScenarioNumber]=$pointScenarioId;
			}
			ksort($point);
			//вставляємо дані в поінт інтервал
			foreach ($point as $key => $value) 
			{
				$d = new ControlPointInterval;
				$d->point_scenario_id=$value;
				$d->dayintervalid=$newDayId;
				$d->interval=120;
				$d->save();
			}
		}
		
		if (isset($data['deleteDayInterval']))//delete періодів доби 
		{
			$delDay = DayInterval::model()->findByAttributes(array('id'=>$data['deleteDayInterval']));
			$delDay->delete();

			$delInt = ControlPointInterval::model()->findAll(array(
				'condition'=> 'dayintervalid = :did ',
				'params'   => array(':did' => $data['deleteDayInterval']),
				'order'    => 'id'));
			foreach ($delInt as $di) 
			{
				$di->delete();
			}
		}
		if (isset($data['dayintchange']))//change періодів доби 
		{
			if ($data['fieldchange']=='from')
			{
				$time=explode(":", $data['newedittime']);
				$result =  mktime($time[0], $time[1],$time[2]);
				$startTimeInSecond=$time[0]*60*60+$time[1]*60+$time[2];

				$e = DayInterval::model()->findByAttributes(array('id'=>$data['dayintchange']));
				$e->starttime=$startTimeInSecond;
				$e->save();
			}
			if ($data['fieldchange']=='to')
			{
				$time=explode(":", $data['newedittime']);
				$result =  mktime($time[0], $time[1],$time[2]);
				$endTimeInSecond=$time[0]*60*60+$time[1]*60+$time[2];

				$e = DayInterval::model()->findByAttributes(array('id'=>$data['dayintchange']));
				$e->endtime=$endTimeInSecond;
				$e->save();
			}
			else
			{
				$f = ControlPointInterval::model()->findByAttributes(array(
					'dayintervalid'=>$data['dayintchange'],
					'point_scenario_id'=>$data['fieldchange']));
				$f->interval=$data['newedittime']*60;
				$f->save();
			}
		}
	
		$result = array(
				'success' => true
		);
		echo CJSON::encode($result);
	}

}