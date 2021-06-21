<?php
class StationRouteController extends Controller {
	public function actionRead($id) {//на посилання з гет
		
		$arraySR = array();
		$routeid=Yii::app()->request->getParam('routeid');

		$a=StationsScenario::model()->findAll(array(
			'condition'=> 'routes_id = :rid',
			'params'   =>array(':rid' => $routeid),
			'order'    => 'number'));
		foreach ($a as $aa) {
			$arraySR[]=array(
				'id'=>$aa->stations_id,
				'number'=>$aa->number
			);
		}
		$countSR=count($arraySR);
		if ($countSR==0) {
			$result = array(
				'success' => false,
				'msg' => Yii::app()->session['RouteHasNoStops'],
			);
		}
		else if ($countSR!=0) {
			for ($i=0; $i < $countSR; $i++) { 
				$b = Stations::model()->findByAttributes(array('id'=>$arraySR[$i]['id']));
				$arraySR[$i]['title']=$b->name;
			}
			$result = array('success' => true, 'rows' => $arraySR );
		}
		
		echo CJSON::encode($result);

	}//public function actionRead()

}
?>