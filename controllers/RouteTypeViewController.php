<?php
	class RouteTypeViewController extends Controller
	{
		public function actionRead($id)//на посилання з гет
		{
			$metaData = array(
				'idProperty' => 'name',
				'root' => 'rows',
				'totalProperty' => 'results',
				'successProperty' => 'success',
				);	
			$fields[] = array(
				'name' => 'name');
			$a = RouteHistoryIdAll::model()->findByAttributes(array('Id'=>$id));
			$N=$a->amount;
			$rows[]=array('name'=>Yii::app()->session['AllText']);
			for ($i=0; $i<$N; $i++)
			{
				$k=$i+1;
				$h=$k."  ".Yii::app()->session['GrafikTextSmall'];
				$rows[]=array('name'=>$h);
			}
			$result = array('success' => true, 'rows' => array(), );
			$metaData['fields'] = $fields;
			$result['metaData'] = $metaData;		
			$result['rows'] = $rows;
			

			echo CJSON::encode($result);
		}
	}
?>	