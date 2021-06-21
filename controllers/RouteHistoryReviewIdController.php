<?php
	class RouteHistoryReviewIdController extends Controller
	{
		public function actionRead($id)//на посилання з гет для заповнення таблиці всіх розрахованих розкладів руху
		{
			$routeid=Yii::app()->request->getParam('routeid');
			$type=Yii::app()->request->getParam('type');
			$a = RouteHistoryIdAll::model()->findAll(array(
				'condition'=> 'routes_id = :rid',
				'params'   =>array(':rid' => $routeid),
				'order'    => 'Id'));
			$i=1;
			foreach ($a as $aa) 
			{
				$recordId=$aa->Id;
				$create=$aa->date_create;
				$active=$aa->date_activity;
				$moveMethod=$aa->move_method;
				$calcMethod=$aa->calc_method;
				if ($calcMethod==1){$calcMethod=Yii::app()->session['CalculationText'].' 1';}
				if ($calcMethod==2){$calcMethod=Yii::app()->session['CalculationText'].' 2';}
				if ($calcMethod==3){$calcMethod=Yii::app()->session['CalculationText'].' 3';}
				if ($moveMethod==1){$moveMethod=Yii::app()->session['MoveTypeLine'];}
				if ($moveMethod==2){$moveMethod=Yii::app()->session['MoveTypeRound'];}
				if ($moveMethod==3){$moveMethod=Yii::app()->session['MoveTypeMixed'];}
				$schtype=$aa->schedules_type_id;
				if ($schtype==1){$schtype=Yii::app()->session['DayTypeWork'];}
				if ($schtype==2){$schtype=Yii::app()->session['DayTypeHollyday'];}
				$comment=$aa->comment;
				$rows[]=array(
					'scheduletype'=>$schtype,
					'npp'=>$i,
					'id'=>$recordId,
					'create'=>$create,
					'amount'=>$aa->amount,
					'activity'=>$active,
					'moveMethod'=>$moveMethod,
					'calcMethod'=>$calcMethod,
					'comment'=>$comment
				);
				$i++;
			}
			$countRows=count($rows);
//встановлюємо колір запису активності			
			$maxR = '0000-00-00 00:00:00';
			$maxW = '0000-00-00 00:00:00';
			for ($i=0; $i<$countRows; $i++)
			{
				if (isset($rows[$i]['activity']))
				{
					$rows[$i]['color']=1;
					if ($rows[$i]['scheduletype'] == Yii::app()->session['DayTypeWork'])
					{
						if ($rows[$i]['activity']>$maxR)
						{
							$maxR=$rows[$i]['activity'];
						}
					}
					if ($rows[$i]['scheduletype'] == Yii::app()->session['DayTypeHollyday'])
					{
						if ($rows[$i]['activity']>$maxW)
						{
							$maxW=$rows[$i]['activity'];
						}
					}
				}
				if (!isset($rows[$i]['activity']))
				{
					$rows[$i]['color']=0;
				}
			}
			for ($i=0; $i<$countRows; $i++)
			{
				if (isset($rows[$i]['activity']))
				{
					if ($rows[$i]['activity']==$maxR)
					{
						$rows[$i]['color']=2;
					}
					if ($rows[$i]['activity']==$maxW)
					{
						$rows[$i]['color']=2;
					}
				}
			}
//встановлюємо колір запису останнього розрахованого
			if (isset($rows[$countRows-1]['create']) && ($rows[$countRows-1]['color']!=2))
			{
				$rows[$countRows-1]['color']=3;
			}

			if ($type==1)//red
			{
				for ($i=0; $i<$countRows; $i++)
				{
					if ($rows[$i]['color']==2)
					{
						$rowsDo[]=$rows[$i];
					}
				}
			}
			if ($type==2)//green
			{
				for ($i=0; $i<$countRows; $i++)
				{
					if ($rows[$i]['color']==1)
					{
						$rowsDo[]=$rows[$i];
					}
				}
			}
			if ($type==3)//чорний
			{
				for ($i=0; $i<$countRows; $i++)
				{
					if (($rows[$i]['color']!=1) && ($rows[$i]['color']!=2))
					{
						$rowsDo[]=$rows[$i];
					}
				}
			}
			if ($type==4)//усі
			{
				for ($i=0; $i<$countRows; $i++)
				{
					$rowsDo[]=$rows[$i];
				}
			}
			if ($type==null)//усі
			{
				for ($i=0; $i<$countRows; $i++)
				{
					$rowsDo[]=$rows[$i];
				}
			}
			

		$result = array('success' => true, 'rows' => $rowsDo );
		echo CJSON::encode($result);
		}
		public function actionCreate($id)//на посилання з post для заповнення точок контролю
		{
			$stationId = [];
			$routeid=Yii::app()->request->getParam('routeid');
			$idHis=Yii::app()->request->getParam('idHis');
			$StopsOrEvent=Yii::app()->request->getParam('StopsOrEvent');

			


//шукаемо перелік stops
			$a = RouteTimeTable::model()->findAll(array(
				'condition'=> 'routes_history_id = :rhid',
				'params'   => array(':rhid' => $idHis),
				'order'    => 'Id'));
			foreach ($a as $aa) 
			{
				$stationId[]=$aa->stations_id;
				$stationNumber[]=$aa->number;
			}
			$ca=count($stationId);

			for ($i=0; $i<$ca; $i++)
			{
				$arrayStationRoute[$stationNumber[$i]-1]=$stationId[$i];
			}

			//сортуємо за ключами на випадок виїзду не з першої точки
			ksort($arrayStationRoute);
			//шукаемо максимальне значення номеру зупинки
			$maxNumStop=0;
			for ($i=0; $i < $ca; $i++) { 
				if ($stationNumber[$i]>=$maxNumStop) {
					$maxNumStop=$stationNumber[$i];
				}
			}
			//echo $maxNumStop;
			//шукаемо ім*я точки за її ід
			//$cf=count($arrayStationRoute);
			//for($i=0; $i<$cf; $i++)
			for($i=0; $i<$maxNumStop; $i++)
			{
				$d = Stations::model()->findByAttributes(array('id'=>$arrayStationRoute[$i]));
				$title=$d->name;
				$f[$i]['title']=$title;
				$f[$i]['stationId']=$arrayStationRoute[$i];
			}
			if ($StopsOrEvent==Yii::app()->session['OnPointsOfEvents']) {
				$e=StationsScenario::model()->findAll(array(
					'condition'=> 'routes_id = :rid',
					'params'   => array(':rid' => $routeid),
					'order'    => 'number'));
				foreach ($e as $ee) {
					$status=$ee->pc_status;
					if ($status=='yes') {
						$arrEventInStops[]=$ee->stations_id;
					}
				} 
				$countPc=count($arrEventInStops);
				$countF=count($f);
				foreach ($arrEventInStops as $key => $value) {
					for ($i=0; $i < $countF; $i++) { 
						if ($value==$f[$i]['stationId']) 
						{
							$fff[]=array('title'=>$f[$i]['title'],'stationId'=>$f[$i]['stationId']);
						}
					}
				}
				$ff=$fff;
			}
			if ($StopsOrEvent!=Yii::app()->session['OnPointsOfEvents']) {
				$ff=$f;
			}
			
			$result = array('success' => true, 'rows'=>$ff );
			echo CJSON::encode($result);
		}
	}
?>

