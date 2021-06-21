<?php

class WebUser extends CWebUser {
    private $_model = null;

    function getRole() {
        if($user = $this->getModel()){
            // в таблице User есть поле role
            return $user->role;
        }
    }

    private function getModel(){
        if (!$this->isGuest && $this->_model === null){
            $this->_model = User::model()->findByPk($this->id, array('select' => 'role'));
        }
        return $this->_model;
    }
    function checkUser($user){
         	  if($user->parent_id === '0'){
                  return null;
		      }
		      if($user->parent_id === '1'){
		      	 if($user->role === 'fm'){
					return $arr;
		      	 }
		      	 else{
		      	 }
		      }
		      else{
		      	   $arr = array( "id" => $user->parent_id,"name" => $model->username,"nick" => $model->carrier->nick, "carrier_id" => $model->carrier->id);
				   return $arr;
		      }
    }

}