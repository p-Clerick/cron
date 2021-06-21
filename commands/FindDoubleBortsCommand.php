<?php
Yii::import('application.models.*');
class FindDoubleBortsCommand extends CConsoleCommand
{
	public function run($days) {

	/*$i=RouteTimeTable::model()->findAll(array(
					'condition'=> 'routes_history_id  = :f',
					'params'   =>array(':f'=>871),
					'order'    => 'Id'));
		foreach ($i as $k) {
			if ($k->graphs_number==10) {
				
					$k->flights_number=$k->flights_number-1;
					$k->save();
				
			}
			
		}
			
*/
/*	$y=ReportCachStops::model()->findAll(array(
				'condition'=> 'date = :date',
				'params'   =>array(':date' => '2013-09-12'),
				'order'    => 'id'));
			foreach ($y as $k) {
				echo strlen($k->comment); echo "___";
			}

*/
		


/*

//видалення дублікатів
	 $countDays=count($days);
		for ($i=0; $i < $countDays; $i++) {

			
		$unix11=strtotime($days[$i])+3600;
		$unix21=strtotime($days[$i])+(25*3600);
	
	$loc=LocationsFlight::model()->findAll(array(
					'condition'=> 'unixtime >= :f AND unixtime <= :t',
					'params'   =>array(':f'=>$unix11, ':t'=>$unix21),
					'order'=>'id'));	
	foreach ($loc as $key) {
		$arLoc[$key->graphs_id][$key->flights_number][$key->stations_id][]=$key->id;
	}
	//print_r($arLoc);
	foreach ($arLoc as $g => $ag) {
		foreach ($ag as $f => $af) {
			foreach ($af as $s => $as) {
				$cloc=count($as);
				if ($cloc>1) {
					echo $cloc;
				}

			}
		}
	}
	foreach ($arLoc as $g => $ag) {
		foreach ($ag as $f => $af) {
			foreach ($af as $s => $as) {
				$cloc=count($as);
				if ($cloc>1) {
					LocationsFlight::model()->deleteAll(array(
	    			'condition' => 'id = :d',
					'params' => array(':d' => $as[1])));
				}

			}
		}
	}
}	

*/


/*	$countDays=count($days);
		for ($i=0; $i < $countDays; $i++) { 
			$unix1=strtotime($days[$i])+3600;
			$unix2=strtotime($days[$i])+23*3600+59*60+59+3600;
			$r=LocationsFlight::model()->with('route','graph','stations')->findAll(array(
					'condition'=> 'unixtime >= :f AND unixtime <= :t AND graphs_id = :gid',
					'params'   =>array(':f'=>$unix1, ':t'=>$unix2, ':gid'=>108),
					'order'=>'unixtime'));
			foreach ($r as $k) {
				$array1[$k->flights_number][$k->stations_id]=$k->unixtime;
				$scedId=$k->schedules_id;
			}
		}
		//print_r($array1);
		$w=ScheduleTimes::model()->findAll(array(
					'condition'=> 'schedules_id = :f',
					'params'   =>array(':f'=>$scedId),
					'order'=>'id'));
		foreach ($w as $k) {
			$array2[$k->flights_number][$k->stations_id]=array('number'=>$k->pc_number);
		}
		//print_r($array2);
		foreach ($array2 as $fl2 => $arFl) {
			foreach ($arFl as $statid => $ar1) {
				$array3[]=array(
					'statid'=>$statid,
					'unixtime'=>$array1[$fl2][$statid],
					'fl'=>$fl2,
					'number'=>$ar1['number']
				);
			}
		}
		//print_r($array3);
		for ($i=0; $i <count($array3) ; $i++) { 
			if (!isset($array3[$i]['unixtime'])) {
				$arrayDoNotCach[]=$array3[$i];
			}
		}
		print_r($arrayDoNotCach);






		*/
/*	$w=RouteTimeTable::model()->findAll(array(
					'condition'=> 'routes_history_id  = :f',
					'params'   =>array(':f'=>816),
					'order'    => 'Id'));
		foreach ($w as $k) {
			if ($k->number>=47) {
				
					$k->time=$k->time+60;
					$k->save();
				
			}
			
		}*/

/*
	$stDis=DistanceStations::model()->findAll();
	foreach ($stDis as $k) {
		$arrDist[]=array('from'=>$k->stations_id_from, 'to'=>$k->stations_id_to);
	}
	foreach ($arrDist as $key => $value) {
		foreach ($value as $key1 => $value1) {
			$stFt=Stations::model()->findByAttributes(array('id'=>$value1));
			if ($key1=='from') {
				$arrDist[$key]['fromLong']=$stFt->longitude;
				$arrDist[$key]['fromLat']=$stFt->latitude;
			}
			if ($key1=='to') {
				$arrDist[$key]['toLong']=$stFt->longitude;
				$arrDist[$key]['toLat']=$stFt->latitude;
			}
		}
	}
	//print_r($arrDist);
	foreach ($arrDist as $key => $value) {
		$R=6372795;
		$φA=(double) (floor($value['fromLat']/100)*100+(($value['fromLat']   - floor($value['fromLat']/100)*100)*100/60))/100;
		$λA=(double) (floor($value['fromLong']/100)*100+(($value['fromLong']   - floor($value['fromLong']/100)*100)*100/60))/100;
		$φB=(double) (floor($value['toLat']/100)*100+(($value['toLat']   - floor($value['toLat']/100)*100)*100/60))/100;
		$λB=(double) (floor($value['toLong']/100)*100+(($value['toLong']   - floor($value['toLong']/100)*100)*100/60))/100;
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

	    $stDisInsert=DistanceStations::model()->findByAttributes(array('stations_id_from' => $value['from'],'stations_id_to' => $value['to'] ));
	    $stDisInsert->dist_from_map=$dist;
	    $stDisInsert->save();
	}
	
*/
	
	
////////////////////////////////////////////////////////////////////////////		
			
		$countDays=count($days);
		for ($i=0; $i < $countDays; $i++) { 


			$digest_date = $days[$i];
			$date_diff = date("w",strtotime($digest_date));
			

			$unix1=strtotime($days[$i])+3600;
			$unix2=strtotime($days[$i])+23*3600+59*60+59+3600;
			
			$r=LocationsFlight::model()->with('route','graph')->findAll(array(
					'select'   => 't.routes_id, t.graphs_id, t.schedules_id, t.borts_id, t.arrival_plan',
					'condition'=> 'unixtime >= :f AND unixtime <= :t',
					'params'   =>array(':f'=>$unix1, ':t'=>$unix2)));
			foreach ($r as $k) {
				$arrayBorts[$k->routes_id."(-".$k->route->name."-)"][$k->graphs_id."(-".$k->graph->name."-)"][$k->schedules_id][$k->borts_id]=$k->borts_id;
				$arrayBorts2[$k->routes_id."(-".$k->route->name."-)"][$k->graphs_id."(-".$k->graph->name."-)"][$k->borts_id][$k->schedules_id]=$k->schedules_id;
				$arrayEditOld[$k->routes_id."(-".$k->route->name."-)".$k->graphs_id."(-".$k->graph->name."-)"]=$k->schedules_id;
				if ($k->arrival_plan==0) {
					$arrayEdit['nullPlan'][$k->routes_id."(-".$k->route->name."-)"][$k->graphs_id."(-".$k->graph->name."-)"]=$k->graphs_id;
				}
			}
		}
		
		foreach ($arrayBorts as $routes => $value) {
			foreach ($value as $graphs => $value1) {
				$countschedules=count($value1);
				if ($countschedules!=1) {
					foreach ($value1 as $sc => $value2) {

						$arrayEdit['doubleSchedules'][$routes][$graphs][]=$sc;
					}
					
				}
			}
		}
		foreach ($arrayBorts2 as $routes => $value) {
			foreach ($value as $graphs => $value1) {
				$countschedules=count($value1);
				if ($countschedules!=1) {
					foreach ($value1 as $sc => $value2) {
						$arrayEdit['doubleBorts'][$routes][$graphs][]=$sc;
					}
					
				}
			}
		}

		
			
			
			if ($date_diff==0) {
				$typeDayToday=2;
			}
			elseif ($date_diff==6) {
				$typeDayToday=2;
			}
			else {
				$typeDayToday=1; 
			}
			
		foreach ($arrayEditOld as $key => $value) {
			$t=Schedules::model()->findAll(array(
					'select'   =>'t.schedule_types_id',
					'condition'=> 'id = :tid',
					'params'   =>array(':tid'=>$value)));
			foreach ($t as $key1) {
				if ($key1->schedule_types_id!=$typeDayToday) {
					$arrayEdit['typeDay'][$key]=$value;
				}
			}
		}
		for ($i=0; $i < $countDays; $i++) { 
			$daysTo[$i]=$days[$i]." "."23:59:59";
			$d=MoveOnMap::model()->with('route','graph')->findAll(array(
					'condition'=> 'datatime >= :f AND datatime <= :t',
					'params'   =>array(':f'=>$days[$i], ':t'=>$daysTo[$i])));
			foreach ($d as $k) {
				$arDB[$k->routes_id."(-".$k->route->name."-)".$k->graphs_id."(-".$k->graph->name."-)"][$k->borts_id]=$k->routes_id;
			}
		}
		foreach ($arDB as $key => $value) {
			if (count($value)>=2) {
				foreach ($value as $bid => $rid) {
					$arrayEdit['doubleBortsMoveOnMap'][$key][]=$bid;
				}
				
			}
		}

		print_r($arrayEdit);
		if (!isset($arrayEdit)) {
			echo "0";
		}
		echo "counttypeDay".count($arrayEdit['typeDay']);


    }
}
?>