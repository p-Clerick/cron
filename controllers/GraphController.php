<?php

class GraphController extends Controller
{
	public function actionCreate(){
		$result = array();
		if( Yii::app()->user->checkAccess('createSchedule') ){
			$data = json_decode(stripslashes(file_get_contents('php://input')), true);
			if( !Graphs::model()->exists( 'name = :name AND routes_id = :routeid',
					 array(':name' => $data['title'], ':routeid' => $data['route']) ) ){
				$schedule = new Graphs;
				$schedule->name = $data['title'];
				$route = Route::model()->findByPk($data['route']);
				$schedule->routes_id = $route->id;
				if($schedule->save()){
					$result = array('success' => true);
				} else {
					$result = array('success' => false);
				}
			} else {
				$result = array(
					'success' => false,
					'msg' => Yii::app()->session['GrafikTextTwo'].$data['title'].Yii::app()->session['AlreadyExistsPleaseSelectAnotherName'],
				);
			}
		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}
		echo CJSON::encode($result);
	}

	public function actionRead ($id) {
		$result = array();
		if( Yii::app()->user->checkAccess('readGraph') ){
			$graph = Graphs::model()->findByPk($id);
			$rows = array(
				'id' => $graph->id,
				'carrier' => $graph->carriers_id,
			);
			$result = array(
				'success' => true,
				'rows' => $rows,
			);
		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}
		echo CJSON::encode($result);
	}

	public function actionSetCarrier ($id, $carrier) {
		$result = array();
		if( Yii::app()->user->checkAccess('setCarrierForGraph') ){
			$graph = Graphs::model()->findByPk($id);
			$graph->carriers_id = $carrier;
			if ($graph->save()) {
				$result = array('success' => true);			
			} else {			
				$result = array('success' => false);
			}
		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}
		echo CJSON::encode($result);
	}

	public function actionDelete($id){
		$result = array();
		if( Yii::app()->user->checkAccess('deleteSchedule') ){
			$schedule = Graphs::model()->findByPk($id);
			if($schedule->delete()){
				$result = array('success' => true);
			} else {
				$result = array(
					'success' => false,
					'msg' => Yii::app()->session['RemovingFailed']
				);
			}
		} else {
			$result = array(
				'success' => false,
				'msg' => 'Not permitted',
			);
		}
		echo CJSON::encode($result);
	}

	public function actionGraphScenarioFileCreate(){

						 if (isset($_POST)){
							 if($_POST['level'] === '3'){
                              	$carrier = Yii::app()->user->checkUser(Yii::app()->user);
		                         if($carrier){
                                    $graphs = Graphs::model()->findByPk($_POST['nodeid']);
  									$schedule = $graphs->getCurrentSchedule($_POST['type']);
  									$pcs_count = PointsControlScenario::model()->count(array(
				                   					'condition'=>'t.routes_id=:rtid',
				                   					'params'=>array(':rtid' => $graphs->route->id))
  									);
                                     $str="";
			                         $scheduletimes = ScheduleTimes::model()->with('schedules')->findAll(array(
		                   					'condition'=>'t.schedules_id=:schedid',
		                   					'params'=>array(':schedid' => $schedule->id),
					                        'order' => 't.flight_number, t.points_control_scenario_id'));
			                         $dinners = Dinner::model()->findAll(array(
		                   					'condition'=>'t.schedules_id=:schedid',
		                   					'params'=>array(':schedid' => $schedule->id),
					                        'order' => 't.flight_number'));

		                         	 $borts  = Borts::model()->with('park')->findAll(array(
					                        'condition'=>'park.carriers_id=:carrid and t.status=:stat',
		    								'params'=>array(':carrid'=>$carrier['carrier_id'],':stat'=>'yes'),
					                        	'order' => 't.id'));

											foreach($scheduletimes as $scheduletime){
													$routename = $graphs->route->changeRouteName($graphs->route->name);
													$str.= $routename;
													$str.="::";
													$str.= $graphs->name;
													$str.="::";
													$str.=$scheduletime->flight_number;
													$str.="::";
													$str.=$scheduletime->points_control_scenario->number;
													$str.="::";
													$tmp  = new Time($scheduletime->time);
                      				 				$time = $tmp->getFormattedTime('.');
													$str.= $time;
													$str.="\n";
					                        }
					                        $str=substr($str,0,-1);
					                        //echo $str;

									  if($str!=''){
									  		$graphname = $graphs->changeGraphName($graphs->name);
					    				    $filename  	= 'm_graf.'.$routename.''.$graphname.'.txt';
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
													  	echo "No data to create file m_graf!";
													  	exit;
									  }
									  $str = "";
									  $routename = $graphs->route->changeRouteName($graphs->route->name);
											foreach($dinners as $dinner){

													$tmp  = new Time($dinner->start_time);
                      				 				$time = $tmp->getFormattedTime('.');
													$str.= $time;
													$str.="::";
													$tmp  = new Time($dinner->end_time);
                      				 				$time = $tmp->getFormattedTime('.');
													$str.= $time;
													$str.="::";
													$str.=$dinner->points_control_scenario->number;
													$str.="\n";
					                        }
					                        $str=substr($str,0,-1);
					                        echo $str;

										  if($str!=''){
										  		$graphname = $graphs->changeGraphName($graphs->name);
						    				    $filename  	= 'obid.'.$routename.''.$graphname.'.txt';
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
														  	echo "No data to create file obid!";
														  	exit;
										  }
							 	}
							 }
						 }

	}

}