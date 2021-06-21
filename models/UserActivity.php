<?php
/**
 * Модель User
 *
 * @property integer $id
 * @property string $login
 * @property string $password
 * @property string $role
 */
class UserActivity extends CActiveRecord {

	

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function relations(){
		return array(			
			'userid'=>array(self::HAS_MANY, 'User', 'user_id'),
		);
	}

	public function tableName(){
		return 'user_activity';
	}

	/**
	 * Додає в таблицю запис про завершення роботи контроллера
	 * @param array $user_operation_array дані про користувача та виконувану ним подію
	 */
	public function	setUserActivityInfo(array $user_operation_array){
		try {
			$user_activity = new $this;
			while (list($key, $value) = each($user_operation_array)) {
				$user_activity[$key]=$value;
			}
			$user_activity->save();
		}
		catch (Exception $e){
			Yii::log($e->getMessage(), "error", "app.models.UserActivity");
		}
	}	
		
}