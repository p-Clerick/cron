<?php
	class PointDistanseController extends Controller
	{
		public function actionRead()
		{
			$pointNameFrom=Yii::app()->request->getParam('pointNameFrom');

//шукаемо назви точок і їх ідентифікатори
			$a = PointsControl::model()->findAll();
			foreach ($a as $aa) 
			{
				$id=$aa->id;
				$name=$aa->name;
				$arrayPointName[]=array(
					'id'=>$id,
					'name'=>$name
				);
			}
			//print_r($arrayPointName);
			$countPoint=count($arrayPointName);
			//для кожної точки формуємо ровс
			for ($i=0; $i < $countPoint; $i++) 
			{ 
				$rows[]=array(
					'pointNameTo'=>$arrayPointName[$i]['name'],
					'pointIdTo'=>$arrayPointName[$i]['id']
				);
			}
			$countRows=count($rows);
			
			for ($a=0; $a < $countRows; $a++) 
			{ 
				$c = DistansePoint::model()->findByAttributes(array(
					'point_id_from'=>$pointNameFrom,
					'point_id_to'=>$rows[$a]['pointIdTo'])
				);
				if (!isset($c))
				{
					$d= new DistansePoint;
					$d->point_id_from=$pointNameFrom;
					$d->point_id_to=$rows[$a]['pointIdTo'];
					$d->distanse=0;
					$d->save();
				}
			}

			$b = DistansePoint::model()->findAll(array(
				'condition'=> 'point_id_from = :from',
				'params'   => array(':from' => $pointNameFrom),
				'order'    => 'Id'));
			foreach ($b as $bb)
			{
				$Id=$bb->Id;
				$pointTo=$bb->point_id_to;
				$distanse=$bb->distanse;
				$arrayDistanse[]=array(
					'id'=>$Id,
					'pointFrom'=>$pointNameFrom,
					'pointTo'=>$pointTo,
					'distanse'=>$distanse
				);
			}

			$countArrayDistanse=count($arrayDistanse);
			for ($i=0; $i < $countRows; $i++) 
			{ 
				for ($a=0; $a < $countArrayDistanse; $a++) 
				{ 
					if ($rows[$i]['pointIdTo']==$arrayDistanse[$a]['pointTo'])
					{
						$rows[$i]['distanse']=$arrayDistanse[$a]['distanse'];
						$rows[$i]['number']=$i+1;
					}
				}
			}
			$result = array('success' => true, 'rows' => $rows);
			echo CJSON::encode($result);
		}

		public function actionCreate()
		{
			$pointIdFrom=Yii::app()->request->getParam('pointIdFrom');
			$pointIdTo=Yii::app()->request->getParam('pointIdTo');
			$distanse=Yii::app()->request->getParam('distanse');
			$rows[]= array(
				'pointIdFrom'=>$pointIdFrom,
				'pointIdTo'=>$pointIdTo,
				'distanse'=>$distanse
			);

			$a = DistansePoint::model()->findByAttributes(array(
				'point_id_to'=>$pointIdTo,
				'point_id_from'=>$pointIdFrom));
			$a->distanse=$distanse;
			$a->save();
			
			$result = array('success' => true, 'rows' => $rows);
			echo CJSON::encode($result);
		}	
	}
?>