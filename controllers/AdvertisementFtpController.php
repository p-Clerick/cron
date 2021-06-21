<?php
class AdvertisementFtpController extends Controller
{
  	   		public function actionRead()
  	   		{
				$carrier = Yii::app()->user->checkUser(Yii::app()->user);
                     $way = $_SERVER['DOCUMENT_ROOT'].'/.ftp/'.$carrier['nick'].'/contents/';
     			$i = 0;
				if ($handle = opendir($way)) {
				 	   while (false !== ($file = readdir($handle))) {
					        if ($file != "." && $file != ".." && filesize($way.'/'.$file) &&
					        (substr_count($file, '.mpeg') || substr_count($file, '.txt') || substr_count($file, '.avi'))) {
					        		$f_name[$i]		= $file;
				  					$f_size[$i]		= sprintf("%u", filesize($way.'/'.$file));
				  					$f_size[$i]      = number_format($f_size[$i]).' byte';
									$f_date[$i] 		= date ("d.m.Y H:i:s", filemtime($way.'/'.$file));
					            $i++;
					        }
					    }
				    closedir($handle);
				    			$result = array(
									'success'=>true,
									'data'=>array(),
								);

				}
				else{
					echo 'can not open dir';
					exit;
				}

		  				 for($i=0;$i<count($f_name);$i++){
         								$result['data'][] = array(
         									'f_name'	=> $f_name[$i],
											'f_size'	=> $f_size[$i],
											'f_date' 	=> $f_date[$i]
										);
          				 }
                                echo json_encode($result);
  	   		}
  	   		public function actionTreeFtp()
  	   		{
				$carrier = Yii::app()->user->checkUser(Yii::app()->user);
                $way = $_SERVER['DOCUMENT_ROOT'].'/.ftp/'.$carrier['nick'].'/';
                 if($_POST['level'] === '0'){
			           	$borts  = Borts::model()->with('park')->findAll(array(
		                        'condition'=>'park.carriers_id=:carrid',
	  							'params'=>array(':carrid'=>$carrier['carrier_id']),
	                        	'order' => 't.id'));
			                      foreach($borts as $bort){
										$result[] = array(
											'text'			=> $bort->number,
											'uiProvider'	=> "col",
											'id'	 		=> $bort->id,
											'leaf' 			=> false,
											'cls'			=> "folder",
											'level'			=> 1,
											'nodeid'		=> $bort->id,
										);
			                      }
			                      echo json_encode($result);
                 }
                 if($_POST['level'] === '1'){
			           				    $bort  = Borts::model()->findByPk($_POST['nodeid']);
			      				 $way.= $bort->number.'/';
								 $i = 0;
								 if ($handle = opendir($way)) {
								 	   while (false !== ($file = readdir($handle))) {
									        if ($file != "." && $file != "..") {
									        	$path_f[$i]		    = $file;
												$path_id[$i]		= $file;
									            $i++;
									        }
									    }
								    closedir($handle);

						 						 for($i=0;$i<count($path_f);$i++){
						 						 		if (($path_f[$i] === "contents") or ($path_f[$i] === "reports")){
						 						 				if ($path_f[$i] === "contents"){
						 						 					$path_ft[$i] = "відео";
						 						 				}
						 						 				if ($path_f[$i] === "reports"){
													 				$path_ft[$i] = "звіти";
						 						 				}
						 						 				$result[] = array(
																		'text'			=> $path_ft[$i],
																		'uiProvider'	=> "col",
																		'id'	 		=> $path_id[$i],
																		'leaf' 			=> false,
																		'cls'			=> "folder",
																		'level'			=> 2,
																		'nodeid'		=> $bort->id,
																		'fld'			=> $path_f[$i],
																);
														}
												 }
							 	 }
								 else{
									echo 'can not open dir';
									exit;
								 }
			                      echo json_encode($result);
                 }
                  if($_POST['level'] === '2'){
								 $bort  = Borts::model()->findByPk($_POST['nodeid']);
			      				 $way.= $bort->number.'/'.$_POST['node'].'/';
						 $i = 0;
						 if ($handle = opendir($way)) {
						 	   while (false !== ($file = readdir($handle))) {
							        if ($file != "." && $file != "..") {
							        		$path_f[$i]		    = $file;
											$file_size[$i]		= sprintf("%u", filesize($way.'/'.$file));
						  					$file_size[$i]      = number_format($file_size[$i]).' byte';
											$file_data[$i] 		= date ("d.m.Y H:i:s", filemtime($way.'/'.$file));
											$path_id[$i]		= $i.'-'.$_POST['node'];
							            $i++;
							        }
							   }
							   if(isset($path_f)){
				 						 for($i=0;$i<count($path_f);$i++){
											$result[] = array(
																		'text'			=> $path_f[$i],
																		'file_size'		=> $file_size[$i],
																		'file_data'		=> $file_data[$i],
																		'uiProvider'	=> "col",
																		'id'	 		=> $path_id[$i],
																		'leaf' 			=> true,
																		'cls'			=> "file",
																		'level'			=> 3,
																		'nodeid'		=> $bort->id,
																		'fld'			=> $_POST['node'],
											);
										 }
							   }
							   else{
                                    echo '[]';
									exit;
							   }
					 	 }
						 else{
							echo 'can not open dir';
							exit;
						 }
						 echo json_encode($result);
                  }
  	   		}
  	   		public function actionDelete()
  	   		{
  	   			$carrier = Yii::app()->user->checkUser(Yii::app()->user);
                $way = $_SERVER['DOCUMENT_ROOT'].'/.ftp/'.$carrier['nick'].'/';
                list($a,$name) = explode('-',$_POST['id']);
                if($_POST['level'] === '3'){
 								 $bort  = Borts::model()->findByPk($_POST['nodeid']);
			      				 $way.= $bort->number.'/'.$name.'/'.$_POST['text'];
                }
                if (unlink($way)){
													echo "success:true";
				}
				else{
													echo "succes:false";
				}
  	   		}
 	   		public function actionDeleteLocal()
  	   		{
  	   			$carrier = Yii::app()->user->checkUser(Yii::app()->user);
                $way = $_SERVER['DOCUMENT_ROOT'].'/.ftp/'.$carrier['nick'].'/contents/'.$_POST['name'];
                if (unlink($way)){
													echo "success:true";
				}
				else{
													echo "succes:false";
				}

			}
 	   		public function actionUrl()
  	   		{
  	   			$carrier = Yii::app()->user->checkUser(Yii::app()->user);
  	   			$bort  = Borts::model()->findByPk($_POST['nodeid']);
  	   			list($a,$name) = explode('-',$_POST['id']);
                $way = 'http://'.$_SERVER['HTTP_HOST'].'/.ftp/'.$carrier['nick'].'/'.$bort->number.'/'.$name.'/'.$_POST['text'];
                echo $way;
			}
 	   		public function actionLoad()
  	   		{
	  	   			$carrier = Yii::app()->user->checkUser(Yii::app()->user);
                    $way = $_SERVER['DOCUMENT_ROOT'].'/.ftp/'.$carrier['nick'].'/';
	  	   			if($_POST['level'] === '0'){
			           	$borts  = Borts::model()->with('park')->findAll(array(
		                        'condition'=>'park.carriers_id=:carrid and t.status=:stat',
   								'params'=>array(':carrid'=>$carrier['carrier_id'],':stat'=>'yes'),
	                        	'order' => 't.id'));
			                      foreach($borts as $bort){
						  				$target = '../../contents/'.$_POST['f_name']; echo "\n";
										$link   = $way.''.$bort->number.'/contents/'.$_POST['f_name'];
													if (!is_link($link)){
														symlink($target, $link);
										        	}
			                      }
	  	   		    }
	  	   		    if($_POST['level'] === '1'){
						$bort  = Borts::model()->findByPk($_POST['nodeid']);
						  				$target = '../../contents/'.$_POST['f_name']; echo "\n";
										$link   = $way.''.$bort->number.'/contents/'.$_POST['f_name'];
													if (!is_link($link)){
														symlink($target, $link);
										        	}
	  	   		    }
  	   		}
}
?>