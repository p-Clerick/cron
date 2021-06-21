<?php
class ReportIntervalsReportController extends CController  {
	public function actionRead()//на посилання з гет
	{
		$level=Yii::app()->request->getParam('level');
		$nodeId=Yii::app()->request->getParam('recordIdLevel');
		$fromDate=Yii::app()->request->getParam('fromDate');
		$toDate=Yii::app()->request->getParam('toDate');

		$newFrom = strtotime($fromDate);
		$newTo   = $newFrom+(24*60*60);

		if ($level==3) {
			$a=LocationsFlight::model()->with('route','stations','graph','bort')->findAll(array(
						'condition'=> 'unixtime >= :f AND unixtime <= :t AND graphs_id = :gid',
						'params'   =>array(':f'=>$newFrom, ':t'=>$newTo, ':gid'=>$nodeId),
						'order'    => 't.unixtime'));
			foreach ($a as $k) {
				$array1[$k->stations->name][]=$k->unixtime;
			}
		}
		if ($level==2) {
			$a=LocationsFlight::model()->with('route','stations','graph','bort')->findAll(array(
						'condition'=> 'unixtime >= :f AND unixtime <= :t AND t.routes_id = :gid',
						'params'   =>array(':f'=>$newFrom, ':t'=>$newTo, ':gid'=>$nodeId),
						'order'    => 't.unixtime'));
			foreach ($a as $k) {
				$array1[$k->stations->name][]=$k->unixtime;
			}
		}
		//print_r($array1);
		foreach ($array1 as $st => $aru) {
			for ($i=0; $i < count($aru)-1; $i++) { 
				$rows[]=array(
					'date'=>$st,
					'stationsName'=> strftime('%H:%M:%S',$aru[$i]),
					'timeAverage'=> round(($aru[$i+1]-$aru[$i])/60,0)
				);
				
			}
		}
		
		

		$result = array('success' => true, 'rows'=>$rows); 
		echo CJSON::encode($result);

	}
}