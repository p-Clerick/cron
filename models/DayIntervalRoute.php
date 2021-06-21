<?php
class DayIntervalRoute extends CActiveRecord {
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function relations()
    {
        return array(
            'routes' => array(self::BELONGS_TO, 'Route', 'routes_id'),
        );
    }

	public function tableName()
	{
		return 'day_interval_route';
	}

	public function getAllDayIntervalRoute() 
    {

        $sql = $this->findAll(array(
            'order' => 'id'
        ));

        foreach ($sql as $s) 
        {
            $arr[] = array(
                'id'                	=> $s->id,
                'routes_id'        		=> $s->routes_id,
                'day_interval_city_id'  => $s->day_interval_city_id
            );
        }

        return $arr;
    }
		
}