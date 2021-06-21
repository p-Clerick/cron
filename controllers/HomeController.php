<?php

class HomeController extends CController
{
	public function actionIndex(){
		
		if(Yii::app()->user->isGuest){
			Yii::app()->request->redirect(Yii::app()->user->loginUrl);
		} else {
			if(Yii::app()->user->checkAccess('admin')){
				Yii::app()->getClientScript()->registerPackage('admin');
				$this->render('admin');	
			} else if(Yii::app()->user->checkAccess('superadmin')){
				Yii::app()->getClientScript()->registerPackage('superadmin');
				$this->render('superadmin');	
			}
			else if(Yii::app()->user->checkAccess('gov')){
				Yii::app()->getClientScript()->registerPackage('gov');
				$this->render('gov');	
			}
			else if(Yii::app()->user->checkAccess('state_agencies')){
				Yii::app()->getClientScript()->registerPackage('state_agencies');
				$this->render('state_agencies');	
			}
		}		
	}

	public function actionGuest(){
		$identity = new UserIdentity('guest', '');
		$carrier = new Carriers;
		$carrier->id = 0;
		$identity->setCarrier($carrier);
		Yii::app()->user->login($identity);

		Yii::app()->getClientScript()->registerPackage('guest');
		$this->render('guest');
	}
}