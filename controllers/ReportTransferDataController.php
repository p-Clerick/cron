<?php
class ReportTransferDataController extends CController  {
	public function actionRead()//на посилання з гет
	{
		$level=Yii::app()->request->getParam('level');
		$nodeId=Yii::app()->request->getParam('recordIdLevel');
		$fromDate=Yii::app()->request->getParam('fromDate');
		$toDate=Yii::app()->request->getParam('toDate');

		$newFrom = strtotime($fromDate)+3600;
		$newTo   = strtotime($toDate)+(25*3600);

		$diff=30;

		/*function getaddress($lat,$lng)
		{
				$url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&sensor=false&language=uk';
				$json = @file_get_contents($url);
				$data=json_decode($json);
				$status = $data->status;
				if($status=="OK")
				return $data->results[0]->formatted_address;
				else
				return false;
		}*/

		if ($level==1) {
		}
		if ($level==2) {
			$a=Locations::model()->with('route','graph','bort')->findAll(array(
					'condition'=> 'unixtime >= :f AND unixtime <= :t AND t.routes_id = :r',
					'params'   =>array(':f'=>$newFrom, ':t'=>$newTo, ':r'=>$nodeId),
					'order'    => 'unixtime'));
			foreach ($a as $k) {
				$allData[$k->bort->number][]=array(
					'route'=>$k->route->name,
					'graph'=>$k->graph->name,
					'bort_number'=>$k->bort->number,
					'bort_state_number'=>$k->bort->state_number,
					'unixtime'=>$k->unixtime,
					'long'=>$k->longitude,
					'lat'=>$k->latitude
				);
			}
			$npp=1;
			foreach ($allData as $bort => $arData) {
				for ($i=0; $i < count($arData)+1; $i++) { 
					if ($i==0) {
						$diff0=$arData[$i]['unixtime']-$newFrom;
					}
					else if ($i==count($arData)) {
						$diff0=$newTo-$arData[$i-1]['unixtime'];
					}
					else {
						$diff0=$arData[$i]['unixtime']-$arData[$i-1]['unixtime'];
					}
					if ($diff0>$diff) {
						$realduration= new Time($diff0);
						if ($i==0) {
							$rows[]=array(
								'npp'=>$npp,
								'route'=>$arData[$i]['route'],
								'graph'=>$arData[$i]['graph'],
								'bort_number'=>$arData[$i]['bort_number'],
								'bort_state_number'=>$arData[$i]['bort_state_number'],
								'time_from'=>strftime('%Y-%m-%d %H:%M:%S',$newFrom),
								'time_to'=>strftime('%Y-%m-%d %H:%M:%S',$arData[$i]['unixtime']),
	        					'duration'=>$realduration->getFormattedTime(),
	        					'latTo'=>$arData[$i]['lat'],
	        					'longTo'=>$arData[$i]['long']
							);
							$npp=$npp+1;
						}
						else if ($i==count($arData)) {
							$rows[]=array(
								'npp'=>$npp,
								'route'=>$arData[$i-1]['route'],
								'graph'=>$arData[$i-1]['graph'],
								'bort_number'=>$arData[$i-1]['bort_number'],
								'bort_state_number'=>$arData[$i-1]['bort_state_number'],
								'time_from'=>strftime('%Y-%m-%d %H:%M:%S',$arData[$i-1]['unixtime']),
								'time_to'=>strftime('%Y-%m-%d %H:%M:%S',$newTo),
	        					'duration'=>$realduration->getFormattedTime(),
	        					'latFrom'=>$arData[$i-1]['lat'],
	        					'longFrom'=>$arData[$i-1]['long']
							);
							$npp=$npp+1;
						}
						else {
							$rows[]=array(
								'npp'=>$npp,
								'route'=>$arData[$i]['route'],
								'graph'=>$arData[$i]['graph'],
								'bort_number'=>$arData[$i]['bort_number'],
								'bort_state_number'=>$arData[$i]['bort_state_number'],
								'time_from'=>strftime('%Y-%m-%d %H:%M:%S',$arData[$i-1]['unixtime']),
								'time_to'=>strftime('%Y-%m-%d %H:%M:%S',$arData[$i]['unixtime']),
	        					'duration'=>$realduration->getFormattedTime(),
	        					'latFrom'=>$arData[$i-1]['lat'],
	        					'latTo'=>$arData[$i]['lat'],
	        					'longFrom'=>$arData[$i-1]['long'],
	        					'longTo'=>$arData[$i]['long']
							);
							$npp=$npp+1;
						}
					}
					
				}
			}
			$countRows=count($rows);
			
		
			for ($i=0; $i < $countRows; $i++) { 
				$latFrom8=(double) (floor($rows[$i]['latFrom']/100)*100+(($rows[$i]['latFrom']   - floor($rows[$i]['latFrom']/100)*100)*100/60))/100;
				$longFrom8=(double) (floor($rows[$i]['longFrom']/100)*100+(($rows[$i]['longFrom']   - floor($rows[$i]['longFrom']/100)*100)*100/60))/100;
				$latTo8=(double) (floor($rows[$i]['latTo']/100)*100+(($rows[$i]['latTo']   - floor($rows[$i]['latTo']/100)*100)*100/60))/100;
				$longTo8=(double) (floor($rows[$i]['longTo']/100)*100+(($rows[$i]['longTo']   - floor($rows[$i]['longTo']/100)*100)*100/60))/100;
				$addressFrom=explode(',',getaddress($latFrom8,$longFrom8));
				$addressTo=explode(',',getaddress($latTo8,$longTo8));
				$rows[$i]['adress']=$addressFrom[0].$addressFrom[1].' - '.$addressTo[0].$addressTo[1];
			}

			
			$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows); 
			echo CJSON::encode($result);

		}
		if ($level==3) {
			$a=Locations::model()->with('route','graph','bort')->findAll(array(
					'condition'=> 'unixtime >= :f AND unixtime <= :t AND graphs_id = :g',
					'params'   =>array(':f'=>$newFrom, ':t'=>$newTo, ':g'=>$nodeId),
					'order'    => 'unixtime'));
			foreach ($a as $k) {
				$allData[$k->bort->number][]=array(
					'route'=>$k->route->name,
					'graph'=>$k->graph->name,
					'bort_number'=>$k->bort->number,
					'bort_state_number'=>$k->bort->state_number,
					'unixtime'=>$k->unixtime,
					'long'=>$k->longitude,
					'lat'=>$k->latitude
				);
			}
			$npp=1;
			foreach ($allData as $bort => $arData) {
				for ($i=0; $i < count($arData)+1; $i++) { 
					if ($i==0) {
						$diff0=$arData[$i]['unixtime']-$newFrom;
					}
					else if ($i==count($arData)) {
						$diff0=$newTo-$arData[$i-1]['unixtime'];
					}
					else {
						$diff0=$arData[$i]['unixtime']-$arData[$i-1]['unixtime'];
					}
					if ($diff0>$diff) {
						$realduration= new Time($diff0);
						if ($i==0) {
							$rows[]=array(
								'npp'=>$npp,
								'route'=>$arData[$i]['route'],
								'graph'=>$arData[$i]['graph'],
								'bort_number'=>$arData[$i]['bort_number'],
								'bort_state_number'=>$arData[$i]['bort_state_number'],
								'time_from'=>strftime('%Y-%m-%d %H:%M:%S',$newFrom),
								'time_to'=>strftime('%Y-%m-%d %H:%M:%S',$arData[$i]['unixtime']),
	        					'duration'=>$realduration->getFormattedTime(),
	        					'latTo'=>$arData[$i]['lat'],
	        					'longTo'=>$arData[$i]['long']
							);
							$npp=$npp+1;
						}
						else if ($i==count($arData)) {
							$rows[]=array(
								'npp'=>$npp,
								'route'=>$arData[$i-1]['route'],
								'graph'=>$arData[$i-1]['graph'],
								'bort_number'=>$arData[$i-1]['bort_number'],
								'bort_state_number'=>$arData[$i-1]['bort_state_number'],
								'time_from'=>strftime('%Y-%m-%d %H:%M:%S',$arData[$i-1]['unixtime']),
								'time_to'=>strftime('%Y-%m-%d %H:%M:%S',$newTo),
	        					'duration'=>$realduration->getFormattedTime(),
	        					'latFrom'=>$arData[$i-1]['lat'],
	        					'longFrom'=>$arData[$i-1]['long']
							);
							$npp=$npp+1;
						}
						else {
							$rows[]=array(
								'npp'=>$npp,
								'route'=>$arData[$i]['route'],
								'graph'=>$arData[$i]['graph'],
								'bort_number'=>$arData[$i]['bort_number'],
								'bort_state_number'=>$arData[$i]['bort_state_number'],
								'time_from'=>strftime('%Y-%m-%d %H:%M:%S',$arData[$i-1]['unixtime']),
								'time_to'=>strftime('%Y-%m-%d %H:%M:%S',$arData[$i]['unixtime']),
	        					'duration'=>$realduration->getFormattedTime(),
	        					'latFrom'=>$arData[$i-1]['lat'],
	        					'latTo'=>$arData[$i]['lat'],
	        					'longFrom'=>$arData[$i-1]['long'],
	        					'longTo'=>$arData[$i]['long']
							);
							$npp=$npp+1;
						}
					}
					
				}
			}
			$countRows=count($rows);
			
		
			for ($i=0; $i < $countRows; $i++) { 
				$latFrom8=(double) (floor($rows[$i]['latFrom']/100)*100+(($rows[$i]['latFrom']   - floor($rows[$i]['latFrom']/100)*100)*100/60))/100;
				$longFrom8=(double) (floor($rows[$i]['longFrom']/100)*100+(($rows[$i]['longFrom']   - floor($rows[$i]['longFrom']/100)*100)*100/60))/100;
				$latTo8=(double) (floor($rows[$i]['latTo']/100)*100+(($rows[$i]['latTo']   - floor($rows[$i]['latTo']/100)*100)*100/60))/100;
				$longTo8=(double) (floor($rows[$i]['longTo']/100)*100+(($rows[$i]['longTo']   - floor($rows[$i]['longTo']/100)*100)*100/60))/100;
				$addressFrom=explode(',',getaddress($latFrom8,$longFrom8));
				$addressTo=explode(',',getaddress($latTo8,$longTo8));
				$rows[$i]['adress']=$addressFrom[0].$addressFrom[1].' - '.$addressTo[0].$addressTo[1];
			}

			
			$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows); 
			echo CJSON::encode($result);
		}
	}
}
?>	