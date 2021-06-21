<?php
/**
 * Модель User
 *
 * @property integer $id
 * @property string $login
 * @property string $password
 * @property string $role
 */
class User extends CActiveRecord {

	const ROLE_ADMIN = 'admin';
	const ROLE_SUPERADMIN = 'superadmin';
	const ROLE_DISP = 'disp';
	const ROLE_CW = 'cw';
	const ROLE_FM = 'fm';
	const ROLE_SU = 'superuser';
	const ROLE_GOV = 'gov';
	const ROLE_STATE_AGENCIES = 'state_agencies';

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function relations(){
		return array(
			'carrier'=>array(self::HAS_ONE, 'Carriers', 'user_id'),
			'children'=>array(self::HAS_MANY, 'User', 'parent_id'),
			'parent'=>array(self::BELONGS_TO, 'User', 'parent_id'),
		);
	}

	public function getRoleTitle(){
		switch($this->role){
			case USER::ROLE_ADMIN: return Yii::app()->session['Admin'];
			case USER::ROLE_SUPERADMIN: return Yii::app()->session['SuperAdmin'];
			case USER::ROLE_DISP: return Yii::app()->session['RoleDisp'];
			case USER::ROLE_CW: return Yii::app()->session['RoleCw'];
			case USER::ROLE_FM: return Yii::app()->session['Carrier'];
			case USER::ROLE_SU: return Yii::app()->session['SuperUser'];
			case USER::ROLE_GOV: return Yii::app()->session['Goverment'];
			case USER::ROLE_STATE_AGENCIES: return Yii::app()->session['State_agencies'];
			default: return false;
		}
	}

	public function tableName(){
		return 'user';
	}
		
}