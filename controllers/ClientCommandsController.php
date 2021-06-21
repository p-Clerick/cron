<?php
class ClientCommandsController extends Controller {
	public function actionCreate() {
		
		$idCommandsForStore=Yii::app()->request->getParam('idCommandsForStore');
		$idSelectionForCommand=Yii::app()->request->getParam('idSelectionForCommand');
		$client_commands=Yii::app()->request->getParam('client_commands');
		$params=Yii::app()->request->getParam('params');


		if ($idCommandsForStore==1) {//"борту(ів)"
			$arrayBorts=explode(",", $idSelectionForCommand);
			for ($i=0; $i <count($arrayBorts); $i++) { 
				$commandsInsert= new ClientSendCommandsLoad;
				$commandsInsert->borts_id=$arrayBorts[$i];
				$commandsInsert->client_commands=$client_commands;
				$commandsInsert->client_send_commands_history_id=0;
				$commandsInsert->params=$params;
				$commandsInsert->save();
			}
		}
		if ($idCommandsForStore==2) {//"маршруту(ів)"
			$arrayRoutes=explode(",", $idSelectionForCommand);
			$today=date('Y-m-d');
			$todayPlusOneHour=$today." 02:00:00";
			for ($i=0; $i <count($arrayRoutes); $i++) {
				//шукаємо всі борти, які сьогодні їздять по даному маршруті
				$a=MoveOnMap::model()->findAll(array(
					'condition'=> 'datatime >= :t AND routes_id = :r',
					'params'   =>array(':t'=>$todayPlusOneHour, ':r'=>$arrayRoutes[$i]),
					'order'    => 'borts_id'
				));
				foreach ($a as $k) {
					$arrayBorts[]=$k->borts_id;
				}
			}
			for ($i=0; $i <count($arrayBorts); $i++) { 
				$commandsInsert= new ClientSendCommandsLoad;
				$commandsInsert->borts_id=$arrayBorts[$i];
				$commandsInsert->client_commands=$client_commands;
				$commandsInsert->client_send_commands_history_id=0;
				$commandsInsert->params=$params;
				$commandsInsert->save();
			}
		}
		if ($idCommandsForStore==3) {//"перевізника(ів)"
			$arrayCarriers=explode(",", $idSelectionForCommand);
			for ($i=0; $i <count($arrayCarriers); $i++) {
				//шукаемо всі парки, що належать перевізнику
				$a=Parks::model()->findAll(array(
					'condition'=> 'carriers_id = :c',
					'params'   =>array(':c'=>$arrayCarriers[$i]),
					'order'    => 'id'
				));
				foreach ($a as $k) {
					$arrayParks[]=$k->id;
				}
			}
			for ($i=0; $i <count($arrayParks); $i++) {
				$b=Borts::model()->findAll(array(
					'condition'=> 'parks_id = :p',
					'params'   =>array(':p'=>$arrayParks[$i]),
					'order'    => 'id'
				));
				foreach ($b as $k) {
					$arrayBorts[]=$k->id;
				}
			}
			for ($i=0; $i <count($arrayBorts); $i++) { 
				$commandsInsert= new ClientSendCommandsLoad;
				$commandsInsert->borts_id=$arrayBorts[$i];
				$commandsInsert->client_commands=$client_commands;
				$commandsInsert->client_send_commands_history_id=0;
				$commandsInsert->params=$params;
				$commandsInsert->save();
			}
		}
		$result = array('success' => true, 'rows' => "Команду внесено для ".count($arrayBorts)." бортів");
		echo CJSON::encode($result);
	}
	public function actionRead() {
		$typeSelect=Yii::app()->request->getParam('typeSelect');
		if ($typeSelect==1) {//borts
			$a=Borts:: model()->findAll();
			foreach ($a as $k) {
				$d[$k->id]=$k->number." - ".$k->state_number;
			}
			natsort($d);
			foreach ($d as $key => $value) {
				$rows[]=array(
					'name'=>$value,
					'id'=>$key
				);
			}
		}

		if ($typeSelect==2) {//routes
			$a=Route:: model()->findAll();
			foreach ($a as $k) {
				$d[$k->id]=$k->name;
			}
			natsort($d);
			foreach ($d as $key => $value) {
				$rows[]=array(
					'name'=>$value,
					'id'=>$key
				);
			}
		}
		if ($typeSelect==3) {//carriers
			$a=Carriers:: model()->findAll();
			foreach ($a as $k) {
				$d[$k->id]=$k->name;
			}
			natsort($d);
			foreach ($d as $key => $value) {
				$rows[]=array(
					'name'=>$value,
					'id'=>$key
				);
			}
		}
		$result = array('success' => true, 'rows'=>$rows);
		echo CJSON::encode($result);
	}
}
?>