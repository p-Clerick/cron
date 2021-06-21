<?php 

class AuthController extends Controller
{
	public function actionRead(){
		if(isset($_GET['lang'])){
			$app = Yii::app();
	        if (isset($_GET['lang']))
	        {
	            $app->language = $_GET['lang'];
	            $app->session['lang'] = $app->language;
	        }
	        else if (isset($app->session['lang']))
	        {
	            $app->language = $app->session['lang'];
	        }			
			if($_GET['lang'] == 'ua'){
				$this->renderPartial('login');
			}
			else if ($_GET['lang'] == 'ru'){
				$this->renderPartial('login_ru');
			}
		}
		else{
			$this->renderPartial('login');
		}	
	}

	public function actionCreate(){
		$username = $_POST['username'];
		$password = $_POST['password'];
		$app = Yii::app();
        if (isset($_POST['_lang']))
        {
            $app->language = $_POST['_lang'];
            $app->session['_lang'] = $app->language;
        }
        else if (isset($app->session['_lang']))
        {
            $app->language = $app->session['_lang'];
        }
		$identity = new UserIdentity($username,$password);
		$user_identity_array = array(
			'username' 	=> $username,
			'password' 	=> $password,
			'host' 		=> Yii::app()->request->userHostAddress
		);		
		if($identity->authenticate()){
			$user_identity_array['message'] = 'success';
			$user_identity_array['password'] = '';
			UserAuthentication::model()->setAuthUserInfo($user_identity_array);
			Yii::log("Auth success: user: $username; host: ".Yii::app()->request->userHostAddress, 'info', 'application.controllers.Auth');
		    Yii::app()->user->login($identity);
		    Yii::trace("login success: ".Yii::app()->user->name, 'application.controllers.Auth');
		    Yii::app()->request->redirect(Yii::app()->user->returnUrl.'?_=1374051291544&lang='.$_POST['_lang']);
		}
		else{
			$user_identity_array['message'] = 'failure';
			UserAuthentication::model()->setAuthUserInfo($user_identity_array);
		    Yii::trace("Auth error: ".$identity->errorCode, 'application.controllers.Auth');
		    Yii::log("Auth error: user: ".$username."; password: ".$password."; host: ".Yii::app()->request->userHostAddress, 'info', 'application.controllers.Auth');
			Yii::app()->request->redirect(Yii::app()->user->loginUrl.'?_=1374051291544&lang='.$_POST['_lang']);
		}
	}	

	public function actionLogout(){
		Yii::app()->user->logout();
		Yii::app()->request->redirect(Yii::app()->user->returnUrl);		
	}
}