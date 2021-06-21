<?php
class StationsPlaybackController extends Controller
{
	public function actionContentFileName(){
        if (isset($_POST)){
	        if($_POST['level'] === '2'){
            	$sql = ContentFiles::model()->findAll(array('select'=>'id,filename',
            		'condition'=>'t.file_support_types_id=:fstid and t.content_types_id=:ctid',
					'params'=>array(':fstid'=>1,':ctid'=>1),
            		'order' => 'filename'));
            	$result = array(
        			'success'=>array(),
					'data'=>array()
	    		);
				foreach($sql as $cf){
					$result['data'][] = array(
						'content_files_id'	=> $cf->id,						
						'content_filename'	=> $cf->filename						
					);
		        }
		        $result['success'] = "true";
			    echo json_encode($result);             	
	      	}
		}
	}
	public function actionCreate(){
		if( Yii::app()->user->checkAccess('createStationsPlayback') ){	
			$data = json_decode(Yii::app()->request->getPost('data'),true);
			$post = new StationsPlayback;         					   
			$post->stations_scenario_id = $data['stations_scenario_id'];
			$post->content_files_id 	= $data['content_files_id'];
			$post->save();
            $res =  CJSON::encode(array(
            	'success'=>'true',
            	//'data'=>$post
            ));
            echo $res;
        }
        else{
        	echo "{success: false, msg: 'Not permitted!}";
        }		
	}
	public function actionUpdate(){
		if( Yii::app()->user->checkAccess('updateStationsPlayback') ){
			$data = json_decode(Yii::app()->request->getPut('data'),true);
			$put=StationsPlayback::model()->findByPk($data['id']);		
			if (isset($data['content_files_id'])){
				$put->content_files_id 	= $data['content_files_id'];
			}			
			$put->save();
	        $res =  CJSON::encode(array(
	        	'success'=>'true',
	        	'data'=>$data
	        ));
	        echo $res;
	    }
	    else{
	    	echo "{success: false, msg: 'Not permitted!}";
	    }
	}		
}
?>