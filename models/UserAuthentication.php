<?php

class UserAuthentication extends CActiveRecord {	

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName(){
		return 'user_authentication';
	}

	/**
	 * Додає в таблицю запис про аутентифікованого користувача або про помилку аутентифікації
	 * @param array $userdata дані про користувача
	 */
	public function	setAuthUserInfo(array $userdata){
		try {
			$user_auth = new $this;
			while (list($key, $value) = each($userdata)) {
				$user_auth[$key]=$value;
			}
			$user_auth->save();
		}
		catch (Exception $e){
			Yii::log($e->getMessage(), "error", "app.models.UserAuthentication");
		}
	}
}