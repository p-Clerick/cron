<?php
class EngineViewTableController extends Controller  {

	public function actionRead() //на посилання з гет
	{
		$idHis=Yii::app()->request->getParam('idHis');
		$head=Yii::app()->request->getParam('head');
		$routeid=Yii::app()->request->getParam('routeid');

		//сворюємо мметадані
		$metaData = array(
			'idProperty' => 'id',
			'root' => 'rows',
			'totalProperty' => 'results',
			'successProperty' => 'success',
			);	
		$fields[] = array(
			'name' => 'graphNumber',
			'type' => 'int');
		$fields[] = array(
			'name' => 'outputNumber',
			'type' => 'int');
		$fields[] = array(
			'name' => 'shiftType');
		$fields[] = array(
			'name' => 'id');
		$fields[] = array(
			'name' => 'presenceTo');
		$fields[] = array(
			'name' => 'depotTo');
		$fields[] = array(
			'name' => 'depotAfter');
		$fields[] = array(
			'name' => 'shiftOneTime');
		$fields[] = array(
			'name' => 'flightFullOne');
		$fields[] = array(
			'name' => 'flightNullOne');
		$fields[] = array(
			'name' => 'flightEndOne');
		$fields[] = array(
			'name' => 'shiftTwoTime');
		$fields[] = array(
			'name' => 'flightFullTwo');
		$fields[] = array(
			'name' => 'flightNullTwo');
		$fields[] = array(
			'name' => 'flightEndTwo');

		//шукаемо назви колонок
		$allHead = explode(",", $head);
		$countHead=count($allHead);

		//встановлюемо назви колонок fields 		
		for ($a=0; $a < $countHead; $a++) {
			for ($i=1; $i < 31; $i++) {
				$fields[] = array(
					'name' => $allHead[$a].$i
				);
			}
		}
		$shiftNumber=1;

		//шукаемо усі часи
		$b = RouteTimeTable::model()->findAll(array(
			'condition' => 'routes_history_id = :hid',
			'params' => array(':hid' => $idHis),
			'order' => 'Id'
		));
		foreach ($b as $bb)
		{
			$n=$bb->graphs_number;
			$fl=$bb->flights_number;
			$time=$bb->time;
			$stationsId=$bb->stations_id;
			$stationNumber=$bb->number;
			$ds=$bb->dinner_start;
			$de=$bb->dinner_end;
			$ws=$bb->workshift_start;
			$we=$bb->workshift_end;
			$oborot=$bb->duration_flight;
			$prevention=$bb->prevention;
			$columnNumber=$fl;
			if ($N<$n) {$shiftNumber=1;}
			$arrayTime[]=array(
				'n'=>$n,
				'fl'=>$fl,
				'time'=>$time,
				'stationsId'=>$stationsId,
				'idScen'=>$idScen,
				'stationNumber'=>$stationNumber,
				'ds'=>$ds,
				'de'=>$de,
				'ws'=>$ws,
				'we'=>$we,
				'oborot'=>$oborot,
				'prevention'=>$prevention,
				'shiftNumber'=>$shiftNumber,
				'columnNumber'=>$columnNumber
			);
			if (isset($ws)) {
				$shiftNumber=2;
			}
			$N=$n;
		} 
		$countATime=count($arrayTime);

		for ($i=0; $i < $N; $i++) { 
			$rows[$i]['graphNumber']=$i+1;
		}

		for ($i=0; $i < $countATime; $i++) { 
			for ($a=0; $a < $countHead; $a++) { 
				if ($arrayTime[$i]['stationsId']==$allHead[$a]) {
					$arrayTime[$i]['numberHead']=$a;
				}
			}
		}

		
		//взнаємо кількість точок на маршруті
		$maxPc=0;
		for ($i=0; $i < $countATime; $i++) {
			if ($arrayTime[$i]['stationNumber']>$maxPc) {
				$maxPc=$arrayTime[$i]['stationNumber'];
			}
		}

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
				

		for ($i=0; $i <$N ; $i++) {
			$rows[$i]['presenceTo']=$arrayOrderData[$i]['attendance'];
			$rows[$i]['depotTo']=$arrayOrderData[$i]['depotTo'];
			$rows[$i]['depotAfter']=$arrayOrderData[$i]['depotAfter'];
			$rows[$i]['flightNullOne']=1;
		}

		for ($i=0; $i < $countATime; $i++) {
			if ($arrayTime[$i]['ds']!=null) {
				$rizn=$arrayTime[$i]['de']-$arrayTime[$i]['ds'];
				$n=$arrayTime[$i]['n'];
				if ($rizn>=2*60*60)
				{
					$rows[$arrayTime[$i]['n']-1]['shiftType']=3;
					$dod=round($rizn/$arrayTime[$i]['oborot'],0);
					$k=$i;
					for ($m=$k+1; $m < $countATime; $m++) {
						if ($arrayTime[$m]['n']==$n) {
							$arrayTime[$m]['columnNumber']=$arrayTime[$m]['columnNumber']+$dod;
						} 
					}
				}
				if ($rizn<2*60*60)
				{
					$k=$i;
					for ($m=$k+1; $m < $countATime; $m++) { 
						if ($arrayTime[$m]['n']==$n) {
							$arrayTime[$m]['columnNumber']=$arrayTime[$m]['columnNumber']+1;
						}
					}
				}
			}
		}

		for ($i=0; $i < $countATime; $i++) {
			if ($i==0) {
				$rows[$arrayTime[$i]['n']-1]['startShift1']=$arrayTime[$i]['time'];
				$rows[$arrayTime[$i]['n']-1]['startFlightNumber1']=$arrayTime[$i]['fl'];
			}
			if ($i==$countATime-1) {
				$rows[$arrayTime[$i]['n']-1]['endShift2']=$arrayTime[$i]['time'];
				$rows[$arrayTime[$i]['n']-1]['endFlightNumber2']=$arrayTime[$i]['fl'];
			}
			if ($arrayTime[$i]['n']!=$arrayTime[$i-1]['n']) {
				$rows[$arrayTime[$i]['n']-1]['startShift1']=$arrayTime[$i]['time'];
				$rows[$arrayTime[$i]['n']-1]['startFlightNumber1']=$arrayTime[$i]['fl'];
			}
			if ($arrayTime[$i]['n']!=$arrayTime[$i+1]['n']) {
				$rows[$arrayTime[$i]['n']-1]['endShift2']=$arrayTime[$i]['time'];
				$rows[$arrayTime[$i]['n']-1]['endFlightNumber2']=$arrayTime[$i]['fl'];
			}
			if (isset($arrayTime[$i]['ws'])) {
				$rows[$arrayTime[$i]['n']-1]['endShift1']=$arrayTime[$i]['time'];
				$rows[$arrayTime[$i]['n']-1]['startShift2']=$arrayTime[$i]['time'];
				$rows[$arrayTime[$i]['n']-1]['startFlightNumber2']=$arrayTime[$i]['fl'];
				$rows[$arrayTime[$i]['n']-1]['endFlightNumber1']=$arrayTime[$i]['fl'];
			}
		}

		for ($i=0; $i < $N; $i++) { 
			$rows[$i]['outputNumber']=$rows[$i]['startShift1']-($rows[$i]['presenceTo']+$rows[$i]['depotTo']);
			if (isset($rows[$i]['startShift2'])) {
				$dur1=$rows[$i]['endShift1']-$rows[$i]['startShift1'];
				$dur2=$rows[$i]['endShift2']-$rows[$i]['startShift2'];
				if ($dur1<=0) {
					$dur1=$dur1+24*3600;
				}
				if ($dur2<=0) {
					$dur2=$dur2+24*3600;
				}
				$rows[$i]['shiftOneTime']=$rows[$i]['presenceTo']+$rows[$i]['depotTo']+$dur1;
				$rows[$i]['shiftTwoTime']=$rows[$i]['depotAfter']+$dur2;
				$rows[$i]['flightEndTwo']=1;
				$rows[$i]['shiftType']=1;
				$rows[$i]['flightFullOne']=$rows[$i]['endFlightNumber1'];
				$rows[$i]['flightFullTwo']=$rows[$i]['endFlightNumber2']-$rows[$i]['endFlightNumber1'];
			}
			if (!isset($rows[$i]['startShift2'])) {
				$dur1=$rows[$i]['endShift2']-$rows[$i]['startShift1'];
				if ($dur1<=0) {
					$dur1=$dur1+24*3600;
				}
				$rows[$i]['shiftOneTime']=$rows[$i]['presenceTo']+$rows[$i]['depotTo']+$dur1+$rows[$i]['depotAfter'];
				$rows[$i]['flightEndOne']=1;
				$rows[$i]['flightFullOne']=$rows[$i]['endFlightNumber2'];
			}
			$rows[$i]['depotTo']=$rows[$i]['startShift1']-$rows[$i]['depotTo'];
			$rows[$i]['depotAfter']=$rows[$i]['endShift2']+$rows[$i]['depotAfter'];
			$rows[$i]['presenceTo']=$rows[$i]['depotTo']-$rows[$i]['presenceTo'];
		}
		for ($i=0; $i < $countATime; $i++) {
			$notFormatTime=$arrayTime[$i]['time'];
			$doFormatTime= new Time($notFormatTime);
			$rows[$arrayTime[$i]['n']-1][$arrayTime[$i]['stationsId'].$arrayTime[$i]['columnNumber']]=$doFormatTime->getFormattedTime();
			if (isset($arrayTime[$i]['ws'])) {
				$shiftArray[]=array(
					'time'=>$doFormatTime->getFormattedTime(),
					'n'=>$arrayTime[$i]['n'],
					'columnIndex'=>5+$arrayTime[$i]['numberHead']+$countHead*($arrayTime[$i]['columnNumber']-1)
				);
			}
			if (isset($arrayTime[$i]['ds'])) {
				$durDin= new Time($arrayTime[$i]['de']-$arrayTime[$i]['ds']);
				$rows[$arrayTime[$i]['n']-1][$arrayTime[$i]['stationsId'].($arrayTime[$i]['columnNumber']+1)]=$durDin->getFormattedTime();
				$dinnerArray[]=array(
					'start'=>$arrayTime[$i]['ds'],
					'end'=>$arrayTime[$i]['de'],
					'duration'=>$arrayTime[$i]['de']-$arrayTime[$i]['ds'],
					'n'=>$arrayTime[$i]['n'],
					'shiftNumber'=>$arrayTime[$i]['shiftNumber'],
					'time'=>$durDin->getFormattedTime(),
					'columnIndex'=>5+$arrayTime[$i]['numberHead']+$countHead*($arrayTime[$i]['columnNumber'])
				);
			}
			if (isset($arrayTime[$i]['prevention'])) {
				$preventionArray[]=array(
					'time'=>$doFormatTime->getFormattedTime(),
					'n'=>$arrayTime[$i]['n'],
					'columnIndex'=>5+$arrayTime[$i]['numberHead']+$countHead*($arrayTime[$i]['columnNumber']-1)
				);
			}
		}
		$countAD=count($dinnerArray);
		for ($i=0; $i < $countAD; $i++) { 
			$durDinnerArray[$dinnerArray[$i]['n']][$dinnerArray[$i]['shiftNumber']][]=$dinnerArray[$i]['duration'];
		}

		for ($i=0; $i < $N; $i++) {
			$t=count($durDinnerArray[$i+1][1]);
			if ($t!=0) {
				$rows[$i]['durdin1']=array_sum($durDinnerArray[$i+1][1]);
			}
			if ($t==0) {
				$rows[$i]['durdin1']=0;
			}
			$tt=count($durDinnerArray[$i+1][2]);
			if ($tt!=0) {
				$rows[$i]['durdin2']=array_sum($durDinnerArray[$i+1][2]);
			}
			if ($tt==0) {
				$rows[$i]['durdin2']=0;
			}
		}

		for ($i=0; $i < $N; $i++) { 
			$rows[$i]['id']=$i;
			if ($rows[$i]['shiftType']==1) {
				$rows[$i]['shiftOneTime']=$rows[$i]['shiftOneTime']-$rows[$i]['durdin1'];
				$rows[$i]['shiftTwoTime']=$rows[$i]['shiftTwoTime']-$rows[$i]['durdin2'];
			}
			else if ($rows[$i]['shiftType']!=1) {
				$rows[$i]['shiftOneTime']=$rows[$i]['shiftOneTime']-$rows[$i]['durdin1'];
			}
			if ($rows[$i]['shiftType']==null) {
				$rows[$i]['shiftType']=2;
			}
			$out[]=$rows[$i]['outputNumber'];
		}
	/*	for ($i=0; $i < $N; $i++) {
			$e= RouteGraphOrderTimeTable::model()->findByAttributes(array(
				'historys_id'=>$idHis,
				'graphs_number'=>$i+1
			));
			$e->typeWork=$rows[$i]['shiftType'];
			$e->save();
			
		}*/

		function cmp($a, $b)
		{
		    if ($a == $b) {
		        return 0;
		    }
		    return ($a < $b) ? -1 : 1;
		}
		if (isset($out)) {
			usort($out, "cmp");
		}
		
		for ($i=0; $i < $N; $i++) {
			for ($a=0; $a < $N; $a++) {
				if ($rows[$i]['outputNumber']==$out[$a]) {
					$rows[$i]['outputNumber']=$a+1;
				}
			}
		}
		for ($i=0; $i < $N; $i++) { 
			if ($rows[$i]['shiftType']==2) {$rows[$i]['shiftType']="цілоденний";}
			else if ($rows[$i]['shiftType']==3) {$rows[$i]['shiftType']="розривний";}
			else if ($rows[$i]['shiftType']==1) {$rows[$i]['shiftType']="2-змінний";}
			$pr= new Time($rows[$i]['presenceTo']);
			$rows[$i]['presenceTo']=$pr->getFormattedTime();
			$dt= new Time($rows[$i]['depotTo']);
			$rows[$i]['depotTo']=$dt->getFormattedTime();
			$da= new Time($rows[$i]['depotAfter']);
			$rows[$i]['depotAfter']=$da->getFormattedTime();
			$ts1=new Time($rows[$i]['shiftOneTime']);
			$rows[$i]['shiftOneTime']=$ts1->getFormattedTime();
			if (isset($rows[$i]['shiftTwoTime'])) {
				$ts2=new Time($rows[$i]['shiftTwoTime']);
				$rows[$i]['shiftTwoTime']=$ts2->getFormattedTime();
			}
		}
		for ($i=0; $i < $countATime; $i++) { 
			$arrFlPc[$arrayTime[$i]['n']-1][$arrayTime[$i]['shiftNumber']][]=$arrayTime[$i];
		}
		for ($i=0; $i < $N; $i++) { 
			for ($a=1; $a <=2 ; $a++) { 
				$countAmountPc=count($arrFlPc[$i][$a]);
				
					if ($a==1) {
						$rows[$i]['flightFullOne']=round($countAmountPc/$maxPc,1);
					}
					if (($a==2) && (isset($rows[$i]['flightEndTwo']))) {
						$rows[$i]['flightFullTwo']=round($countAmountPc/$maxPc,1);
					}
				
			}
		}
		//print_r($arrFlPc);
				
		$result = array('success' => true, 'rows' => array(), );
		$metaData['fields'] = $fields;
		$result['metaData'] = $metaData;		
		$result['rows'] = $rows;
		$result['dinners'] = $dinnerArray;
		$result['shifts'] = $shiftArray;
		$result['prevention'] = $preventionArray;
		
		echo CJSON::encode($result);
	}

////////////////////////////////////////////////////////////////////////////////


	public function actionCreate() //на посилання з post
	{
		$idHis=Yii::app()->request->getParam('idHis');
		$head=Yii::app()->request->getParam('head');
		$routeid=Yii::app()->request->getParam('routeid');

		$allHead = explode(",", $head);
		$countHead=count($allHead);

		for ($i=0; $i < $countHead; $i++)
		{
			$b = Stations::model()->findByAttributes(array('id'=>$allHead[$i]));
			$nameHeads[$i]['namePoint']=$b->name;
		}
		$r = RouteTimeTable::model()->findAll(array(
			'condition' => 'routes_history_id = :hid',
			'params' => array(':hid' => $idHis),
			'order' => 'Id'
		));
		foreach ($r as $rr) {
			$fl[]=$rr->flights_number;
		}
		$countFl=count($fl);
		$max=1;
		for ($i=0; $i < $countFl; $i++) { 
			if ($fl[$i]>=$max) {
				$max=$fl[$i];
			}
		}
		$max=$max+6;
		for ($i=1; $i <=$max ; $i++) { 
			for ($a=0; $a < $countHead; $a++) { 
				$arrayHeadsTwo[]=array(
					'idPoint'=>$allHead[$a],
					'namePoint'=>$nameHeads[$a]['namePoint'],
					'dataIndForTable'=>$allHead[$a].$i
				);
			}
		}

		$result = array('success' => true, 'rows' => $arrayHeadsTwo);
		echo CJSON::encode($result);
	}
}
?>