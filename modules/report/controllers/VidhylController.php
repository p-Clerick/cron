<?php

Yii::import('application.vendor.*');
require_once('underscore/Underscore.php/underscore.php');

class VidhylController extends CController
{
	public function actionRead($level, $node, $from, $to){
		$result = array();
		if( Yii::app()->user->checkAccess('getVidhylReport') ) {
			$request = Yii::app()->getRequest();

			$limit = $request->getParam('limit', 200);
			$start = $request->getParam('start', 0);

			$fields = implode(',', array(
				'item.borts_id as bort',
				'item.flight_number as flight',
				'item.time_difference as diff',
				'item.schedules_id as schedule',
				'bort.number as bort_number',

				'DATE(item.datatime) as date',
				'TIME(item.datatime) as time',

				'point.name as point_name',
				'graph.name as graph_name',
				'route.name as route_name',
				'vtype.name as vtype_name',

				'graph.id as graph_id',
				'route.id as route_id',
				'vtype.id as vtype_id',
			));

			$items = $this->createCommand($level, $node, $from, $to, $limit, $start)
				->select($fields)
				->queryAll();

			$totalCount = $this->createCommand($level, $node, $from, $to, $limit, $start)
				->select('COUNT(*) as value, DATE(item.datatime) as date')
				->queryRow();

			$result['success']    = true;
			$result['totalCount'] = $totalCount['value'];

			$result['rows'] = __($items)->map(function ($item) {
				return array(
					'date'  => $item['date'],
					't_N_t' => $item['time'],
					'r_n'   => $item['flight'],
					't_rizn'=> $item['diff'],
					'name'  => $item['point_name'],
					'graf'  => $item['graph_name'],
					'race'  => $item['route_name'],
					'bort'  => $item['bort_number'],
				);
			});
		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}
		echo CJSON::encode($result);
	}

	protected function createCommand ($level, $node, $from, $to, $limit, $start) {
		$command = Yii::app()->db->createCommand()
			->from('move_on_schedule_total as item')
			->join('points_control_scenario as sc', 'item.points_control_scenario_id = sc.id')
			->join('points_control as point', 'sc.points_control_id = point.id')
			->join('schedules as schedule', 'item.schedules_id = schedule.id')
			->join('graphs as graph', 'schedule.graphs_id = graph.id')
			->join('routes as route', 'graph.routes_id = route.id')
			->join('borts as bort', 'item.borts_id = bort.id')
			->join('models as model', 'bort.models_id = model.id')
			->join('transport_types as vtype', 'model.transport_types_id = vtype.id')
			->order('date')
			->limit($limit, $start);

		switch ($level) {
		case Tree::LEVEL_VEHICLE :
			$command->where('vtype.id = :vtype AND DATE(datatime) BETWEEN :from AND :to', array(
				'from'  => $from,
				'to'    => $to,
				'vtype' => $node,
			));
			break;
		case Tree::LEVEL_ROUTE :
			$command->where('route.id = :route AND DATE(datatime) BETWEEN :from AND :to', array(
				'from'  => $from,
				'to'    => $to,
				'route' => $node,
			));
			break;
		case Tree::LEVEL_SCHEDULE :
			$command->where('graph.id = :graph AND DATE(datatime) BETWEEN :from AND :to', array(
				'from'  => $from,
				'to'    => $to,
				'graph' => $node,
			));
			break;
		default : throw new Exception("Unknown tree level", 1);	}

		return $command;
	}
}
