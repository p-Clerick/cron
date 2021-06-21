<?php
class StopsScenarioController extends Controller
{
	public function actionStopsScenarioFileCreate(){
        if( Yii::app()->user->checkAccess('loadStopsScenario')){
			if (isset($_POST)){
				if($_POST['level'] === '2'){
                  	$carrier = Yii::app()->user->checkUser(Yii::app()->user);
                    if($carrier){
                 		$stops = StopsScenario::model()->with('stops', 'route')->findAll(array(
           					'condition'=>'t.routes_id=:routeid',
           					'params'=>array(':routeid'=>$_POST['nodeid']),
           					'order' => 't.number')
   						);			                         
                     	$borts = Borts::model()->with('park')->findAll(array(
	                        'condition'=>'park.carriers_id=:carrid and t.status=:stat',
							'params'=>array(':carrid'=>$carrier['carrier_id'],':stat'=>'yes'),
		                        	'order' => 't.id'));
						$str="";
						$i = 1;
			            foreach($stops as $stop){									  	
							$result['route'] = $stop->route->name;
        		 				$result['data'][] = array(
		                         	'route' 			=> $stop->route->name,
		                         	'number' 			=> $i,
		                         	'video_file_name' 	=> $stop->stops->video_file_name,
		                         	'name'				=> $stop->stops->name,
		                         	'latitude'			=> $stop->stops->latitude,
		                         	'longitude'			=> $stop->stops->longitude,
		                         	'direction'			=> $stop->stops->direction,
                         		);
							$i++;
                		}
		                $str =  json_encode($result);
						if($str!=''){
		    			    $filename  	= 'stops.'.$stop->route->id.'.txt';
		                    $path  		= $_SERVER['DOCUMENT_ROOT'].'/.ftp/'.$carrier['nick'].'/';
		                    $from  		= $_SERVER['DOCUMENT_ROOT'].'/.ftp/'.$carrier['nick'].'/services/'.$filename;
							$fp = fopen($from, "w");
			    	  		fwrite($fp,$str);
			    	  		fclose($fp);
		                	foreach($borts as $bort){
								$target = '../../services/'.$filename;
								$link   = $path.''.$bort->number.'/services/'.$filename;
								if (!is_link($link)){
									symlink($target, $link);
							    }
		                	}
						}
						else {
						  	echo "No data to create file tochki zupynki!";
						  	exit;
						}
                	}
				}
			}
		}else {
							echo "{success: false, msg: 'Not permitted!}";
		}
	}
	public function actionPointsControlScenarioName(){
        if (isset($_POST)){
	        if($_POST['level'] === '2'){
                $sql = Yii::app()->db->createCommand()
				    ->select('pc.id , pc.name as points_control_name')
				    ->from('points_control pc')
				    ->join('points_control_scenario pcs','points_control_id = pc.id and  routes_id = '.$_POST['nodeid'].'')
				    //->where('points_control_id = pc.id and  routes_id = '.$_POST['nodeid'].'')
				    ->order('id')
				    ->queryAll();
                $res =  CJSON::encode(array(
                'success'=>'true',
                'data'=>$sql
                ));
                echo $res;
		    }
        }
	}
	public function actionStopsName(){
        if (isset($_POST)){
	        if($_POST['level'] === '2'){
            	$sql = Stops::model()->findAll(array('select'=>'id,name','order' => 'name'));
            	$res =  CJSON::encode(array(
            		'success'=>'true',
            		'data'=>$sql
            	));
            	echo $res;
	      	}
		}
	}
	public function actionVideoFileStopsName(){
		$carrier = Yii::app()->user->checkUser(Yii::app()->user);
        $way = $_SERVER['DOCUMENT_ROOT'].'/.ftp/stops';
		$i = 0;
		if ($handle = opendir($way)) {
		    while (false !== ($file = readdir($handle))) {
		        if ($file != "." && $file != "..") {
					  	$mas[$i] = $file;
		            $i++;
		        }
		    }
		    closedir($handle);
		}
		else{
			echo 'success: "false", msg: "can not open dir"';
			exit;
		}
   		for ($i=0;$i<count($mas);$i++){
			$str=$mas[$i];
			if ((substr_count($str, '.mpeg') and substr_count($str, 'vfz.'))
				|| (substr_count($str, '.avi') and substr_count($str,'vfz.'))){
			 	$str = str_replace('vfz.','',$str);
				$mas1[]['v_file_name']=$str;
			}
		}
  		$mas1[]['v_file_name']='';
		$res =  CJSON::encode(array(
            'success'=>'true',
            'data'=>$mas1
        ));
        echo $res;
	}
	public function actionAudioFileStopsName(){
		$carrier = Yii::app()->user->checkUser(Yii::app()->user);
        $way = $_SERVER['DOCUMENT_ROOT'].'/.ftp/stops';
		$i = 0;
		if ($handle = opendir($way)) {
		    while (false !== ($file = readdir($handle))) {
		        if ($file != "." && $file != "..") {
					$mas[$i] = $file;
		            $i++;
		        }
		    }
		    closedir($handle);
		}
		else{
			echo 'success: "false", msg: "can not open dir"';
			exit;
		}
   		for ($i=0;$i<count($mas);$i++){
			$str=$mas[$i];
			if ((substr_count($str, '.mp3') and substr_count($str,'vfz.'))){
				$str = str_replace('vfz.','',$str);
				$mas1[]['a_file_name']=$str;
			}
		}
		$mas1[]['a_file_name']='';
		$res =  CJSON::encode(array(
            'success'=>'true',
            'data'=>$mas1
        ));
        echo $res;
	}			
	public function actionStopsScenarioName(){
	    if (isset($_POST)){
			if($_POST['level'] === '2'){
                $sql = Yii::app()->db->createCommand()
				    ->select('id , name')
				    ->from('points_control pc')
				    ->order('id')
				    ->queryAll();
                $res =  CJSON::encode(array(
                'success'=>'true',
                'data'=>$sql
                ));
                echo $res;
			}
	    }
	}

	public function actionDelete($id){
		if( Yii::app()->user->checkAccess('deleteStopsScenario') ){				
			$stopsc = StopsScenario::model()->with('stops')->findAll(array(
       			'condition'=>'t.id=:delid',
       			'params'=>array(':delid'=>$id)));
       		$stopssc_num = $stopsc[0]['number'];
       		$routes_id  = $stopsc[0]['routes_id'];						
			$del=StopsScenario::model()->deleteByPk($id);	      				
			$stopssc = StopsScenario::model()->with(array(
				'route'=>array(
					'select'=>false,
					'condition'=>'t.routes_id=:routeid',
					'params'=>array(':routeid'=>$routes_id),
				),
			))->findAll(array('select'=>'id,number','order' => 'number'));						
			$i = 1;
            foreach ($stopssc as $stsc){
            	$put=StopsScenario::model()->findByPk($stsc['id']);
				$put->number = $i;
				$put->save();                                	
            	$i++;
            }
            $res =  CJSON::encode(array(
                'success'=>'true'
            ));
            echo $res;
        }
        else{
        	echo "{success: false, msg: 'Not permitted!}";
        }	                        		                                                        
	}

    public function actionRead(){
       if($_GET['level'] === '2'){                               
            $stop = StopsScenario::model()->with('stops')->findAll(array(
   				'condition'=>'t.routes_id=:routeid',
   				'params'=>array(':routeid'=>$_GET['nodeid']),
   				'order' => 't.number')
   			);
   			$result = array(
				'success'=>true,
				'data'=>array(),
			);
            foreach($stop as $stops){
				$result['data'][] = array(
					'id'			=> $stops->id,
					'number'		=> $stops->number,													
					'name'			=> $stops->stops->name,
					'v_file_name'	=> $stops->v_file_name,
					'a_file_name'	=> $stops->a_file_name
				);
            }
            echo json_encode($result);
	   }
	}

	public function actionUpdate(){
		if( Yii::app()->user->checkAccess('updateStopsScenario') ){
			$data = json_decode(Yii::app()->request->getPut('data'),true);
			$put=StopsScenario::model()->findByPk($data['id']);
	        if(isset($data['name'])){
				$stops_id = Stops::model()->findAll(array(
					'condition'=>'t.name=:adname',
					'params'=>array(':adname'=>$data['name']),
	            	'order' => 't.id'));						
				$put->stops_id = $stops_id[0]['id'];
			}
			if (isset($data['v_file_name'])){
				$put->v_file_name = $data['v_file_name'];	
			}
			if (isset($data['a_file_name'])){
				$put->a_file_name = $data['a_file_name'];	
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

	public function actionCreate(){
	 	if( Yii::app()->user->checkAccess('createStopsScenario') ){	
			$data = json_decode(Yii::app()->request->getPost('data'),true);			 				   
			$stops_id = Stops::model()->findAll(array(
				'condition'=>'t.name=:adname',
				'params'=>array(':adname'=>$data['name']),
                'order' => 't.id'));
            $stops_id = $stops_id[0]['id'];
			$count = StopsScenario::model()->with('stops')->count(array(
				'condition'=>'t.routes_id=:routeid',
				'params'=>array(':routeid'=>$_POST['nodeid'])));
			$count++;
			$post = new StopsScenario;
			$post->number = $count;           					   
			$post->stops_id = $stops_id;
			if (isset($data['v_file_name'])){
				$post->v_file_name = $data['v_file_name'];	
			}
			if (isset($data['a_file_name'])){
				$post->a_file_name = $data['a_file_name'];	
			}  								           					    
			    $post->routes_id = $_POST['nodeid'];
		    $post->save();
            $res =  CJSON::encode(array(
            'success'=>'true',
            'data'=>$post
            ));
            echo $res;
        }
        else{
        	echo "{success: false, msg: 'Not permitted!}";
        }
	}
	public function actionContentChanges(){
		$contentchanges = ContentChanges::model()->with('route')->findAll(array(
            'select'=>'Id',
            'condition'=>'t.types_of_content_changes_id=:toccid and t.routes_id=:routid',                   					
			'params'=>array(':toccid'=>ContentChanges::SS,':routid'=>$_POST['nodeid']),
        ));
        if ($contentchanges){        	
        	$put=ContentChanges::model()->findByPk($contentchanges[0]['Id']);
			$put->users_id = Yii::app()->user->getId();
			$put->created = date ("Y-m-d H:i:s");
			$put->save();
			$r['type'] = 'update';
			$r['route'] = $contentchanges[0]->route->name;
        }
        else{
			$post = new ContentChanges;
			$post->users_id = Yii::app()->user->getId();
			$post->types_of_content_changes_id = ContentChanges::SS;
			$post->routes_id = $_POST['nodeid'];
			$post->created = date ("Y-m-d H:i:s");
			$post->save();
			$r['type'] = 'insert';
			$route = Route::model()->findByPk($_POST['nodeid']);
			$r['route'] = $route->name;
        }
        $r['user'] = Yii::app()->user->username;
        $res =  CJSON::encode(array(
 	       'success'=>true,
        	'data'=> $r
        ));     
		echo $res;      
	}
	public function actionRoutesName(){
		$pcs = StopsScenario::model()->with('route')->findAll(array(
            'condition'=>'t.stops_id=:ssid',                   					
			'params'=>array(':ssid'=>$_GET['ss_id']),
			'order'=>'route.name'
        ));
        $route_str = '';
        foreach($pcs as $p){
        	$route_str .= $p->route->name.",";
        }
        $r['routes'] = substr($route_str,0,-1);
        $res =  CJSON::encode(array(
 	       'success'=>true,
        	'data'=> $r
        ));     
		echo $res;     
	}
	public function actionContentChangesData(){
		$contentchanges = ContentChanges::model()->with('route')->findAll(array(
            'select'=>'Id,created',
            'condition'=>'t.types_of_content_changes_id=:toccid and t.routes_id=:routid',                   					
			'params'=>array(':toccid'=>ContentChanges::SS,':routid'=>$_GET['nodeid']),
        ));
		if ($contentchanges){
			$r['date'] = $contentchanges[0]->created;
	        $res =  CJSON::encode(array(
	 	        'success'=>true,
	        	'data'=> $r
	        ));     
		}
		else{
        	$res =  CJSON::encode(array(
	 	        'success'=>false,
	        	'data'=> 'no date'
	        ));    
		}
		echo $res;   
	}
}
?>