<?php
	class SingboardController extends Controller
	{
		public function actionRead()
		{
			$pointSingboard = Yii::app()->request->getParam('pointSingboard');
			$DayType = Yii::app()->request->getParam('typeDay');
			$status='yes';
			//шукаемо активні на сьогодні скедли
			$a=Schedules::model()->findAll(array(
				'condition' => 'status = :st AND schedule_types_id = :dt',
				'params' => array(':st' => $status, ':dt' => $DayType),
				'order' => 'id'));
			foreach ($a as $k) {
				if (isset($k->histories_id)) {
					$arrSched[$k->histories_id]=$k->histories_id;
				}
			}
			foreach ($arrSched as $historiesid => $value) {
				$b=RouteTimeTable::model()->with('routeHistoryAll')->findAll(array(
					'condition' => 'routes_history_id = :rhid AND stations_id = :stid',
					'params' => array(':rhid' => $value, ':stid' => $pointSingboard),
					'order' => 't.routes_history_id,t.time'));
				foreach ($b as $k) {
					$arrayTime[$k->routeHistoryAll->routes_id][]=$k->time;
				}
			}
			
			//route name
			$c=Route::model()->findAll(array(
				'order' => 'name'));
			foreach ($c as $k) {
				$arrRouteName[$k->id]=$k->name;
			}
			foreach ($arrayTime as $key => $value) {
				foreach ($value as $key1 => $value1) {
					$e= new Time($value1);
					$ee=$e->getFormattedTime();
					$eee=explode(":", $ee);
					$h=$eee[0];
					$m=$eee[1];
					$review[$arrRouteName[$key]][$h][]=$m;
				}
				
			}

			//print_r($review);
			foreach ($review as $routeName => $value) {
				foreach ($value as $h => $value1) {
					foreach ($value1 as $npp => $m) {
						$rows1[$routeName][$npp][$h]=$m;

						$rows1[$routeName][$npp]['sched']=$routeName;
					}
				}
			}
			$n=0;
			foreach ($rows1 as $routeName => $value) {
				foreach ($value as $num => $value1) {
					foreach ($value1 as $key => $value2) {
						$rows[$n][$key]=$value2;
						$rows[$n]['npp']=$n+1;
					}
					$n=$n+1;
				}
			}
			
			//print_r($rows);
			function SortByName ($a,$b) {
				if ($a['sched']==$b['sched']) {
					if (gettype($a['sched'])!=gettype($b['sched'])) {
						if ($a['npp']==$b['npp']) {
							return 0;
						}
						if ($a['npp']<$b['npp']) {
							return -1;
						}
						if ($a['npp']>$b['npp']) {
							return 1;
						}
					}
					else {
						if ($a['npp']==$b['npp']) {
							return 0;
						}
						if ($a['npp']<$b['npp']) {
							return -1;
						}
						if ($a['npp']>$b['npp']) {
							return 1;
						}
					}
				}
				if ($a['sched']<$b['sched']) {
					return -1;
				}
				if ($a['sched']>$b['sched']) {
					return 1;
				}
			}
			if (isset($rows)) {
				usort($rows, 'SortByName');
			}
			/*for ($i=0; $i < count($rows)-1; $i++) { 
				if ($rows[$i]['sched']!=$rows[$i+1]['sched']) {
					$rows2[]=$rows[$i];
					$rows2[]=array(
						'sched'=>"M",
						'05'=>5,
						'06'=>6,
						'07'=>7,
						'08'=>8,
						'09'=>9,
						'10'=>10,
						'11'=>11,
						'12'=>12,
						'13'=>13,
						'14'=>14,
						'15'=>15,
						'16'=>16,
						'17'=>17,
						'18'=>18,
						'19'=>19,
						'20'=>20,
						'21'=>21,
						'22'=>22,
						'23'=>23,
						'00'=>0
					);

				}
				else {
					$rows2[]=$rows[$i];
				}
			}*/
			//////////////////////////////////////////
			//шукаемо активні на сьогодні скедли
			$tyty=Graphs::model()->with('route')->findAll();
			foreach ($tyty as $k) {
				if ($k->special_needs=='yes') {
					$arrayGraphsSpecial[]=array(
						'rId'=>$k->routes_id,
						'rN'=>$k->route->name,
						'gId'=>$k->id,
						'gN'=>$k->name);
				};
			}
			if (isset($arrayGraphsSpecial)) {
				foreach ($arrayGraphsSpecial as $key => $value) {
					$a=Schedules::model()->findAll(array(
						'condition' => 'status = :st AND schedule_types_id = :dt AND graphs_id =:gid',
						'params' => array(':st' => $status, ':dt' => $DayType, ':gid'=>$value['gId']),
						'order' => 'id'));
					foreach ($a as $k) {
						if (isset($k->histories_id)) {
							$arrSchedSpecial[$k->histories_id][$value['gN']]=$value['gN'];
						}
					}
				}
			}	
			//print_r($arrSched);
			if (isset($arrayGraphsSpecial)) {
				foreach ($arrSchedSpecial as $key => $value) {
					foreach ($value as $key1 => $value1) {
						$t=RouteTimeTable::model()->with('routeHistoryAll')->findAll(array(
							'condition' => 'routes_history_id = :rhid AND stations_id = :stid AND graphs_number = :gid',
							'params' => array(':rhid' => $key, ':stid' => $pointSingboard, ':gid'=>$value1),
							'order' => 't.Id'));
						foreach ($t as $k) {
							$arrayTimeSpecial[$k->routeHistoryAll->routes_id][]=$k->time;
						}
					}
				}
			}	
			//route name
			$c=Route::model()->findAll(array(
				'order' => 'name'));
			foreach ($c as $k) {
				$arrRouteNameSpecial[$k->id]=$k->name;
			}
			//print_r($arrayTime);
			if (isset($arrayTimeSpecial)) {
				foreach ($arrayTimeSpecial as $key => $value) {
					foreach ($value as $key1 => $value1) {
						$e= new Time($value1);
						$ee=$e->getFormattedTime();
						$eee=explode(":", $ee);
						$h=$eee[0];
						$m=$eee[1];
						$reviewSpecial[$arrRouteNameSpecial[$key]][$h][]=$m;
					}
				}
			}	
			//print_r($reviewSpecial);
			$numberCol=array(
				'05'=>2,
				'06'=>3,
				'07'=>4,
				'08'=>5,
				'09'=>6,
				'10'=>7,
				'11'=>8,
				'12'=>9,
				'13'=>10,
				'14'=>11,
				'15'=>12,
				'16'=>13,
				'17'=>14,
				'18'=>15,
				'19'=>16,
				'20'=>17,
				'21'=>18,
				'22'=>19,
				'23'=>20,
				'00'=>21
			);
			if (isset($reviewSpecial)) {
				foreach ($reviewSpecial as $r => $value) {
					foreach ($value as $h => $value1) {
						for ($i=0; $i <count($value1) ; $i++) { 
							$rowsSpecial[]=array(
								'routeName'=>$r,
								'minutes'=>$value1[$i],
								'colNumber'=>$numberCol[$h]
							);
						}
					}
				}
			}	
			$result = array(
				'success' => true,
				//'rows' => $rows2,
				'rows' => $rows,
				'specialNeeds'=>$rowsSpecial
			);
			echo CJSON::encode($result);
		}//publik read
	}
?>