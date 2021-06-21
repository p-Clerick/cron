<?php
	class ReportCheckCachStopController extends CController
	{
		public function actionRead()//на посилання з гет
		{
			$idRecord=Yii::app()->request->getParam('idRecord');

			$a=ReportCachStops::model()->findByAttributes(array('id'=>$idRecord));
			$stationsId=$a->stations_id;
			$routesId=$a->routes_id;
			$graphsId=$a->graphs_id;
			$bortsId=$a->borts_id;
			$fl=$a->flights_number;
			$arrivalPlan=$a->arrival_plan;
			$scheduleId=$a->amount;
			$date=$a->date;

			$dateNeedUnixtime=strtotime($date)+$arrivalPlan;
			$dateNeedUnixtimeFrom=$dateNeedUnixtime-15*60;
			$dateNeedUnixtimeTo=$dateNeedUnixtime+15*60;

			$b=Yii::app()->db->createCommand("SELECT * from stations_locations where stations_id=".$stationsId." AND borts_id=".$bortsId." AND unixtime>=".$dateNeedUnixtimeFrom." AND unixtime<=".$dateNeedUnixtimeTo." order by unixtime")->queryAll();
			
			if (count($b)==0) {
				$r=ReportCachStops::model()->findByAttributes(array('id'=>$idRecord));
				$r->comment="no stations_locations";
				$r->save();
			}
			if (count($b)==1) {
				foreach ($b as $key => $value) {
					$t= new LocationsFlights;
							$t->routes_id=$routesId;
							$t->graphs_id=$graphsId;
							$t->borts_id=$bortsId;
							$t->schedules_id=$scheduleId;
							$t->stations_id=$stationsId;
							$t->flights_number=$fl;
							$t->unixtime=$value['unixtime'];
							$t->time_difference=round(($dateNeedUnixtime-$value['unixtime'])/60,0);
							$t->arrival_plan=$arrivalPlan;
							$t->save();
							$oi=$t->id;
					$r=ReportCachStops::model()->findByAttributes(array('id'=>$idRecord));
					$r->comment='('.round(($dateNeedUnixtime-$value['unixtime'])/60,0).')'.strftime('%H:%M:%S',$value['unixtime'])." yes stations_locations".$r->comment;
					$r->save();
				}
			}
			$result = array('success' => true, 'rows' => $oi);
			echo CJSON::encode($result);
		}
	}
?>