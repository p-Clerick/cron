<?php
class NoticeController extends Controller
{
	public $notice_static = 1;
	public $result = array(
		'success'=>"true",
		'data'=>array()
	);	
    public function actionRead(){
		$this->result['total'] = Notice::model()->count();
        $notices = Notice::model()->with('notice_type')->findAll(array(
        	'order' => 't.id', 
        	'limit' => $_GET['limit'],
        	'offset' => $_GET['start']));    	
        foreach($notices as $notice){
			$this->result['data'][] = array(
				'id'			=> $notice->id,
				'notice_name' 	=> $notice->name,
				'notice_header'	=> $notice->header,
				'notice_type_id' => $notice->notice_type->id
			);
        }
        echo CJSON::encode($this->result);
	}
    public function actionUpdate(){
		$data = json_decode(Yii::app()->request->getPut('data'),true);
		if( Yii::app()->user->checkAccess('updateNotifications') ){
	        $notice=Notice::model()->findByPk($data['id']);
	        $notice->name = (isset($data['notice_name'])) ? $data['notice_name'] : $notice->name;
	        $notice->header = (isset($data['notice_header'])) ?$data['notice_header'] : $notice->header;
	        $notice->notifications_types_id = (isset($data['notice_type_id'])) ?$data['notice_type_id'] : $notice->notifications_types_id;
	        $notice->save();
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
	    if (isset($data) and (count($data) >1)){
	    	if( Yii::app()->user->checkAccess('createNotifications') ){	   
			    $notice = new Notice;
			    $notice->name = $data['notice_name'];
			    $notice->header = $data['notice_header'];
			    $notice->notifications_types_id = $data['notice_type_id'];
			    $notice->save();
	        	$res =  CJSON::encode(array(
	        		'success'=>true,
	        		'data'=>$data,
	        		'message'=>Yii::app()->session['RecordAdded'],
	        	));
	        }
	        else{
				$res =  CJSON::encode(array(
	        		'success'=>true,
	        		'data'=>$data,
	        		'message'=>Yii::app()->session['AccessDenied']
	        	));	        	
	        }
	        echo $res;
        }
	}
	public function actionDelete($id){
		if( Yii::app()->user->checkAccess('deleteNotifications') ){
			$sql=Notice::model()->deleteByPk($id);
			$res =  CJSON::encode(array(
	            'success'=>true,
	            'message'=>Yii::app()->session['RecordDeleted'],
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
    public function actionGetNotice(){
		$this->result['total'] = Notice::model()->count();
        $notices = Notice::model()->findAll(array(
        	'order' => 't.id'));    	
        foreach($notices as $notice){
			$this->result['data'][] = array(
				'notice_id'		=> $notice->id,
				'notice_name' 	=> $notice->name,
				'notice_header'	=> $notice->header,
				'notice_type_id'	=> $notice->notifications_types_id
			);
        }
        echo CJSON::encode($this->result);
	}
    public function actionGetStaticNotice(){
		$this->result['total'] = Notice::model()->count();
        $notices = Notice::model()->findAll(array(
        	'condition'=>'t.notifications_types_id=:ntid',
	        'params'=>array(':ntid' => $this->notice_static),
        	'order' => 't.id'));    	
        foreach($notices as $notice){
			$this->result['data'][] = array(
				'notice_id'		=> $notice->id,
				'notice_name' 	=> $notice->name,
				'notice_header'	=> $notice->header,
				'notice_type_id'	=> $notice->notifications_types_id
			);
        }
        echo CJSON::encode($this->result);
	}	
    public function actionGetNoticeType(){
		$this->result['total'] = Notice::model()->count();
        $notices_types = NoticeType::model()->findAll(array(
        	'order' => 't.id'));    	
        foreach($notices_types as $notice_type){
			$this->result['data'][] = array(
				'notice_type_id'		=> $notice_type->id,
				'notice_type_name' 		=> $notice_type->name
			);
        }
        echo CJSON::encode($this->result);
	}		
}
?>
