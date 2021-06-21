<?php
class ListStopsController extends Controller  {
	public function actionRead()//на посилання з гет
	{
		$a=StationsPlayback::model()->with('stations_scenario')->findAll();
	/*	foreach ($a as $k) {
			if ($k->stations_scenario->routes_id!=6) {//100
				if ($k->stations_scenario->routes_id!=44) {//101
					if ($k->stations_scenario->routes_id!=103) {//102
						if ($k->stations_scenario->routes_id!=26) {//20
							if ($k->stations_scenario->routes_id!=102) {//14
								$ar[$k->content_files_id][$k->stations_scenario->stations_id][$k->stations_scenario->points_of_events_id][$k->stations_scenario->routes_id]=1;
							}
						}
					}
				}
			}
		}
		//print_r($ar);
		$b=Stations::model()->findAll();
		foreach ($b as $k) {
			$stN[$k->id]=$k->name;
		}
		$b=PointsOfEvents::model()->findAll();
		foreach ($b as $k) {
			$tkN[$k->id]=$k->name;
		}
		$b=ContentFiles::model()->findAll();
		foreach ($b as $k) {
			$fN[$k->id]=$k->filename;
		}
		$b=Route::model()->findAll();
		foreach ($b as $k) {
			$rN[$k->id]=$k->name;
		}
		foreach ($ar as $f => $value) {
			foreach ($value as $s => $value1) {
				foreach ($value1 as $tk => $value2) {
					$rows[]=array(
						'npp'=>$f,
						'directions'=>$fN[$f],
						'name'=>$s,
						'1'=>$stN[$s],
						'2'=>$tk,
						'3'=>$tkN[$tk]
					);
					foreach ($value2 as $rid => $value3) {
						$rows[count($rows)-1]['4']=$rows[count($rows)-1]['4']." ".$rN[$rid];
					}
				}
			}
		}
	*/	$routeid=Yii::app()->request->getParam('routeid');
		$a=StationsScenario::model()->with('stations','route_directions')->findAll(array(
				'condition'=> 't.routes_id = :rid',
				'params'   =>array(':rid' => $routeid),
				'order'    => 't.number'));
		foreach ($a as $k) {
			$rows[]=array(
				'npp'=>$k->number,
				'name'=>$k->stations->name,
				'directions'=>$k->route_directions->name
			);
		}

		$result = array('success' => true, 'rows' => $rows );
		echo CJSON::encode($result);
	}
}