<?php
class Orders extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function relations()
    {
        return array(
            'bort'=>array(self::BELONGS_TO, 'Borts', 'borts_id'),
            'status'=>array(self::BELONGS_TO, 'OrdersStatuses', 'order_statuses_id'),
            'graph'=>array(self::BELONGS_TO, 'Graphs', 'graphs_id'),
            //'bort_status'=>array(self::BELONGS_TO, 'BortStatuses', 'bort_statuses_id'),

        );
    }

    public function tableName()
    {
        return 'orders';
    }

    public function checkOrderForBortToday($borts_id,$from,$to){
        $order = Orders::model()->find(array(
            'condition'=>'t.from <=:from and :to <= t.to and t.borts_id =:bortsid',
            'params'=>array(':bortsid'=>$borts_id,':from'=>$from,':to'=>$to )
        ));
        return (count($order) >0) ? $order : false;
    }

    public function checkOrderForGraphToday($graphs_id,$from,$to){
        $order = Orders::model()->find(array(
            'condition'=>'t.from <=:from and :to <= t.to and  t.graphs_id =:graphsid',
            'params'=>array(':graphsid'=>$graphs_id,':from'=>$from,':to'=>$to)
        ));
        return (count($order) >0) ? $order : false;
    }
}
?>