<?php
class OrdersLoadHistory extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function tableName()
    {
        return 'orders_load_history';
    }   
}
?>