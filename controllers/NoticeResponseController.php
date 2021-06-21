<?php
class NoticeResponseController extends Controller
{
	public $result = array(
		'success'=>"true",
		'data'=>array()
	);	
    public function actionRead(){
		$this->result['total'] = NoticeResponse::model()->count();
        $notices = NoticeResponse::model()->findAll(array(
        	'order' => 't.id', 
        	'limit' => $_GET['limit'],
        	'offset' => $_GET['start']));   	
    	foreach($notices as $notice){
			$this->result['data'][] = array(
				'id'			=> $notice->id,
				'notice_id' 	=> $notice->notifications_id,
				'notice_value'	=> $notice->value
			);
        }
        echo CJSON::encode($this->result);
	}
    public function actionUpdate(){
		$data = json_decode(Yii::app()->request->getPut('data'),true);
		if( Yii::app()->user->checkAccess('updateNotificationsResponses') ){
	        $notice_response=NoticeResponse::model()->findByPk($data['id']);
	        $notice_response->notifications_id = (isset($data['notice_id'])) ? $data['notice_id'] : $notice_response->notifications_id;
	        $notice_response->value = (isset($data['notice_value'])) ? $data['notice_value'] : $notice_response->value;
	        $notice_response->save();
	        $res =  CJSON::encode(array(
		        'success'=>true,
		        'message'=>Yii::app()->session['RecordUpdated'],
		        'data'=>$data
	        ));
	    }
	    else{
			$res =  CJSON::encode(array(
		        'success'=>true,
		        'message'=>Yii::app()->session['AccessDenied'],
	        ));	    	
	    }    	
    	echo $res;

	}
	public function actionCreate(){
	    $data = json_decode(Yii::app()->request->getPost('data'),true);
	    if (isset($data) and (count($data) > 1)){
	    	if (Yii::app()->user->checkAccess('createNotificationsResponses')){		   
			    $notice_response = new NoticeResponse;
			    $notice_response->notifications_id = $data['notice_id'];
			    $notice_response->value = $data['notice_value'];
			    $notice_response->save();
	        	$res =  CJSON::encode(array(
	        		'success'=>true,
	        		'data'=>$data,
	        		'message'=> Yii::app()->session['RecordAdded'],
	        	));
	        }
	        else{
	        	$res =  CJSON::encode(array(
	        		'success'=>true,
	        		'data'=>$data,
	        		'message'=> Yii::app()->session['AccessDenied'],
	        	));	        	
	        }
        	echo $res;
        }
	}
	public function actionDelete($id){
		if ( Yii::app()->user->checkAccess('deleteNotificationsResponses')){		
			$sql=NoticeResponse::model()->deleteByPk($id);
			$res =  CJSON::encode(array(
	            'success'=>true,
	            'message'=> Yii::app()->session['RecordDeleted'],
	        ));
	    }
	    else{
			$res =  CJSON::encode(array(
	            'success'=>true,
	            'message'=> Yii::app()->session['AccessDenied'],
	        ));	    	
	    }
        echo $res;		
	}
}
?>
