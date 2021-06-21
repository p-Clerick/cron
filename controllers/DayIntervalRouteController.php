<?php
class DayIntervalRouteController extends Controller {
	public function actionRead() {//на посилання з гет

		$routeid=Yii::app()->request->getParam('routeid');
		$scheduleTypeId=Yii::app()->request->getParam('scheduleTypeId');
		$dayIntId = array();
		$metaData = array(
			'idProperty' => 'id',
			'root' => 'rows',
			'totalProperty' => 'results',
			'successProperty' => 'success',
			);	
		$fields[] = array(
			'name' => 'dayintervalidcity');
		$fields[] = array(
			'name' => 'from');
		$fields[] = array(
			'name' => 'to');
		$fields[] = array(
			'name' => 'oborot');


		$a = DayIntervalRoute::model()->findAll(array(
				'condition'=> 'routes_id = :rid',
				'params'   =>array(':rid' => $routeid),
				'order'    => 'id'));
		foreach ($a as $aa) {
			$dayIntId[]=$aa->day_interval_city_id;
		}
		$countDayAll=count($dayIntId);

		for ($i=0; $i < $countDayAll; $i++) { 
			$a = DayIntervalCity::model()->findAll(array(
				'condition'=> 'id = :id',
				'params'   =>array(':id' => $dayIntId[$i]),
				'order'    => 'id'));
			foreach ($a as $aa) {
				$schType=$aa->schedules_type_id;
				if ($schType==$scheduleTypeId) {
					$arrayDayIntervalRoute[] = array(
						'id'=>$dayIntId[$i],
						'start'=>$aa->start_time,
						'end'=>$aa->end_time
					);
				}
			}
		}
		$countDayRoute=count($arrayDayIntervalRoute);
		for ($i=0; $i < $countDayRoute; $i++) { 
			$start= new Time ($arrayDayIntervalRoute[$i]['start']);
			$end= new Time ($arrayDayIntervalRoute[$i]['end']);
			$rows[]=array(
				'dayintervalidcity'=>$arrayDayIntervalRoute[$i]['id'],
				'from'=>$start->getFormattedTime(),
				'to'=>$end->getFormattedTime()
			);
		}
//шукаемо зупинки маршруту
		$c=StationsScenario::model()->findAll(array(
			'condition'=> 'routes_id = :rid',
			'params'   =>array(':rid' => $routeid),
			'order'    => 'number'));
		foreach ($c as $cc) {
			$arraySR[]=array(
				'id'=>$cc->stations_id,
				'number'=>$cc->number
			);
			$fields[] = array(
				'name' => $cc->stations_id);
		}
		$countSR=count($arraySR);
//шукаемо інтервали між зупинками
		for ($i=0; $i < $countDayRoute; $i++){
            $rows[$i]['oborot'] = 0;
			for ($a=0; $a < $countSR; $a++) { 
				if ($a!=$countSR-1) {
					$d = DayIntervalStations::model()->findByAttributes(array(
						'stations_id_from'=>$arraySR[$a]['id'],
						'stations_id_to'=>$arraySR[$a+1]['id'],
						'day_interval_city_id'=>$arrayDayIntervalRoute[$i]['id']
					));
                    if(!isset($d)){
                        $rows[$i][$arraySR[$a]['id']]=0;
                    }
                    else{
					    $rows[$i][$arraySR[$a]['id']]=round($d->interval/60,2);
                    }
					$rows[$i]['oborot']=$rows[$i]['oborot']+$rows[$i][$arraySR[$a]['id']];
				}
				if ($a==$countSR-1) {
					$d = DayIntervalStations::model()->findByAttributes(array(
						'stations_id_from'=>$arraySR[$a]['id'],
						'stations_id_to'=>$arraySR[0]['id'],
						'day_interval_city_id'=>$arrayDayIntervalRoute[$i]['id']
					));
					if(isset($d)) {
						$rows[$i][
						$arraySR[$a]['id']]=
						round($d->interval/60,2);
						$rows[$i]['oborot']=$rows[$i]['oborot']+$rows[$i][$arraySR[$a]['id']];
					}
				}
			}
		}


		
		$result = array('success' => true, 'rows' => array(), );
		$metaData['fields'] = $fields;
		$result['metaData'] = $metaData;		
		$result['rows'] = $rows;
		echo CJSON::encode($result);

	}//public function actionRead()

	public function actionCreate()//editing
	{
		$routeid=Yii::app()->request->getParam('routeid');
		$scheduleTypeId=Yii::app()->request->getParam('scheduleTypeId');
		$actionDayIntRoute=Yii::app()->request->getParam('actionDayIntRoute');
		$newedittime=Yii::app()->request->getParam('newedittime');
		$oldedittime=Yii::app()->request->getParam('oldedittime');
		$fieldchange=Yii::app()->request->getParam('fieldchange');
		$dayintervalidcity=Yii::app()->request->getParam('dayintervalidcity');
		$newDayIntRoute = array();
		if ($actionDayIntRoute==1) {//if standart dayinterval add
			$a = DayIntervalCity::model()->findAll(array(
				'condition'=> 'schedules_type_id = :stid AND standart = :stand',
				'params'   =>array(':stid' => $scheduleTypeId, ':stand' =>1),
				'order'    => 'id'));
			foreach ($a as $aa) {
				$newDayIntRoute[]=$aa->id;
			}
		
		}
		$countStandartDayInt=count($newDayIntRoute);
		for ($i=0; $i < $countStandartDayInt; $i++) { 
		 	$b = new DayIntervalRoute;
			$b->routes_id=$routeid;
			$b->day_interval_city_id=$newDayIntRoute[$i];
			$b->save();
		}
		if ($actionDayIntRoute==2) {//if non standart dayinterval add
			$a = new DayIntervalCity;
			$a->start_time=0;
			$a->end_time=0;
			$a->schedules_type_id=$scheduleTypeId;
			$a->standart=0;
			$a->save();
			$idNewDayIntRoute=$a->id;
			$b = new DayIntervalRoute;
			$b->routes_id=$routeid;
			$b->day_interval_city_id=$idNewDayIntRoute;
			$b->save();
		}


		if ($actionDayIntRoute==3) {//editing dayinterval
			if ($fieldchange =='from') {
				$time=explode(":", $newedittime);
				$result =  mktime($time[0], $time[1],$time[2]);
				$startTimeInSecond=$time[0]*60*60+$time[1]*60+$time[2];
				$a = DayIntervalCity::model()->findByAttributes(array('id'=>$dayintervalidcity));
				$a->start_time=$startTimeInSecond;
				$a->save();
			}
			if ($fieldchange =='to') {
				$time=explode(":", $newedittime);
				$result =  mktime($time[0], $time[1],$time[2]);
				$startTimeInSecond=$time[0]*60*60+$time[1]*60+$time[2];
				$a = DayIntervalCity::model()->findByAttributes(array('id'=>$dayintervalidcity));
				$a->end_time=$startTimeInSecond;
				$a->save();
			}
			if  (($fieldchange !='from') && ($fieldchange !='to')) {
				$c=StationsScenario::model()->findAll(array(
					'condition'=> 'routes_id = :rid',
					'params'   =>array(':rid' => $routeid),
					'order'    => 'number'));
				foreach ($c as $cc) {
					$arraySR[]=array(
						'id'=>$cc->stations_id,
						'number'=>$cc->number
					);
				}
				$countSR=count($arraySR);
				$arraySR[$countSR]=$arraySR[0];
				for ($i=0; $i <$countSR; $i++) {
					if ($fieldchange==$arraySR[$i]['id']) {
						$nextStationId=$arraySR[$i+1]['id'];
					}
					
				}
				$newInterval=explode('.', $newedittime);
				if (isset($newInterval[1])) {
					$newIntervalInSecond=$newInterval[0]*60+$newInterval[1]*6;
				}
				if (!isset($newInterval[1])) {
					$newIntervalInSecond=$newInterval[0]*60;
				}
				$a = DayIntervalStations::model()->findByAttributes(array(
					'stations_id_from'=>$fieldchange,
					'stations_id_to'=>$nextStationId,
					'day_interval_city_id'=>$dayintervalidcity
				));
				if (isset($a)) {
					$a->interval=$newIntervalInSecond;
					$a->save();
				}
				if (!isset($a)) {
					$q = new DayIntervalStations;
					$q->day_interval_city_id=$dayintervalidcity;
					$q->stations_id_from=$fieldchange;
					$q->stations_id_to=$nextStationId;
					$q->interval=$newIntervalInSecond;
					$q->save();
				}
			}
		}


		if ($actionDayIntRoute==4) {//delete dayinterval
			$a = DayIntervalRoute::model()->findByAttributes(array('routes_id'=>$routeid, 'day_interval_city_id' => $dayintervalidcity));
			$a->delete();
		}

		$result = array('success' => true );
		echo CJSON::encode($result);
	}
    public function actionGetDayIntervalForRoute()
    {
        $routeid=Yii::app()->request->getParam('nodeid');
        $scheduleTypeId = 1;
        /*$arrayRouteDayInterval = DayIntervalRoute::model()->findAll(array(
            'condition'=> 'routes_id = :rid',
            'params'   =>array(':rid' => $route_id),
            'order'    => 'id'));
        foreach ($arrayRouteDayInterval as $elemRouteDayInterval) {
            $elemRouteDayInterval->day_interval_city_id;
        }
        $countDayAll=count($dayIntId);*/

        $a = DayIntervalRoute::model()->findAll(array(
            'condition'=> 'routes_id = :rid',
            'params'   =>array(':rid' => $routeid),
            'order'    => 'id'));
        foreach ($a as $aa) {
            $dayIntId[]=$aa->day_interval_city_id;
        }
        $countDayAll=count($dayIntId);

        for ($i=0; $i < $countDayAll; $i++) {
            $a = DayIntervalCity::model()->findAll(array(
                'condition'=> 'id = :id',
                'params'   =>array(':id' => $dayIntId[$i]),
                'order'    => 'id'));
            foreach ($a as $aa) {
                $schType=$aa->schedules_type_id;
                if ($schType==$scheduleTypeId) {
                    $arrayDayIntervalRoute[] = array(
                        'id'=>$dayIntId[$i],
                        'start'=>$aa->start_time,
                        'end'=>$aa->end_time
                    );
                }
            }
        }
        $countDayRoute=count($arrayDayIntervalRoute);
        for ($i=0; $i < $countDayRoute; $i++) {
            $start= new Time ($arrayDayIntervalRoute[$i]['start']);
            $end= new Time ($arrayDayIntervalRoute[$i]['end']);
            $rows[]=array(
                'dayintervalidcity'=>$arrayDayIntervalRoute[$i]['id'],
                'from'=>$start->getFormattedTime(),
                'to'=>$end->getFormattedTime()
            );
        }
//шукаемо зупинки маршруту
        $c=StationsScenario::model()->findAll(array(
            'condition'=> 'routes_id = :rid',
            'params'   =>array(':rid' => $routeid),
            'order'    => 'number'));
        foreach ($c as $cc) {
            $arraySR[]=array(
                'id'=>$cc->stations_id,
                'number'=>$cc->number
            );
            $fields[] = array(
                'name' => $cc->stations_id);
        }
        $countSR=count($arraySR);
//шукаемо інтервали між зупинками
        for ($i=0; $i < $countDayRoute; $i++){
            $rows[$i]['oborot'] = 0;
            for ($a=0; $a < $countSR; $a++) {
                if ($a!=$countSR-1) {
                    $d = DayIntervalStations::model()->findByAttributes(array(
                        'stations_id_from'=>$arraySR[$a]['id'],
                        'stations_id_to'=>$arraySR[$a+1]['id'],
                        'day_interval_city_id'=>$arrayDayIntervalRoute[$i]['id']
                    ));
                    if(!isset($d)){
                        $rows[$i][$arraySR[$a]['id']]=0;
                    }
                    else{
                        $rows[$i][$arraySR[$a]['id']]=round($d->interval/60,2);
                    }
                    $rows[$i]['oborot']=$rows[$i]['oborot']+$rows[$i][$arraySR[$a]['id']];
                }
                if ($a==$countSR-1) {
                    $d = DayIntervalStations::model()->findByAttributes(array(
                        'stations_id_from'=>$arraySR[$a]['id'],
                        'stations_id_to'=>$arraySR[0]['id'],
                        'day_interval_city_id'=>$arrayDayIntervalRoute[$i]['id']
                    ));
                    $rows[$i][$arraySR[$a]['id']]=round($d->interval/60,2);
                    $rows[$i]['oborot']=$rows[$i]['oborot']+$rows[$i][$arraySR[$a]['id']];
                }
            }
            $arrayDayIntervalRouteResult[] = array(
                'day_period_id' => $rows[$i]['dayintervalidcity'],
                'day_period_name' => $rows[$i]['oborot'].' - '.$rows[$i]['from'].' - '.$rows[$i]['to']
            );
        }
        $arrayResult = array('success' => true, 'data' => array(), );
        $arrayResult['data'] = $arrayDayIntervalRouteResult;
        echo CJSON::encode($arrayResult);
    }
}
?>