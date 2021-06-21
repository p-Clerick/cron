<?php
class AdvertisementController extends Controller
{

  	   		public function actionAdvertisement()
   	   		{
		       			 if (isset($_POST)){
			       			 	$carrier = Yii::app()->user->checkUser(Yii::app()->user);
		     		            $advertisements   = Advertisement::model()->findAll(array(
		     		      			'condition'=>'t.carriers_id=:carrid',
	                   				'params'=>array(':carrid'=>$carrier['carrier_id']),
			     		            'order' => 'id'));
		     		            $count = Advertisement::model()->count();
		     		            $count = 0;
			                   $result = array(
												'points'=>array(),
												'count'=>array()
							   );
		                      foreach($advertisements as $advertisement){
									$result['points'][] = array(
										'id'				=> $advertisement->id,
										'name'				=> $advertisement->name,
										'latitude'	 		=> (double) (floor($advertisement->latitude/100)*100+(($advertisement->latitude   - floor($advertisement->latitude/100)*100)*100/60))/100,
										'longitude' 		=> (double) (floor($advertisement->longitude/100)*100+(($advertisement->longitude - floor($advertisement->longitude/100)*100)*100/60))/100,
									);
		                            $count++;
		                      }
		                      $result['count'] = $count;
		                      echo json_encode($result);
		                 }
  	   		}
			public function actionDelete($id){
    			if( Yii::app()->user->checkAccess('deleteAdvertisement') ){
      					$sql=Advertisement::model()->deleteByPk($id);
						$res =  CJSON::encode(array(
		                        	'success'=>'true'
		                        ));
		                        echo $res;
				}else {
						echo "{success: false, msg: 'Not permitted!}";
				}
			}
            public function actionRead(){
            		   $carrier = Yii::app()->user->checkUser(Yii::app()->user);
                       if($_GET['level'] === '1'){
                       		if ($carrier['id']){
		                        $sql = Advertisement::model()->findAll(array(
                   				'condition'=>'t.carriers_id=:carrid',
                   				'params'=>array(':carrid'=>$carrier['carrier_id']),
		                        'order' => 'id'));		                        
		                    }
		                    else{
		                    	$sql = Advertisement::model()->findAll(array(                   				
		                        'order' => 'id')); 
		                    }    
		                        $res =  CJSON::encode(array(
		                        'success'=>'true',
		                        'data'=>$sql
		                        ));
		                        echo $res;
					   }

			}
            public function actionUpdate(){
			 					if( Yii::app()->user->checkAccess('updateAdvertisement') ){
            					$data = json_decode(Yii::app()->request->getPut('data'),true);

		                        $sql=Advertisement::model()->findByPk($data['id']);
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
            		   			$carrier = Yii::app()->user->checkUser(Yii::app()->user);
			 					if( Yii::app()->user->checkAccess('createAdvertisement') ){
			 				    $data = json_decode(Yii::app()->request->getPost('data'),true);
			 				   		$ad = new Advertisement;
           						    $ad->latitude  	 = $data['latitude'];
           						    $ad->longitude 	 = $data['longitude'];
           						    $ad->name 		 = $data['name'];
           						    $ad->carriers_id = $carrier['carrier_id'];
									$ad->save();

		                        $res =  CJSON::encode(array(
		                        'success'=>'true',
		                        'data'=>$data
		                        ));
		                        echo $res;
								} else {
										echo "{success: false, msg: 'Not permitted!}";
								}

			 }
}
?>