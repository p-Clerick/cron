<?php
class ClientSendCommandsLoad extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function tableName()
    {
        return 'client_send_commands_load';
    }
}
?>