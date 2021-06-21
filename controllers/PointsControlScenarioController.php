<?php
class PointsControlScenarioController extends CController
{
	public function actionPointsControlScenarioFileCreate(){
            if( Yii::app()->user->checkAccess('loadPointsControlScenario')){
                $carrier = Yii::app()->user->checkUser(Yii::app()->user);
                 if($carrier){
                     $pcs = PointsControlScenario::model()->with('points_control','route')->findAll(array(
           					'condition'=>'t.routes_id=:routeid',
           					'params'=>array(':routeid'=>$_POST['nodeid']),
	                        'order' => 't.id'));
                 	 $borts = Borts::model()->with('park')->findAll(array(
		                        'condition'=>'park.carriers_id=:carrid and t.status=:stat',
								'params'=>array(':carrid'=>$carrier['carrier_id'],':stat'=>'yes'),
	                        	'order' => 't.id'));
					 $str="";
		             foreach($pcs as $pointsctrlscenario){							  	
					 $result['route'] = $pointsctrlscenario->route->name;
            		 $result['data'][] = array(
		                         	'route' 	=> $pointsctrlscenario->route->name,
		                         	'number' 	=> $pointsctrlscenario->number,
		                         	'pcs_id' 	=> $pointsctrlscenario->id,
		                         	'name'		=> $pointsctrlscenario->points_control->name,
		                         	'latitude'		=> $pointsctrlscenario->points_control->latitude,
		                         	'longitude'		=> $pointsctrlscenario->points_control->longitude,
		                         	'direction'		=> $pointsctrlscenario->points_control->direction,
	                         	);
	                         }
                       		$str =  json_encode($result);
					  if($str!=''){
	    				    $filename  	= 'points.'.$pointsctrlscenario->route->id.'.txt';
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
									  	echo "No data to create file tochki!";
									  	exit;
					  }
            	 }
	    	}
           	else {
								echo "{success: false, msg: 'Not permitted!}";
			}
	}
	public function actionPointsControlScenarioName(){
                    // $data = json_decode(Yii::app()->request->getPost('data'),true);
		      if (isset($_POST)){

				      if($_POST['level'] === '2'){
                        $points_control = PointsControl::model()->findAll(array(
                        'select' => 'id,name',
                        'order' => 'name'));
            			$result =  CJSON::encode(array(
                        'success'=>'true',
                        'data'=>$points_control
                        ));
                        echo $result;
				      }
		      }
	}

	public function actionDelete($id){
 			if( Yii::app()->user->checkAccess('deletePointsControlScenario') ){
				$sql = Yii::app()->db->createCommand()
						    ->select('max(id) as cnt')
						    ->from('points_control_scenario pcs')
						    ->where('routes_id = (select routes_id from points_control_scenario where id = '.$id.')')
						    ->queryAll();
				$number = $sql[0]['cnt'];
				if ($number === $id){
  					$del=PointsControlScenario::model()->deleteByPk($id);
						$res =  CJSON::encode(array(
                        	'success'=>'true'
                        ));
                        echo $res;
  				}
  				else{
						$res =  CJSON::encode(array(
                        	'success'=>'false'
                        ));
                        echo $res;
  				}
  			}else {
								echo "{success: false, msg: 'Not permitted!}";
						}
	}

    public function actionRead(){
            	$result = array();
               if($_GET['level'] === '2'){
                        $pcs = PointsControlScenario::model()->with('points_control')->findAll(array(
           					'condition'=>'t.routes_id=:routeid',
           					'params'=>array(':routeid'=>$_GET['nodeid']),
	                        'order' => 't.id'));
	                        $result = array('success' => 'true','data'=>array());
	                         foreach($pcs as $pointsctrlscenario){
	                         	$result['data'][] = array(
		                         	'id' 		=> $pointsctrlscenario->id,
		                         	'number' 	=> $pointsctrlscenario->number,
		                         	'name'		=> $pointsctrlscenario->points_control->name,
	                         	);
	                         }
                       		echo json_encode($result);
			   }
	}

    public function actionUpdate(){
 			if( Yii::app()->user->checkAccess('updatePointsControlScenario') ){
    					$data = json_decode(Yii::app()->request->getPut('data'),true);
	 				    $sql = Yii::app()->db->createCommand()
						    ->select('id')
						    ->from('points_control pc')
						    ->where('name = "'.$data['name'].'"')
						    ->queryRow();
                        $put=PointsControlScenario::model()->findByPk($data['id']);
						$put->points_control_id = $sql['id'];
						$put->save();

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
	 		if( Yii::app()->user->checkAccess('createPointsControlScenario') ){
	 				   $data = json_decode(Yii::app()->request->getPost('data'),true);
	 				   $sql = Yii::app()->db->createCommand()
						    ->select('count(id) as count')
						    ->from('points_control_scenario pcs')
						    ->where('routes_id = '.$_POST['nodeid'].'')
						    ->queryAll();
					   $number = $sql[0]['count'] +1;

	 				   $sql = Yii::app()->db->createCommand()
						    ->select('id')
						    ->from('points_control pc')
						    ->where('name = "'.$data['name'].'"')
						    ->queryRow();

	 				   $post = new PointsControlScenario;
   					   $post->number = $number;
   					   $post->points_control_id = $sql['id'];
   					   $post->routes_id = $_POST['nodeid'];
					   $post->save();

                        $res =  CJSON::encode(array(
                        'success'=>'true',
                        'data'=>$post
                        ));
                        echo $res;
            }else {
								echo "{success: false, msg: 'Not permitted!}";
		    }
	}
	public function actionContentChanges(){
		$contentchanges = ContentChanges::model()->with('route')->findAll(array(
            	'select'=>'Id',
                'condition'=>'t.types_of_content_changes_id=:toccid and t.routes_id=:routid',                   					
				'params'=>array(':toccid'=>ContentChanges::PCS,':routid'=>$_POST['nodeid']),
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
			$post->types_of_content_changes_id = ContentChanges::PCS;
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
		$pcs = PointsControlScenario::model()->with('route')->findAll(array(
            'condition'=>'t.points_control_id=:pcsid',                   					
			'params'=>array(':pcsid'=>$_GET['pcs_id']),
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
				'params'=>array(':toccid'=>ContentChanges::PCS,':routid'=>$_GET['nodeid']),
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