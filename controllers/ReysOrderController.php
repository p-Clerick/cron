<?php
	class ReysOrderController extends Controller
	{
		public function actionRead()//
		{
			$routeid=Yii::app()->request->getParam('routeid');
			$idHis=Yii::app()->request->getParam('idHis');
			$typeView=Yii::app()->request->getParam('typeView');

			$metaData = array(
				'idProperty' => 'id',
				'root' => 'rows',
				'totalProperty' => 'results',
				'successProperty' => 'success',
				);
			$fields[] = array(
				'name' => 'id');		
			$fields[] = array(
				'name' => 1);
			$fields[] = array(
				'name' => 2);
			$fields[] = array(
				'name' => 3);
			$fields[] = array(
				'name' => 'shiftChange');
			$fields[] = array(
				'name' => 'graphNumber');
			$fields[] = array(
				'name' => 'pointNumber');
			$fields[] = array(
				'name' => 'tr');

			$a = RouteTimeTable::model()->findAll(array(
				'condition' => 'routes_history_id = :rid',
				'params'    => array(':rid' => $idHis),
				'order'     => 'id')); 
			foreach ($a as $aa)
			{
				$graphNumber=$aa->graphs_number;
				$flightNumber=$aa->flights_number;
				$time=$aa->time;
				$pointNameId=$aa->stations_id;
				$pointScId=$aa->stations_scenario_id;
				$pointNumber=$aa->number;
				$dstart=$aa->dinner_start;
				$dend=$aa->dinner_end;
				$ws=$aa->workshift_start;
				if ($flightNumber>2)
				{
					if ($time<4*3600)
					{
						$time=$time+24*3600;
					}
				}
				$arrayTime[]=array(
					'routeid'=>$routeid,
					'idHis'  =>$idHis,
					'graphNumber'=>$graphNumber,
					'flightNumber'=>$flightNumber,
					'time'=>$time,
					'pointNameId'=>$pointNameId,
					'pointScId'=>$pointScId,
					'pointNumber'=>$pointNumber,
					'ds'=>$dstart,
					'de'=>$dend
				);
				if (isset($ws)) {
					$u= new Time($time);
					$shifts[]=array(
						'routeid'=>$routeid,
						'idHis'  =>$idHis,
						'graphNumber'=>$graphNumber,
						'flightNumber'=>$flightNumber,
						'time'=>$u->getFormattedTime(),
						'pointNameId'=>$pointNameId,
						'pointScId'=>$pointScId,
						'pointNumber'=>$pointNumber
					);
				}
			} 
			//print_r($arrayTime);
			$countShifts=count($shifts);
			//шукаемо точки по сценарію
			$b = StationsScenario::model()->findAll(array(
				'condition' => 'routes_id = :rid',
				'params'    => array(':rid' => $routeid),
				'order'     => 'number')); 
			foreach ($b as $bb) 
			{
				$number=$bb->number;
				$namePcId=$bb->stations_id;
				$scenarioId=$bb->id;
				$arrayPC[]=array(
					'idScensrio'=>$scenarioId,
					'number'=>$number,
					'nameId'=>$namePcId
				);
			}
			//print_r($arrayPC);

			//шукаемо назви букенні точок
			$c = Stations::model()->findAll();
			foreach ($c as $cc) 
			{
				$id=$cc->id;
				$name=$cc->name;
				$arrayNamePoint[]=array('id'=>$id, 'name'=>$name);
			}
			//print_r($arrayNamePoint);

			$countArrayTime=count($arrayTime);//всього записфів в таблиці таймтебле
			$countArrayPC=count($arrayPC);//всього точок
			$countArrayNamePoint=count($arrayNamePoint);//всього назв точок

			//шукаємо відстань
			for ($i=0; $i < $countArrayPC; $i++) 
			{ 
				if ($i==0)
				{
					$arrayPC[0]['distanse']=0;
				}
				if ($i!=0)
				{
					$dist = DistanceStations::model()->findByAttributes(array(
						'stations_id_from'=>$arrayPC[$i-1]['nameId'],
						'stations_id_to'=>$arrayPC[$i]['nameId']
					));
					$arrayPC[$i]['distanse']=($dist->distance_in_meters)/1000;
				}
			}
			for ($i=0; $i < $countArrayPC; $i++)
			{
				$arrayPC[$i]['distanse']=$arrayPC[$i-1]['distanse']+$arrayPC[$i]['distanse'];
			}
			//всовуємо імя буквене точки в всього записфів в таблиці таймтебле
			for ($i=0; $i < $countArrayTime; $i++) 
			{ 
				for ($a=0; $a < $countArrayNamePoint; $a++) 
				{ 
					if ($arrayTime[$i]['pointNameId']==$arrayNamePoint[$a]['id'])
					{
						$arrayTime[$i]['pointName']=$arrayNamePoint[$a]['name'];
					}
				}
			}
			//print_r($arrayTime);

			//для кожного графіка формуемо рядки з переліком точок
			for ($i=0; $i < $arrayTime[$countArrayTime-1]['graphNumber']; $i++)
			{
				for ($a=0; $a < $countArrayPC; $a++) 
				{ 
					$t=$a+$countArrayPC*$i;
					$rows[$t]=array(
						'graphNumber'=>$i+1,
						'pointNumber'=>$a+1,
						3=>$arrayPC[$a]['distanse']
					);
				}
			}
			//print_r($rows);

			//для кожного запису аррейтайм вставляемо інтервал до попередньої точки
			for ($i=0; $i < $countArrayTime; $i++) 
			{
				if ($arrayTime[$i]['graphNumber']==$arrayTime[$i-1]['graphNumber'])
				{
					$arrayTime[$i]['dur']=$arrayTime[$i]['time']-$arrayTime[$i-1]['time'];
					if (isset($arrayTime[$i]['ds']))
					{
						$arrayTime[$i+1]['isDinner']=1;
					}
				}
			}
			//print_r($arrayTime);
			
			//для кожного запису в ровс вставлямо часи
			$countrows=count($rows);
			for ($i=0; $i < $countArrayTime; $i++) 
			{ 
				for ($a=0; $a <$countrows ; $a++) 
				{ 
					if ($rows[$a]['graphNumber']==$arrayTime[$i]['graphNumber'])
					{
						if ($rows[$a]['pointNumber']==$arrayTime[$i]['pointNumber'])
						{
							$rows[$a][1]=$rows[$a]['graphNumber'];
							$rows[$a][2]=$arrayTime[$i]['pointName'];
							//$rows[$a][3]=0;
							if ($arrayTime[$i]['isDinner']==1)
							{
								$qq=round($arrayTime[$i]['dur']/60,2);
							}
							else if ($arrayTime[$i]['isDinner']!=1)
							{
								if ($arrayTime[$i]['dur']>60)
								{
									$qq=1;
								}
								if ($arrayTime[$i]['dur']<=60)
								{
									$qq=round($arrayTime[$i]['dur']/2/60,2);
								}
							}
							$w=$arrayTime[$i]['time']-$qq*60;
							$q= new Time($w);
							$qqq = new Time($arrayTime[$i]['time']);
							$rows[$a][$arrayTime[$i]['flightNumber']*10+1]=$q->getFormattedTime();
							$rows[$a][$arrayTime[$i]['flightNumber']*10+2]=$qq;
							$rows[$a][$arrayTime[$i]['flightNumber']*10+3]=$qqq->getFormattedTime();
						}
					}
				}
			}
			//print_r($rows);

			//якщо потрібний тільки один графік
			if ($typeView !=Yii::app()->session['AllText'])
			{
				$newType=explode(" ", $typeView);
				$Type=$newType[0];
				for ($a=0; $a <$countrows ; $a++)
				{
					if ($rows[$a]['graphNumber']==$Type)
					{
						$Newrows[]=$rows[$a];
					}
					unset($rows[$a]);					
				}
				$countNewRows=count($Newrows);
				for ($i=0; $i < $countNewRows; $i++) 
				{ 
					$rows[$i]=$Newrows[$i];
				}
				//взнаемо кількість рейсів для графіку
				$maxFlight=1;
				for ($i=0; $i < $countArrayTime; $i++) 
				{ 
					if ($arrayTime[$i]['graphNumber']==$Type)
					{
						if ($arrayTime[$i]['flightNumber']>$maxFlight)
						{
							$maxFlight=$arrayTime[$i]['flightNumber'];
						}
					}
				}
			}
			else if ($typeView ==Yii::app()->session['AllText'])
			{
				$maxFlight=1;
				for ($i=0; $i < $countArrayTime; $i++) 
				{ 
					if ($arrayTime[$i]['flightNumber']>$maxFlight)
					{
						$maxFlight=$arrayTime[$i]['flightNumber'];
					}
				}
			}
			//echo $maxFlight;//максимальна кылькысть рейсыв

			for ($i=1; $i <= $maxFlight; $i++) 
			{ 
				$fields[] = array(
					'name' => $i*10+1);
				$fields[] = array(
					'name' => $i*10+2);
				$fields[] = array(
					'name' => $i*10+3);
			}
			$countrows=count($rows);
			for ($i=0; $i < $countrows; $i++) 
			{ 
				$rows[$i]['tr']=$rows[$i][3]-$rows[$i-1][3];
			}
			for ($i=0; $i < $countrows; $i++) 
			{ 
				if ($rows[$i]['graphNumber']==$rows[$i-1]['graphNumber'])
				{
					$rows[$i][3]=$rows[$i]['tr']."/".$rows[$i][3];
				}
				if ($rows[$i]['graphNumber']!=$rows[$i-1]['graphNumber'])
				{
					$rows[$i][3]=$rows[$i][3]."/".$rows[$i][3];
				}
			}
			for ($i=0; $i < $countrows; $i++) {
				$rows[$i]['id']=$i;
				for ($a=0; $a <$countShifts ; $a++) { 
					if ($rows[$i]['graphNumber']==$shifts[$a]['graphNumber']) {
						if ($rows[$i]['pointNumber']==$shifts[$a]['pointNumber']) {
							if ($rows[$i][$shifts[$a]['flightNumber']*10+3]==$shifts[$a]['time']) {
								$shiftArrayColor[]=array(
									'idRows'=>$i,
									'time'=>$shifts[$a]['time'],
									'graphNumber'=>$shifts[$a]['graphNumber']
								);
								$rows[$i]['shiftChange']=$shifts[$a]['time'];
							}
						}
					}
				}
			}
			//print_r($rows);
			$result = array('success' => true, 'rows' => array(), );
			$metaData['fields'] = $fields;
			$result['metaData'] = $metaData;		
			$result['rows'] = $rows;
			$result['shifts']=$shiftArrayColor;
			echo CJSON::encode($result);
		}
		public function actionCreate()//
		{
			$routeid=Yii::app()->request->getParam('routeid');
			$idHis=Yii::app()->request->getParam('idHis');
			$typeView=Yii::app()->request->getParam('typeView');



			if ($typeView ==Yii::app()->session['AllText'])
			{
				$b = RouteTimeTable::model()->findAll(array(
					'condition' => 'routes_history_id = :rid',
					'params'    => array(':rid' => $idHis),
					'order'     => 'Id')); 
				foreach ($b as $bb) 
				{
					$graphN=$bb->graphs_number;
					$reysN=$bb->flights_number;
					$arrayForReys[$graphN-1]=$reysN;
				}
			}
			else if ($typeView !=Yii::app()->session['AllText'])
			{
				$newType=explode(" ", $typeView);
				$Type=$newType[0];
				$b = RouteTimeTable::model()->findAll(array(
					'condition' => 'routes_history_id = :rid AND graphs_number = :gn',
					'params'    => array(':rid' => $idHis, ':gn' =>$Type),
					'order'     => 'Id')); 
				foreach ($b as $bb) 
				{
					$graphN=$bb->graphs_number;
					$reysN=$bb->flights_number;
					$arrayForReys[]=$reysN;
				}
			}
			$max=1;
			$countArForReys=count($arrayForReys);
			for ($i=0; $i < $countArForReys; $i++) 
			{ 
				if ($arrayForReys[$i]>$max)
				{
					$max=$arrayForReys[$i];
				}
			}
			for ($i=0; $i <= $max; $i++) 
			{ 
				$flight[]=array('flight'=>$i);
			}

			
			$result = array('success' => true, 'rows' =>$rows, 'flight' => $flight);
			echo CJSON::encode($result);
		}//public function actionCreate//
	}
?>