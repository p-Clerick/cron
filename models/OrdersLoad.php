<?php
class OrdersLoad extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function tableName()
    {
        return 'orders_load';
    }
    public function relations()
    {
        return array(
            'bort'=>array(self::BELONGS_TO, 'Borts', 'borts_id'),
            'graph'=>array(self::BELONGS_TO, 'Graphs', 'graphs_id'),

        );
    }

     public function SheduleTypeId()
    {
                    $weekend = date("w");
                    if($weekend==0 || $weekend==6) {return 2;} else {return 1;}
    }
        public function CarrierBortList($carrier_id){
        $borts =OrdersLoad::model()->with('bort','bort.park')->findAll(array(
                                'condition' => 'park.carriers_id =:caridd',
                                'params'=>array(':caridd'=>$carrier_id),                 
                            ));
                            $bort_arr = "(";
                            foreach($borts as $bort){
                                $bort_arr .=  $bort->bort->id;  
                                $bort_arr .=",";
                            }           
                            $bort_arr = substr($bort_arr, 0, -1);           
                            $bort_arr .=")";
         if ($borts){
             return $bort_arr;
         }   
         else{
             return "(0)";
         }                
    }
}
?>