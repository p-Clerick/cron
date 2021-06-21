<?php
class ReportOutingGraphsController extends CController  {
	public function actionRead()//на посилання з гет
	{
		$sortNo=Yii::app()->request->getParam('sortNo');
		$level=Yii::app()->request->getParam('level');
		$nodeId=Yii::app()->request->getParam('recordIdLevel');
		$fromDate=Yii::app()->request->getParam('fromDate');
		$toDate=Yii::app()->request->getParam('toDate');

/*		$unix11=strtotime($fromDate)+3600;
		$unix21=strtotime($fromDate)+(25*3600);
	
	$loc=LocationsFlight::model()->findAll(array(
					'select'=>'graphs_id, flights_number, stations_id, id, routes_id',
					'condition'=> 'unixtime >= :f AND unixtime <= :t',
					'params'   =>array(':f'=>$unix11, ':t'=>$unix21),
					'order'=>'id'));	
	foreach ($loc as $key) {
		$arLoc[$key->graphs_id."/".$key->graphs_id][$key->flights_number][$key->stations_id][]=$key->id;
	}
	
	foreach ($arLoc as $g => $ag) {
		foreach ($ag as $f => $af) {
			foreach ($af as $s => $as) {
				$cloc=count($as);
				if ($cloc>1) {
					print_r($as);echo "\r\n";
				//	LocationsFlight::model()->deleteAll(array(
	    		//	'condition' => 'id = :d',
				//	'params' => array(':d' => $as[1])));
				}

			}
		}
	}
*/
		if ($level==1) {
			if(Yii::app()->user->name != "guest"){
	            $carrier = Yii::app()->user->checkUser(Yii::app()->user);
	        }
	        if ($carrier) { 
	        	$a = ReportOutingGraphs::model()->with('route','graph','bort','park','carrier')->findAll(array(
					'condition'=> 't.date >= :f AND t.date <= :t AND t.carriers_id = :car',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate, ':car' => $carrier['carrier_id']),
					'order'    => 't.id'));
				foreach ($a as $k) {
					//if ($k->bort->status=='yes') {
						$rows[]=array(
							'id'=>$k->id,
							'routeId'=>$k->routes_id,
							'routeName'=>$k->route->name,
							'graphName'=>$k->graph->name,
							'bortNumber'=>$k->bort->number,
							'bortNameState'=>$k->bort->state_number,
							'parkName'=>$k->carrier->name,
							'graphId'=>$k->graphs_id,
							'date'=>$k->date,
							'bortId'=>$k->borts_id,
							'outStatus'=>$k->outing_status
						);
					//}
				}
				$countRows=count($rows);
	        }
	        else {

				$a = ReportOutingGraphs::model()->with('route','graph','bort','park','carrier')->findAll(array(
					'condition'=> 'date >= :f AND date <= :t',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate),
					'order'    => 't.id'));
				foreach ($a as $k) {
					//if ($k->bort->status=='yes') {
						$rows[]=array(
							'id'=>$k->id,
							'routeId'=>$k->routes_id,
							'routeName'=>$k->route->name,
							'graphName'=>$k->graph->name,
							'bortNumber'=>$k->bort->number,
							'bortNameState'=>$k->bort->state_number,
							'parkName'=>$k->carrier->name,
							'graphId'=>$k->graphs_id,
							'date'=>$k->date,
							'bortId'=>$k->borts_id,
							'outStatus'=>$k->outing_status
						);
					
				}
				$countRows=count($rows);
			}
		}
		////////////////////////////////////////////////////////////////////////////////////////////
		if ($level==2) {
			$a = ReportOutingGraphs::model()->with('route','graph','bort','park','carrier')->findAll(array(
				'condition'=> 't.routes_id = :rhid AND date >= :f AND date <= :t',
				'params'   => array(':rhid' => $nodeId,':f'=>$fromDate, ':t'=>$toDate),
				'order'    => 't.id'));
			foreach ($a as $k) {
				$rows[]=array(
						'id'=>$k->id,
						'routeId'=>$k->routes_id,
						'graphId'=>$k->graphs_id,
						'routeName'=>$k->route->name,
						'graphName'=>$k->graph->name,
						'bortNumber'=>$k->bort->number,
						'parkName'=>$k->carrier->name,
						'bortNameState'=>$k->bort->state_number,
						'date'=>$k->date,
						'bortId'=>$k->borts_id,
						'outStatus'=>$k->outing_status
				);
			}
			$countRows=count($rows);
		}
		//////////////////////////////////////////////////////////////////////////
		if ($level==3) {
			$a = ReportOutingGraphs::model()->with('route','graph','bort','park','carrier')->findAll(array(
				'condition'=> 't.graphs_id = :rhid AND date >= :f AND date <= :t',
				'params'   => array(':rhid' => $nodeId,':f'=>$fromDate, ':t'=>$toDate),
				'order'    => 't.id'));
			foreach ($a as $k) {
				$rows[]=array(
						'id'=>$k->id,
						'routeId'=>$k->routes_id,
						'graphId'=>$k->graphs_id,
						'routeName'=>$k->route->name,
						'graphName'=>$k->graph->name,
						'bortNumber'=>$k->bort->number,
						'parkName'=>$k->carrier->name,
						'bortNameState'=>$k->bort->state_number,
						'date'=>$k->date,
						'bortId'=>$k->borts_id,
						'outStatus'=>$k->outing_status
				);
				$routeFromTable=$k->routes_id;
			}
			$countRows=count($rows);
		}
		//шукаемо пояснення
		$findReazons = ReazonsNoOuting::model()->findAll(array(
			'condition'=> 'date >= :f AND date <= :t',
			'params'   => array(':f'=>$fromDate, ':t'=>$toDate),
			'order'    => 'id'));
		$reazonsArray = array();
		foreach ($findReazons as $k) {
			$reazonsArray[]=array(
				'date'=>$k->date,
				'bortsId'=>$k->borts_id,
				'reazons' => $k->reazons_text,
				'graphId'=>$k->graphs_id
			);
		}
		$countReazons=count($reazonsArray);
		for ($i=0; $i < $countReazons; $i++) { 
			for ($q=0; $q < $countRows; $q++) {
				if ($rows[$q]['date']==$reazonsArray[$i]['date']) {
					if ($reazonsArray[$i]['graphId']==0){
						if ($rows[$q]['bortId']==$reazonsArray[$i]['bortsId']) {
							$rows[$q]['notation']=$reazonsArray[$i]['reazons'];
						}
					}
					else if ($reazonsArray[$i]['bortId']==0) {
						if ($rows[$q]['graphId']==$reazonsArray[$i]['graphId']) {
							$rows[$q]['notation']=$reazonsArray[$i]['reazons'];
						}
					}
					else if (($reazonsArray[$i]['graphId']!=0) && ($reazonsArray[$i]['bortId']!=0)) {
						if (($rows[$q]['graphId']==$reazonsArray[$i]['graphId']) && ($rows[$q]['bortId']==$reazonsArray[$i]['bortId'])) {
							$rows[$q]['notation']=$reazonsArray[$i]['reazons'];
						}
					}
				}
			}
		}
		for ($i=0; $i < $countRows; $i++) { 

			if ($rows[$i]['outStatus']=='Y') {
				$rows[$i]['outStatus']=Yii::app()->session['YesTextSmall'];
			}
			if ($rows[$i]['outStatus']=='N') {
				$rows[$i]['outStatus']=Yii::app()->session['NoTextSmall'];
			}
			$rows[$i]['npp']=$i+1;
		}

		if ($sortNo==0) {
			$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows); 
			echo CJSON::encode($result);
		}
		if ($sortNo==1) {//ні не виїхало
			for ($i=0; $i <$countRows ; $i++) { 
				if ($rows[$i]['outStatus']==Yii::app()->session['NoTextSmall']) {
					$rowsNo[] = $rows[$i];
				}
			}
			$countRowsNo=count($rowsNo);
			$result = array('success' => true, 'rows'=>$rowsNo, 'totalCount'=>$countRowsNo); 
			echo CJSON::encode($result);
		}
		if ($sortNo==2) {//так виїхало
			for ($i=0; $i <$countRows ; $i++) { 
				if ($rows[$i]['outStatus']==Yii::app()->session['YesTextSmall']) {
					$rowsNo[] = $rows[$i];
				}
			}
			$countRowsNo=count($rowsNo);
			$result = array('success' => true, 'rows'=>$rowsNo, 'totalCount'=>$countRowsNo); 
			echo CJSON::encode($result);
		}
		if ($sortNo==3) {//z primitkoyu
			for ($i=0; $i <$countRows ; $i++) { 
				if ($rows[$i]['notation']!=null) {
					$rowsNo[] = $rows[$i];
				}
			}
			$countRowsNo=count($rowsNo);
			$result = array('success' => true, 'rows'=>$rowsNo, 'totalCount'=>$countRowsNo); 
			echo CJSON::encode($result);
		}
	}

	public function actionCreate () {
		$date=Yii::app()->request->getParam('date');
		$notation=Yii::app()->request->getParam('notation');
		$bortId=Yii::app()->request->getParam('bortId');
		$graphId=Yii::app()->request->getParam('graphId');
		//шукаемо чи є записи старі
		$b = ReazonsNoOuting::model()->findByAttributes(array(
			'date'=>$date,
			'borts_id'=>$bortId,
			'graphs_id'=>$graphId
		));
		if (isset($b)) {
			$newCom=$b->reazons_text." ".$notation;
			$length=strlen($newCom);
			if ($length>=60)
			{
				$newtext = wordwrap($newCom, 60, "<br />\n");
			}
			else if ($length<60)
			{
				$newtext = $newCom;
			}
			$b->reazons_text=$newtext;
			$b->save();
		}
		if (!isset($b)) {
			$a = new ReazonsNoOuting;
			$a->date=$date;
			$a->borts_id=$bortId;
			$a->graphs_id=$graphId;
			$length=strlen($notation);
			if ($length>=60)
			{
				$newtext = wordwrap($notation, 60, "<br />\n");
			}
			else if ($length<60)
			{
				$newtext = $notation;
			}
			$a->reazons_text=$newtext;
			$a->save();
		}
		
		$result = array('success' => true); 
		echo CJSON::encode($result);
	}
}
?>	