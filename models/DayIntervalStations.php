<?php
class DayIntervalStations extends CActiveRecord {
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'day_interval_stops_city';
	}


	public function getAllDayIntervalStations() 
    {

        $sql = $this->findAll(array(
            'order' => 'id'
        ));

        foreach ($sql as $s) 
        {
            $arr[] = array(
                'id'                   => $s->id,
                'day_interval_city_id' => $s->day_interval_city_id,
                'stations_id_from'	   => $s->stations_id_from,
                'stations_id_to'	   => $s->stations_id_to,
                'interval'			   => $s->interval
            );
        }

        return $arr;
    }	
}