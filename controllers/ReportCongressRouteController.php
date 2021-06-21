<?php
class ReportCongressRouteController extends CController  {
	public function actionRead()//на посилання з гет
	{
		$level=Yii::app()->request->getParam('level');
		$nodeId=Yii::app()->request->getParam('recordIdLevel');
		$fromDate=Yii::app()->request->getParam('fromDate');
		$newFrom = strtotime($fromDate)+3600;
		$newTo   = $newFrom+(23*3600+59*60+60+3600);
		if ($level==3) {
			//вибираемо всі координати за період
			$loc=Locations::model()->with('graph','route','bort')->findAll(array(
				'select'=>'t.routes_id, t.graphs_id, latitude, longitude, unixtime',
				'condition'=> 'unixtime >= :f AND unixtime <= :t AND graphs_id = :g',
				'params'   =>array(':f'=>$newFrom, ':t'=>$newTo, ':g'=>$nodeId),
				'order'    => 't.unixtime'
			));
			foreach ($loc as $k) {
				$arrayLocations[]=array(
					'routes_id'=>$k->routes_id,
					'routes_name'=>$k->route->name,
					'graphs_id'=>$nodeId,
					'graphs_name'=>$k->graph->name,
					'latitude'=>$k->latitude,
					'longitude'=>$k->longitude,
					'unixtime'=>$k->unixtime,
					'data_time'=>strftime('%Y-%m-%d %H:%M:%S',$k->unixtime)
				);
			}
		 
			//print_r($arrayLocations);
			//шукаемо вейпоінттс
			$routesId=$arrayLocations[1]['routes_id'];
			
			$wayPoints=WayPoints::model()->findAll(array(
				'condition'=> 'routes_id = :r',
				'params'   =>array(':r'=>$routesId),
				'order'    => 'number'));
			foreach ($wayPoints as $k) {
				$arrayWayPoints[]=array('longitude'=>$k->longitude,'latitude'=>$k->latitude);
			}	
			//print_r($arrayWayPoints);
			$R=6372795;	
			for ($i=0; $i <count($arrayWayPoints) ; $i++) { 
				if ($i==0) {
					$arrayWayPoints1[]=$arrayWayPoints[$i];
				}
				else {

					$φA=(double) (floor($arrayWayPoints[$i]['latitude']/100)*100+(($arrayWayPoints[$i]['latitude']   - floor($arrayWayPoints[$i]['latitude']/100)*100)*100/60))/100;
					$λA=(double) (floor($arrayWayPoints[$i]['longitude']/100)*100+(($arrayWayPoints[$i]['longitude']   - floor($arrayWayPoints[$i]['longitude']/100)*100)*100/60))/100;
					$φB=(double) (floor($arrayWayPoints[$i+1]['latitude']/100)*100+(($arrayWayPoints[$i+1]['latitude']   - floor($arrayWayPoints[$i+1]['latitude']/100)*100)*100/60))/100;
					$λB=(double) (floor($arrayWayPoints[$i+1]['longitude']/100)*100+(($arrayWayPoints[$i+1]['longitude']   - floor($arrayWayPoints[$i+1]['longitude']/100)*100)*100/60))/100;
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
				    if ($dist>40) {
				    	$arrayWayPoints1[]=$arrayWayPoints[$i];
				    	$difference=round($dist/40,0);
				    	$difLong=($arrayWayPoints[$i]['longitude']-$arrayWayPoints[$i+1]['longitude'])/$difference;
				    	$difLat=($arrayWayPoints[$i]['latitude']-$arrayWayPoints[$i+1]['latitude'])/$difference;
				    	for ($i=0; $i < $difference; $i++) { 
				    	 	$arrayWayPoints1[]=array(
				    	 		'longitude'=>round($arrayWayPoints[$i]['longitude']-($difLong*($i+1)),4),
				    	 		'latitude'=>round($arrayWayPoints[$i]['latitude']-($difLat*($i+1)),4)
				    	 	);
				    	} 
				    }
				}
			}	
		print_r($arrayWayPoints1); 
		}   
		$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows); 
		echo CJSON::encode($result);
		
	}
}
?>	