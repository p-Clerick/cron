<?php
Yii::import('application.models.*');
class fdingCommand extends CConsoleCommand
{
	public function run($days) {
	/*	$nnp=35;
		$y=StationsScenario::model()->findAll(array(
			'condition'=> 'routes_id = :b',
			'params'   =>array(':b'=>20),
			'order'=>'number'));
		foreach ($y as $k) {
			echo $k->number;
			if ($k->number>=2) {
				if ($k->number<=32){
					$q=new StationsScenario;
					$q->routes_id=103;
					$q->stations_id=$k->stations_id;
					$q->number=$nnp;
					$q->route_directions_id=$k->route_directions_id;
					$q->points_of_events_id=$k->points_of_events_id;
					$q->pc_status=$k->pc_status;
					$q->save();
					$nnp=$nnp+1;
				}
			}
		}*/

		/*
	промальовка маршруту нитки


		$countDays=count($days);
		for ($i=0; $i < $countDays; $i++) {
			$num=1;
			$unix11=strtotime($days[$i])+9*3600+7*60+37;
			$unix21=strtotime($days[$i])+9*3600+55*60+27;
			$a=Locations::model()->findAll(array(
							'condition'=> 'unixtime >= :f AND unixtime <= :t AND borts_id = :b',
							'params'   =>array(':f'=>$unix11, ':t'=>$unix21, ':b'=>40),
							'order'=>'id'));
			foreach ($a as $k) {
				$ghy=new WayPoints;
				$ghy->routes_id=102;
				$ghy->number=$num;
				$ghy->longitude=$k->longitude;
				$ghy->latitude=$k->latitude;
				$ghy->save();
				$num=$num+1;
			}
		}*/

		
	//звірка дублювання бортів за один день на різних графіках
	$countDays=count($days);
		for ($i=0; $i < $countDays; $i++) {

		$unix11=strtotime($days[$i])+3600*2;
		$unix21=strtotime($days[$i])+(26*3600);
	
		$loc=LocationsFlight::model()->with('route', 'graph')->findAll(array(
						'select'=>'t.graphs_id, t.borts_id',
						'condition'=> 't.unixtime >= :f AND t.unixtime <= :t',
						'params'   =>array(':f'=>$unix11, ':t'=>$unix21),
						'order'=>'t.id'));	
		foreach ($loc as $key) {
			$arLoc[$key->borts_id][$key->graphs_id]=$key->route->name."/".$key->graph->name;
		}
		foreach ($arLoc as $b => $arb) {
			if (count($arb)>1) {
				echo "borts_id___";echo $b; echo "___";
				print_r($arb);echo "\r\n"; 
			}
		}
	}	


	
echo "end";

    }
}
?>