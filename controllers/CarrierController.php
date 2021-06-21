<?php

class CarrierController extends Controller
{
	public function actionRead () {
		$result = array();
		if( Yii::app()->user->checkAccess('readCarrier') ){
			$carriers = Carriers::model()->findAll();
			$rows = array();
			foreach ($carriers as $item) {
				$rows[] = array(
					'id' => $item->id,
					'name' => $item->name
				);
			}
			$result = array(
				'success' => true,
				'rows' => $rows,
			);
		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}
		echo CJSON::encode($result);
	}

	public function actionCarriersName () {
		$result = array();
		$carriers = Carriers::model()->findAll();
		$rows = array();
		foreach ($carriers as $item) {
			$rows[] = array(
				'carriers_id' => $item->id,
				'carriers_name' => $item->name
			);
		}
		$result = array(
			'success' => true,
			'data' => $rows,
		);

		echo CJSON::encode($result);
	}	
}