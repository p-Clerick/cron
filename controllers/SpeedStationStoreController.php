<?php
class SpeedStationStoreController extends Controller {
	public function actionRead() {
		$a=Stations::model()->findAll();
		foreach ($a as $k) {
			$rowsName[$k->id]=$k->name;
		}
		natsort($rowsName);
		foreach ($rowsName as $key => $value) {
			$rows[]=array(
				'id'=>$key,
				'name'=>$value
			);
		}
		$countRows=count($rows);
		$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows);
		echo CJSON::encode($result);
	}
}
?>