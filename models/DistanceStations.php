<?php

class DistanceStations extends CActiveRecord {
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return 'stations_distances';
    }

    public function getAllStationsDistances() {
        $sql = $this->findAll(array(
            'order' => 'id'
        ));

        foreach ($sql as $s) 
        {
            $arr[] = array(
                'id' => $s->id,
                'stations_id_from' => $s->stations_id_from,
                'stations_id_to' => $s->stations_id_to,
                'distance_in_meters' => $s->distance_in_meters
            );
        }

        return $arr;
    }
}

?>