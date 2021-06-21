<?php
class DayIntervalCity extends CActiveRecord {
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'day_interval_city';
	}

	public function getAllDayIntervalCity() 
    {

        $sql = $this->findAll(array(
            'order' => 'id'
        ));

        foreach ($sql as $s) 
        {
            $arr[] = array(
                'id'                => $s->id,
                'start_time'        => $s->start_time,
                'end_time'         	=> $s->end_time,
                'schedules_type_id'	=> $s->schedules_type_id,
                'standart' 			=> $s->standart
            );
        }

        return $arr;
    }
		
}