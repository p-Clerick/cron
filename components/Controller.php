<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/column1';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();

	public $logMessage = NULL;
        
	public $writeLog = true;

	protected function beforeAction($action){
		if (Yii::app()->user->isGuest and $this->getId() != 'auth'){        	
        	if (Yii::app()->request->requestType === 'GET'){
        		$headers = apache_request_headers();
	        	if ( array_key_exists('Referer', $headers) ){
					if ($headers['Referer'] === 'SmartMak' || $headers['Referer'] === 'http://'.$_SERVER['HTTP_HOST'].'/guest'){

			    	}
			    	else{
			    		$this->redirect('',true,301);
			    	}        		
	        	}
	        	else{
			    	$this->redirect('',true,301);
			    }

			    if ( array_key_exists('User-Agent',$headers) ){
			    	if (strlen($headers['User-Agent']) > 1){

			    	}
			    	else{
			    		$this->redirect('',true,301);
			    	}
			    }
			    else{
			    	$this->redirect('',true,301);
			    }
			}		
		}
		return parent::beforeAction($action);

	}

 //	protected function afterAction(){
 	//	$user_operation_array = array(
	//		'username'		=> Yii::app()->user->name,
	//		'host' 			=> $_SERVER['REMOTE_ADDR'],
	//		'controller' 	=> $this->getId(),
	//		'action'		=> $this->getAction()->getId(),
	//		'details'		=> $this->logMessage,
	//		'params'		=> CJSON::encode($_REQUEST)
	//	);
      //  if($this->writeLog)
      //  {
      //  	if (($user_operation_array['username'] != 'guest') and ($user_operation_array['username'] != 'Guest')) {
      //  		UserActivity::model()->setUserActivityInfo($user_operation_array);
      //  	}
      //  }
   // }
}
