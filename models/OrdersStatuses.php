<?php
class OrdersStatuses extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(
            'order'=>array(self::HAS_MANY, 'Orders', 'order_statuses_id'),
        );
    }

    public function tableName()
    {
        return 'orders_statuses';
    }
}
?>