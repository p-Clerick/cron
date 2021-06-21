<?php

class RolesController extends CController
{
	public function actionRead(){


		$roles = array();
		if(Yii::app()->user->checkAccess('superuser')){
			$roles = array(
				array(User::ROLE_SUPERADMIN, Yii::app()->session['SuperAdmin']),
				array(User::ROLE_FM, Yii::app()->session['Carrier']),
			);
		} else if(Yii::app()->user->checkAccess('fm')){
			$roles = array(
				array(User::ROLE_ADMIN, Yii::app()->session['Admin']),
				array(User::ROLE_CW, Yii::app()->session['RoleCw']),
				array(User::ROLE_DISP, Yii::app()->session['RoleDisp']),
			);
		} else if(Yii::app()->user->checkAccess('admin')){
			$roles = array(
				array(User::ROLE_CW, Yii::app()->session['RoleCw']),
				array(User::ROLE_DISP, Yii::app()->session['RoleDisp']),
			);
		} else if(Yii::app()->user->checkAccess('superadmin')) {
			$roles = array(
				array(User::ROLE_FM, Yii::app()->session['Carrier']),
			);
		}
		$res = array(
			'success' => true,
			'rows' => $roles
		);
		echo json_encode($res);
	}
}