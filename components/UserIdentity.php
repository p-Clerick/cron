<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{

	private $_id;
	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
		$user = User::model()->findByAttributes(array('username'=>$this->username));
		if($user===null)
            $this->errorCode=self::ERROR_USERNAME_INVALID;
        else if($user->password!==md5($this->password))
            $this->errorCode=self::ERROR_PASSWORD_INVALID;
        else
        {
            if ($user->carrier) {
                Yii::trace(
                    '<'.$user->id.'@'.$user->username.'>: found carrier <'.$user->carrier->name.'>', 
                    'app.components.UserIdentity');

                $carrier = $user->carrier;
            } else if ($user->parent) {
                Yii::trace('<'.$user->id.'@'.$user->username.'>: found parent user <'
                    .$user->parent->id.':'.$user->parent->username.'>', 'app.components.UserIdentity');
                if ($user->parent->carrier) {
                    Yii::trace(
                        '<'.$user->id.'@'.$user->username.'>: found carrier <'.$user->parent->carrier->name.'>', 
                        'app.components.UserIdentity');
                    
                    $carrier = $user->parent->carrier;
                } else {                    
                    Yii::trace(
                        '<'.$user->id.'@'.$user->username.'>: carrier not found', 
                        'app.components.UserIdentity');
                    $carrier = new Carriers; // default carrier
                    $carrier->id = 0;
                }
            } else {
                Yii::trace('<'.$user->id.'@'.$user->username.'>: parent user not found', 
                    'app.components.UserIdentity');
                Yii::trace('<'.$user->id.'@'.$user->username.'>: carrier not found, carrier set to default', 
                        'app.components.UserIdentity');

                $carrier = new Carriers; // default carrier
                $carrier->id = 0;
            }

            $this->_id = $user->id;
            $this->setState('username', $user->username);
            $this->setState('parent_id', $user->parent_id);
            $this->setState('carrier', $carrier);
            $this->errorCode=self::ERROR_NONE;
        }
        return !$this->errorCode;
    }

    public function setCarrier ($carrier) {
        $this->setState('carrier', $carrier);
    }

    public function getId()
    {
        return $this->_id;
    }

}