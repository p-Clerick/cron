<?php
	class PointsOfEvents extends CActiveRecord
	{
	    public static function model($className=__CLASS__)
	    {
	        return parent::model($className);
	    }
	    public function relations()
	    {
	        return array(
	        	'stations_scenario' => array(self::HAS_MANY, 'StationsScenario', 'points_of_events_id'),
	        //    'points_OfEvents_scenario'=>array(self::HAS_ONE, 'PointsOfEventsScenario', 'points_OfEvents_id'),
	        );
	    }

	    public function tableName()
	    {
	        return 'points_of_events';
	    }
        /**
        * Функція для підрахунку відстані між 2 координатами
        *
        * @param float $lat1
        * @param float $lng1
        * @param float $lat2
        * @param float $lng2
        * @return int
        */
        public function dist_calc ($lat1,$lng1,$lat2,$lng2) {
            $lat1 = (double) (floor($lat1/100)*100+(($lat1-floor($lat1/100)*100)*100/60))/100;
            $lng1 = (double) (floor($lng1/100)*100+(($lng1-floor($lng1/100)*100)*100/60))/100;

            $lat2 = (double) (floor($lat2/100)*100+(($lat2-floor($lat2/100)*100)*100/60))/100;
            $lng2 = (double) (floor($lng2/100)*100+(($lng2-floor($lng2/100)*100)*100/60))/100;

            $pi = 3.14159265358979;
            $rad = 6372795;
            $lat1 =  $lat1*$pi/180;
            $lat2 =  $lat2*$pi/180;
            $lng1 =  $lng1*$pi/180;
            $lng2 =  $lng2*$pi/180;

            $nuz = sin($lat1)*sin($lat2)+cos($lat1)*cos($lat2)*cos(abs($lng2-$lng1));
            $verh = sqrt((pow(cos($lat2)*sin(abs($lng2-$lng1)),2))+
                pow((cos($lat1)*sin($lat2)-sin($lat1)*cos($lat2)*cos(abs($lng2-$lng1))),2));

            $res_gav_sin = atan($verh/$nuz);
            $return_distance = $res_gav_sin*$rad;
            return  intval($return_distance);
        }        
	}


?>