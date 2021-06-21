<?php
class PointsControlController extends Controller
{
	public function actionDelete($id){
		if( Yii::app()->user->checkAccess('deletePointsControl') ){
			$sql=PointsControl::model()->deleteByPk($id);
			$res =  CJSON::encode(array(
                'success'=>true,
                'msg'=> Yii::app()->session['RecordDeleted'],
            ));
            echo $res;
        }else {
			echo "{success: false, messsage: 'Not permitted'}";
		}
	}
    public function actionRead(){
       if($_GET['level'] === '1'){
            $sql = PointsControl::model()->findAll(array('order' => 'id'));
            $res =  CJSON::encode(array(
            'success'=>true,
            'data'=>$sql
            ));
            echo $res;
	   }
	}
    public function actionUpdate(){
		if( Yii::app()->user->checkAccess('updatePointsControl') ){
			$data = json_decode(Yii::app()->request->getPut('data'),true);

	        $sql=PointsControl::model()->findByPk($data['id']);
	        while (list($key, $value) = each($data)) {
				$sql->$key=$value;
			}
				$sql->save();
			$pcs = PointsControlScenario::model()->with('route')->findAll(array(
	            'condition'=>'t.points_control_id=:pcsid',                   					
				'params'=>array(':pcsid'=>$data['id']),
				'order'=>'route.id'
	        ));	        
	        foreach($pcs as $p){
				$contentchanges = ContentChanges::model()->with('route')->findAll(array(
		            	'select'=>'Id',
		                'condition'=>'t.types_of_content_changes_id=:toccid and t.routes_id=:routid',                   					
						'params'=>array(':toccid'=>ContentChanges::PCS,':routid'=>$p->route->id),
		        ));	        	
				$put=ContentChanges::model()->findByPk($contentchanges[0]['Id']);
				$put->users_id = Yii::app()->user->getId();				
				$put->routes_id = $p->route->id;
				$put->created = date ("Y-m-d H:i:s");
				$put->save();	        	
	        }      	        
	        $res =  CJSON::encode(array(
		        'success'=>true,
		        'msg'=> Yii::app()->session['RecordUpdated'],
		        'data'=>$data
	        ));
	        echo $res;
    	}else {
			echo '{"success": "false", "msg": "Not permitted!"}';
		}
	}
	public function actionCreate(){
		if( Yii::app()->user->checkAccess('createPointsControl') ){
			    $data = json_decode(Yii::app()->request->getPost('data'),true);
			    $sql = new PointsControl;
				while (list($key, $value) = each($data)) {
				$sql->$key=$value;
			}
			$sql->save();
            $res =  CJSON::encode(array(
            'success'=>'true'/*,
            'data'=>$data*/
            ));
            echo $res;
		} else {
				echo "{success: false, msg: 'Not permitted!}";
		}
	}
}
?>