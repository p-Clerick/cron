<?php
class ContentFilesController extends Controller
{
	public function actionStScPoeGet(){
        if (isset($_GET)){
	        if($_GET['level'] === '2'){
	           	$stop = StationsScenario::model()->with('stations','route_directions','poe','stations_playback')->findAll(array(
	   				'condition'=>'t.routes_id=:routeid',
	   				'params'=>array(':routeid'=>$_GET['nodeid']),
	   				'order' => 't.number')
	   			);
	   			$result = array(
					'success'=>true,
					'data'=>array(),
				);
	            foreach($stop as $stations){
					$result['data'][] = array(
						'id'						=> $stations->stations_playback->id,
						'stations_scenario_id'		=> $stations->id,
						'stations_scenario_number'	=> $stations->number,
						'stations_scenario_name'	=> $stations->stations->name,										
						'route_directions_name'		=> $stations->route_directions->name,
						'poe_name'					=> $stations->poe->name,
						'content_files_id'			=> $stations->stations_playback->content_files_id,
					);
	            }
	            echo json_encode($result);          	
	      	}
		}
	}	
}
?>