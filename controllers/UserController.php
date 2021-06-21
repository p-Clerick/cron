<?php

class UserController extends Controller{
	
	public function actionRead($id = -1){
		if($id = -1){
			$this->findAllUsers();
		} else {
			$this->findUser($id);
		}
	}

	public function actionCreate(){
		if( Yii::app()->user->checkAccess('createUser') ){
			$data = json_decode(stripslashes(file_get_contents('php://input')), true);
			$data = $data['rows'];

			$user = new User;
			$user->username = $data['username'];
			$user->parent_id = Yii::app()->user->getId();
			$user->password = md5($data['password']); 	
			$user->role = $data['role'];
			$user->created = date('Y-m-d');
			$user->save();	
			echo json_encode($this->findUser($user->id));
		} else {
			echo "{success: false, msg: 'Not permitted!}";
		}		
	}	

	public function actionDelete($id){
		if( Yii::app()->user->checkAccess('deleteUser') ){			
			$user = User::model()->findByPk($id);
			$user->delete();	
			echo "{success: true}";
		} else {
			echo "{success: false}";
			echo "Not permitted!";
		}		
	}	

	protected function findAllUsers(){
		$users = User::model()->findAll('parent_id = :pid', array(':pid' => Yii::app()->user->getId()));
		$result = array(
			'success'=>true,
			'rows'=>array(),
		);
		foreach($users as $user){
			$result['rows'][] = array(
				'id'	=> $user->id,
				'username' 	=> $user->username,
				'role' 	=> $user->getRoleTitle(),
				'date'	=> $user->created,
			);
		}
		echo json_encode($result);
	}

	protected function findUser($id){
		$user = User::model()->findByPk($id);
		$result = array(
			'success'=>true,
			'rows'=>array(
				'id'	=> $user->id,
				'username' 	=> $user->username,
				'role' 	=> $user->getRoleTitle(),
				'date'	=> $user->created,
			),
		);
		return $result;
	}
}

?>