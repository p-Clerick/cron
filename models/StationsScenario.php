<?php

class StationsScenario extends CActiveRecord {
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    public function relations() {
        return array(
            //'points_control_scenario'=>array(self::BELONGS_TO, 'PointsControlScenario', 'points_control_scenario_id'),
            'stations'=>array(self::BELONGS_TO, 'Stations', 'stations_id'),
            'route_directions'=>array(self::BELONGS_TO, 'RouteDirections', 'route_directions_id'),
            'poe'=>array(self::BELONGS_TO, 'PointsOfEvents', 'points_of_events_id'),
            'route' => array(self::BELONGS_TO, 'Route', 'routes_id'),
            'stations_playback'=>array(self::HAS_ONE, 'StationsPlayback', 'stations_scenario_id'),
        );
    }

    public function tableName() {
        return 'stations_scenario';
    }

    public function getAllStationsScenario() {
        $sql = $this->findAll(array(
            'order' => 'id'
        ));

        foreach ($sql as $s) {
            $arr[] = array(
                'id'                  => $s->id,
                'routes_id'           => $s->routes_id,
                'stations_id'         => $s->stations_id,
                'number'              => $s->number,
                'route_directions_id' => $s->route_directions_id,
                'pc_status'           => $s->pc_status
            );
        }

        return $arr;
    }

    public function getAllStationsScenarioWithPointsOfControl() {
        $sql = $this->findAll(array(
            'condition' => 'pc_status = :st',
            'params'    => array(':st'=>'yes'),
            'order'     => 'routes_id'
        ));

        foreach ($sql as $s) {
            $arr[] = array(
                'id'                  => $s->id,
                'routes_id'           => $s->routes_id,
                'stations_id'         => $s->stations_id,
                'number'              => $s->number,
                'route_directions_id' => $s->route_directions_id
            );
        }

        return $arr;
    }
}

?>