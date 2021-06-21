<?php
class AdvertisementScenarioController extends Controller
{
			public function actionAdvertisementScenarioFileCreate(){

						 if (isset($_POST)){
							 if($_POST['level'] === '2'){
                              	$carrier = Yii::app()->user->checkUser(Yii::app()->user);
		                         if($carrier['id']){
			                         $advertisements = AdvertisementScenario::model()->with('advertisement')->findAll(array(
		                   					'condition'=>'t.routes_id=:routeid and advertisement.carriers_id=:carrid',
		                   					'params'=>array(':routeid'=>$_POST['nodeid'],':carrid'=>$carrier['carrier_id']),
					                        'order' => 't.number'));
		                         	 $borts  = Borts::model()->with('park')->findAll(array(
					                        'condition'=>'park.carriers_id=:carrid and t.status=:stat',
		    								'params'=>array(':carrid'=>$carrier['carrier_id'],':stat'=>'yes'),
					                        	'order' => 't.id'));
		                         	 $graphs = Graphs::model()->findAll(array(
						                        'condition'=>'t.routes_id=:routid',
			    								'params'=>array(':routid'=>$_POST['nodeid']),
					                        	'order' => 't.name'));
									 $str="";
									 $i = 1;
						             foreach($advertisements as $advertisement){					             	
											$result['route'] = $advertisement->route->name;
	                		 				$result['data'][] = array(
					                         	'route' 	=> $advertisement->route->name,
					                         	'number' 			=> $i,
					                         	'video_file_name' 	=> $advertisement->video_file_name,
					                         	'name'				=> $advertisement->advertisement->name,
					                         	'latitude'			=> $advertisement->advertisement->latitude,
					                         	'longitude'			=> $advertisement->advertisement->longitude,
					                         	'direction'			=> $advertisement->advertisement->direction,
			                         		);
			                         		$i++;
			                		 }
					                  $str =  json_encode($result);
									  if($str!=''){
									  	foreach($graphs as $graph){
									  		$graphname = $graph->changeGraphName($graph->name);
											$filename  	= 'ad.'.$advertisement->route->id.'.txt';
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
									  }
									  else {
													  	echo "No data to create file tochki reklamy!";
													  	exit;
									  }
		                    	 }
							 }
						 }
			}	   		
			public function actionAdvertisementName(){
		      		  if (isset($_POST)){
		      		  		$carrier = Yii::app()->user->checkUser(Yii::app()->user);
						    if(($_POST['level'] === '2') or ($_POST['level'] === '3')){
						      	if($carrier['id']){
	 		                        $sql = Advertisement::model()->findAll(array(
	 		                        'select'=>'id,name',
	                   				'condition'=>'t.carriers_id=:carrid',
	                   				'params'=>array(':carrid'=>$carrier['carrier_id']),
	 		                        'order' => 'id'));
	 		                    }
	 		                    else{
	 		                        $sql = Advertisement::model()->with('carrier.route')->findAll(array(
	 		                        'select'=>'id,name',
	                   				'condition'=>'route.id=:nodeid',
	                   				'params'=>array(':nodeid'=>$_POST['nodeid']),
	 		                        'order' => 't.id'));
	 		                    	
	 		                    }    
			                        $res =  CJSON::encode(array(
			                        'success'=>'true',
			                        'data'=>$sql
			                        ));
			                        echo $res;
						      }
				      }

			}
			public function actionVideoFileAdvertisementName(){
								$result = array(
									'success'=>true,
									'data'=>array(),
								);

		                      $carrier = Yii::app()->user->checkUser(Yii::app()->user);
		                      if ($carrier['id']){
		                      		$carrier = $carrier['nick'];
		                      }
		                      else{
	                      			$route = Route::model()->with('carrier')->findByPk($_POST['nodeid']);	                      			
	                      				$carrier = $route->carrier->nick;        					
		                      }		                      
				if($_POST['level'] === '2'){
                    $way = $_SERVER['DOCUMENT_ROOT'].'/.ftp/'.$carrier.'/contents/';
                    		if(file_exists($way)){
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
										echo 'can not open dir';
										exit;
									}

									for ($i=0;$i<count($mas);$i++){
											$str=$mas[$i];
												if ((substr_count($str, '.mpeg') and (!substr_count($str, 'vfz.')))
												 || (substr_count($str, '.avi') and (!substr_count($str,'vfz.')))){
													$mas1[]['video_file_name']=$str;
												}
									}
							}
							else{
									$mas1[0]['video_file_name']	= "no data";
							}				
				  	                $res =  CJSON::encode(array(
				                        'success'=>'true',
				                        'data'=>$mas1
				                        ));
				                        echo $res;
		       }
		       if($_POST['level'] === '3'){
                   if ($carrier){
                         $advertisementscenario = AdvertisementScenario::model()->with('advertisement')->findAll(array(
                  					'condition'=>'t.routes_id=:routeid and advertisement.carriers_id=:carrid',
                  					'params'=>array(':routeid'=>$_POST['nodeid'],':carrid'=>$carrier['carrier_id']),
		                        'order' => 't.number'));
                                 foreach($advertisementscenario as $as){

         								$result['data'][] = array(
         									'video_file_name'					=> $as->video_file_name,
										);
                                }
                   }
                    echo json_encode($result);
		       }
			}
	   		public function actionAdvertisementScenarioName(){

			}

			public function actionDelete($id){
						if( Yii::app()->user->checkAccess('deleteAdvertisement') ){
								 $carrier = Yii::app()->user->checkUser(Yii::app()->user);
                 				 $advertisementscenario = AdvertisementScenario::model()->with('advertisement')->findAll(array(
                   					'condition'=>'t.id=:adid',
                   					'params'=>array(':adid'=>$id))
                   					);
                   					$routes_id = $advertisementscenario[0]['routes_id'];

                                 $del=AdvertisementScenario::model()->deleteByPk($id);

                 				 $advertisementscenario = AdvertisementScenario::model()->with('advertisement')->findAll(array(
                 				 	'select'=>'id,number',
                   					'condition'=>'t.routes_id=:routeid and advertisement.carriers_id=:carrid',
                   					'params'=>array(':routeid'=>$routes_id,':carrid'=>$carrier['carrier_id']),
                   					'order'=>'t.number')
                   					);

								 $i = 1;
                                foreach($advertisementscenario as $as){
									    	$put=AdvertisementScenario::model()->findByPk($as->id);
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
            			$carrier = Yii::app()->user->checkUser(Yii::app()->user);
              			if($_GET['level'] === '2'){
              				if ($carrier['id']){
                 				 $advertisementscenario = AdvertisementScenario::model()->with('advertisement')->findAll(array(
                   					'condition'=>'t.routes_id=:grrouteid and advertisement.carriers_id=:carrid',
                   					'params'=>array(':grrouteid'=>$_GET['nodeid'],':carrid'=>$carrier['carrier_id']),
                   					'order' => 't.number')
                   					);
                   			}
                   			else{
                				 $advertisementscenario = AdvertisementScenario::model()->with('advertisement')->findAll(array(
                   					'condition'=>'t.routes_id=:grrouteid',
                   					'params'=>array(':grrouteid'=>$_GET['nodeid']),
                   					'order' => 't.number')
                   					);
                   				
                   			}
                 		}
              			if($_GET['level'] === '3'){
                 				 $advertisementscenario = AdvertisementScenario::model()->with('advertisement')->findAll(array(
                   					'condition'=>'t.routes_id=:grrouteid and advertisement.carriers_id=:carrid',
                   					'params'=>array(':grrouteid'=>$_GET['nodeid'],':carrid'=>$carrier['carrier_id']),
                   					'order' => 't.id')
                   					);
                 		}

								$result = array(
									'success'=>true,
									'data'=>array(),
								);
                                 foreach($advertisementscenario as $as){

         								$result['data'][] = array(
         									'id'					=> $as->id,
											'number'				=> $as->number,											
											'video_file_name' 		=> $as->video_file_name,
											'name'					=> $as->advertisement->name,

										);
                                }
                                echo json_encode($result);


			}

            public function actionUpdate(){
            	         if( Yii::app()->user->checkAccess('updateAdvertisement') ){
            					$data = json_decode(Yii::app()->request->getPut('data'),true);
            					$iden = Yii::app()->request->getPut('nodeid');
                                if(isset($data['name'])){
		                        	$advertisement = Advertisement::model()->findAll(array(
	                   					'condition'=>'t.name=:adname',
	                   					'params'=>array(':adname'=>$data['name']),
				                        'order' => 't.id'));
			                     	$ad_id = $advertisement[0]['id'];
									$put=AdvertisementScenario::model()->findByPk($data['id']);
									$put->advertisement_id = $ad_id;
									$put->save();

                                }
                                if (isset($data['video_file_name'])){
                                	$put=AdvertisementScenario::model()->findByPk($data['id']);
									$put->video_file_name = $data['video_file_name'];
									$put->save();
                                }                                

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
			 			if( Yii::app()->user->checkAccess('createAdvertisement') ){
							 	$carrier = Yii::app()->user->checkUser(Yii::app()->user);
			 				    $data = json_decode(Yii::app()->request->getPost('data'),true);		                        
		                        $advertisement = Advertisement::model()->findAll(array(
                   					'condition'=>'t.name=:adname and t.carriers_id=:carrid',
                   					'params'=>array(':adname'=>$data['name'],':carrid'=>$carrier['carrier_id']),
			                        'order' => 't.id'));
			                     	  $ad_id = $advertisement[0]['id'];
                                $count = AdvertisementScenario::model()->with('advertisement')->count(array(
                   					'condition'=>'t.routes_id=:routeid and advertisement.carriers_id=:carrid',
                   					'params'=>array(':routeid'=>$_POST['nodeid'],':carrid'=>$carrier['carrier_id'])));
                   					$count++;

			 				   $ad = new AdvertisementScenario;
           					   $ad->number = $count;
           					   $ad->routes_id = $_POST['nodeid'];
           					   $ad->advertisement_id = $ad_id;
           					   $ad->video_file_name = $data['video_file_name'];
							   $ad->save();

		                        $res =  CJSON::encode(array(
		                        'success'=>'true',
		                        'data'=>$ad
		                        ));
		                        echo $res;
		        		} else {
										echo "{success: false, msg: 'Not permitted!}";
								}

			 }
	public function actionRoutesName(){
		$as = AdvertisementScenario::model()->with('route')->findAll(array(
            'condition'=>'t.advertisement_id=:asid',                   					
			'params'=>array(':asid'=>$_GET['as_id']),
			'order'=>'route.name'
        ));
        $route_str = '';
        foreach($as as $p){
        	$route_str .= $p->route->name.",";
        }
        $r['routes'] = substr($route_str,0,-1);
        $res =  CJSON::encode(array(
 	       'success'=>true,
        	'data'=> $r
        ));     
		echo $res;     
	}
	public function actionContentChanges(){
		$contentchanges = ContentChanges::model()->with('route')->findAll(array(
            	'select'=>'Id',
                'condition'=>'t.types_of_content_changes_id=:toccid and t.routes_id=:routid',                   					
				'params'=>array(':toccid'=>ContentChanges::ADS,':routid'=>$_POST['nodeid']),
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
			$post->types_of_content_changes_id = ContentChanges::ADS;
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
	public function actionContentChangesData(){
		$contentchanges = ContentChanges::model()->with('route')->findAll(array(
            	'select'=>'Id,created',
                'condition'=>'t.types_of_content_changes_id=:toccid and t.routes_id=:routid',                   					
				'params'=>array(':toccid'=>ContentChanges::ADS,':routid'=>$_GET['nodeid']),
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
