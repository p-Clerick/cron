<?php
class StopsController extends Controller
{
	public function actionStops(){
      	if (isset($_POST)){
		    if($_POST['level'] === '1'){
                $sql = Stops::model()->findAll(array('order' => 'id'));
                $res =  CJSON::encode(array(
                'success'=>'true',
                'data'=>$sql
                ));
                echo $res;
		    }
      	}
	}
	public function actionDelete($id){
		if( Yii::app()->user->checkAccess('deleteStops') ){
			$sql=Stops::model()->deleteByPk($id);
			$res =  CJSON::encode(array(
                'success'=>'true'
            ));
            echo $res;
		}else {
			echo "{success: false, msg: 'Not permitted!}";
		}
	}
    public function actionRead(){
        if($_GET['level'] === '1'){
            $sql = Stops::model()->findAll(array('order' => 'id'));
            $res =  CJSON::encode(array(
            	'success'=>'true',
            	'data'=>$sql
            ));
            echo $res;
	    }
	}
    public function actionUpdate(){
		if( Yii::app()->user->checkAccess('updateStops') ){
			$data = json_decode(Yii::app()->request->getPut('data'),true);
	        $sql=Stops::model()->findByPk($data['id']);
        	while (list($key, $value) = each($data)) {
				$sql->$key=$value;
			}
			$sql->save();
	        $res =  CJSON::encode(array(
		        'success'=>'true',
		        'data'=>$data
	        ));
	        echo $res;
        }else {
			echo "{success: false, msg: 'Not permitted!}";
		}
	}
	public function actionCreate(){
		if( Yii::app()->user->checkAccess('createStops') ){
		   $data = json_decode(Yii::app()->request->getPost('data'),true);
		   $sql = new Stops;
			while (list($key, $value) = each($data)) {
				$sql->$key=$value;
			}
			$sql->save();
        	$res =  CJSON::encode(array(
        		'success'=>'true',
        		'data'=>$data
        	));
        	echo $res;
		} else {
			echo "{success: false, msg: 'Not permitted!}";
		}
	}
	public function actionStopsPoints()
    {
        $count = 0;
        $result = array(
			'points'=>array(),
			'count'=>array()
	    );
	  	if($_POST['level'] === '1'){
	  		$stops = Stops::model()->findAll(array(
       			'order' => 't.id'));
		}
	  	if($_POST['level'] === '2' or $_POST['level'] === '3'){
	  		$stops = Stops::model()->with('stopsscenario.points_control_scenario.route')->findAll(array(
				'condition'=>'route.id=:routid',
				'params'=>array(':routid'=>$_POST['nodeid']),
				'order' => 't.id'));
	  	}
        foreach($stops as $stop){
			$result['points'][] = array(
				'id'			=> $stop->id,
				'name'			=> $stop->name,
				'latitude'	 	=> (double) (floor($stop->latitude/100)*100+(($stop->latitude   - floor($stop->latitude/100)*100)*100/60))/100,
				'longitude' 	=> (double) (floor($stop->longitude/100)*100+(($stop->longitude - floor($stop->longitude/100)*100)*100/60))/100,
			);
            $count++;
        }
        $result['count'] = $count;
        echo json_encode($result);
	}
}
?>