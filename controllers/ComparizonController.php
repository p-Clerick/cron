<?php
class ComparizonController extends CController {
	public function actionRead() {
		$historyIdComparizon[0]=879;  //11  route name+++++++++++++++++++
		//$historyIdComparizon[1]=263;  //10  route name +++++++++++++++++++
		
	/*	$historyIdComparizon[0]=420;   //1   route name+++++++++++++++++++
		$historyIdComparizon[1]=402;   //3   route name+++++++++++++++++++
		$historyIdComparizon[2]=430;   //8   route name+++++++++++++++++++
		$historyIdComparizon[3]=405;   //9   route name+++++++++++++++++++
		$historyIdComparizon[4]=431;   //10  route name+++++++++++++++++++
		$historyIdComparizon[5]=432;   //11  route name+++++++++++++++++++
		$historyIdComparizon[6]=435;   //17a route name+++++++++++++++++++
		$historyIdComparizon[7]=437;   //18  route name+++++++++++++++++++
		$historyIdComparizon[8]=439;   //23  route name+++++++++++++++++++
		$historyIdComparizon[9]=440;   //26  route name+++++++++++++++++++
		$historyIdComparizon[10]=441;  //26a route name+++++++++++++++++++
		$historyIdComparizon[11]=449;  //27  route name+++++++++++++++++++
		$historyIdComparizon[12]=454;  //27a route name+++++++++++++++++++
		$historyIdComparizon[13]=458;  //31  route name+++++++++++++++++++
		$historyIdComparizon[14]=460;  //32  route name+++++++++++++++++++
		$historyIdComparizon[15]=456;  //47  route name+++++++++++++++++++


		$historyIdComparizon[16]=497;  //2   route name+++++++++++++++++++
		$historyIdComparizon[17]=496;  //4   route name+++++++++++++++++++
		$historyIdComparizon[18]=461;  //9a  route name+++++++++++++++++++
		$historyIdComparizon[19]=488;  //12  route name+++++++++++++++++++
		$historyIdComparizon[20]=483;  //15  route name+++++++++++++++++++
		$historyIdComparizon[21]=482;  //15a route name+++++++++++++++++++
		$historyIdComparizon[22]=486;  //16  route name+++++++++++++++++++
		$historyIdComparizon[23]=484;  //17  route name+++++++++++++++++++
		$historyIdComparizon[24]=487;  //19  route name+++++++++++++++++++
		$historyIdComparizon[25]=489;  //20  route name+++++++++++++++++++
		$historyIdComparizon[26]=495;  //22  route name+++++++++++++++++++
		$historyIdComparizon[27]=492;  //24  route name+++++++++++++++++++
		$historyIdComparizon[28]=480;  //25  route name+++++++++++++++++++
		$historyIdComparizon[29]=490;  //28  route name+++++++++++++++++++
		$historyIdComparizon[30]=479;  //29  route name+++++++++++++++++++
		$historyIdComparizon[31]=477;  //30  route name+++++++++++++++++++
*/
		$countComparizonHistory=count($historyIdComparizon);
		
	/*	$stationsArray[0]=28;         //центральний ринок відправлення
		$stationsArray[1]=181;       //Міжколгоспбуд відправлення
		$stationsArray[2]=72;         //КРЗ відправлення
		$stationsArray[0]=53;         //Надрічна відправлення
		$stationsArray[1]=72;         //КРЗ відправлення
		$stationsArray[2]=86;         //Жд вокзал відправлення
		$stationsArray[3]=101;        //Ас 1 до ТТ відправлення
		$stationsArray[4]=243;        //Госпіталь відправлення
		$stationsArray[5]=250;        //Гараджа відправлення
		$stationsArray[6]=115;        //Полонківська 4 відправлення
		$stationsArray[8]=138;        //Липини відправлення
		$stationsArray[9]=234;        //Райлікарня відправлення
		$stationsArray[10]=220;       //Яна відправлення
		$stationsArray[11]=288;       //рованці відправлення
		$stationsArray[12]=128;       //шруставеллі відправлення
		$stationsArray[13]=133;       //інститут розв відправлення
		$stationsArray[14]=168;       //єршова відправлення
		$stationsArray[15]=201;       //новий ринок відправлення
		$stationsArray[16]=9;         //слон відправлення
		$stationsArray[17]=158;       //Ас 2 відправлення
		$stationsArray[18]=56;        //водоканал відправлення
		$stationsArray[19]=261;       //ГПолонка відправлення
		$stationsArray[20]=129;       //дрнародів відправлення
		$stationsArray[21]=263;       //Милуші відправлення
		$stationsArray[22]=153;       //16 школа відправлення
		$stationsArray[23]=250;       //Гараджа відправлення
		$stationsArray[24]=181;       //Міжколгоспбуд відправлення
		$stationsArray[25]=191;       //жд переїзд вишків відправлення
		$stationsArray[26]=282;       //Підгайці відправлення
		$stationsArray[27]=284;       //Боголюби відправлення
		$stationsArray[28]=120;       //скф відправлення
		*/
		foreach ($historyIdComparizon as $key => $value) {
			
			/*	$a=RouteTimeTable::model()->with('stationName','routeHistoryAll')->findAll(array(
					'condition'=> 'routes_history_id = :rhid AND stations_id = :st',
					'params'   => array(':rhid' => $value, ':st' => 28),
					'order'    => 't.id'));
				foreach ($a as $k) {
					$rows[]=array(
							'routeName'=>$k->routeHistoryAll->routes_id,
							'stationName'=>$k->stationName->name,
							'arrivalTime'=>$k->time,
							'graphName'=>$k->graphs_number
						);
				}*/
			/*	$a=RouteTimeTable::model()->with('stationName','routeHistoryAll')->findAll(array(
					'condition'=> 'routes_history_id = :rhid AND stations_id = :st',
					'params'   => array(':rhid' => $value, ':st' => 73),
					'order'    => 't.id'));
				foreach ($a as $k) {
					$rows[]=array(
							'routeName'=>$k->routeHistoryAll->routes_id,
							'stationName'=>$k->stationName->name,
							'arrivalTime'=>$k->time,
							'graphName'=>$k->graphs_number
						);
				}
				$a=RouteTimeTable::model()->with('stationName','routeHistoryAll')->findAll(array(
					'condition'=> 'routes_history_id = :rhid AND stations_id = :st',
					'params'   => array(':rhid' => $value, ':st' => 158),
					'order'    => 't.id'));
				foreach ($a as $k) {
					$rows[]=array(
							'routeName'=>$k->routeHistoryAll->routes_id,
							'stationName'=>$k->stationName->name,
							'arrivalTime'=>$k->time,
							'graphName'=>$k->graphs_number
						);
				}*/
			$a=RouteTimeTable::model()->with('stationName','routeHistoryAll')->findAll(array(
					'condition'=> 'routes_history_id = :rhid',
					'params'   => array(':rhid' => $value),
					'order'    => 't.id'));
				foreach ($a as $k) {
					$rows[]=array(
							'routeName'=>$k->routeHistoryAll->routes_id,
							'stationName'=>$k->stationName->name,
							'arrivalTime'=>$k->time,
							'graphName'=>$k->graphs_number
						);
				}
		}

		//name routes
		$r=Route::model()->findAll();
		foreach ($r as $k) {
			$rn[$k->id]=$k->name;
		}
		function mysortst ($a,$b) {
				if ($a['stationName']==$b['stationName']) {
					if ($a['arrivalTime']==$b['arrivalTime']) {
						return 0;
					}
					if ($a['arrivalTime']>$b['arrivalTime']) {
						return 1;
					}
					if ($a['arrivalTime']<$b['arrivalTime']) {
						return -1;
					}
				}
				if ($a['stationName']<$b['stationName']) {
					return -1;
				}
				if ($a['stationName']>$b['stationName']) {
					return 1;
				}
			}
			if (isset($rows)) {
				usort($rows, "mysortst");
			}

		$countRows=count($rows);
		for ($i=0; $i < $countRows; $i++) { 
			if ($rows[$i]['stationName']==$rows[$i+1]['stationName']) {
				$rows[$i]['timeDifferenceBuses']=round(($rows[$i+1]['arrivalTime']-$rows[$i]['arrivalTime'])/60,1);
				$t=new Time($rows[$i]['arrivalTime']);
				$rows[$i]['arrivalTime']=$t->getFormattedTime();
			}
			if ($rows[$i]['stationName']!=$rows[$i+1]['stationName']) {
				$t=new Time($rows[$i]['arrivalTime']);
				$rows[$i]['arrivalTime']=$t->getFormattedTime();

			}
			$rows[$i]['routeName']=$rn[$rows[$i]['routeName']];
			$rows[$i]['npp']=$i+1;
		}
		
		$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows); 
		echo CJSON::encode($result);
	}
}