<?php

/** 
 * Stations.php
 *
 * Модель для таблиці з даними про всі зупинки міста.
 *
 * Copyright(c) 2014 Підприємство "Візор"
 * 
 * http://mak.lutsk.ua/
 *
 */

class Stations extends CActiveRecord {
    /**
     * Повертає статичну модель ActiveRecord.
     * 
     * @param {string} $className Ім'я класу.
     * @return {object} Статична модель класу.
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    /**
     * Повертає масив з даними про зв'язки між класами.
     *
     * @return {array} Дані про зв'язки між класами.
     */
    public function relations() {
        return array(
            'stations_scenario'=>array(self::HAS_ONE, 'StationsScenario', 'stations_id'),
            'stations_from'=>array(self::HAS_MANY, 'Stations', 'stations_id_from'),
            'stations_to'=>array(self::HAS_MANY, 'Stations', 'stations_id_to'),
            'moveonschedule'=>array(self::HAS_ONE, 'MoveOnSchedule', 'stations_id'),

        );
    }

    /**
     * Повертає рядок, що містить ім'я таблиці.
     *
     * @return {string} Ім'я таблиці.
     */
    public function tableName() {
        return 'stations';
    }

    /**
     * Повертає масив з даними про всі зупинки міста відсортований по ID зупинок.
     *
     * @return {array} $arr Масив з даними про всі зупинки міста.
     */
    public function getAllStations() {
        $sql = $this->findAll(array(
            'order' => 'id'
        )); 

        if ($sql) {
            foreach ($sql as $s) {
                $arr[] = array(
                    'id'        => $s->id,
                    'latitude'  => $s->latitude,
                    'longitude' => $s->longitude,
                    'name'      => $s->name
                );
            }
            return $arr;
        }
        else
            return null;
    }

    /**
     * Повертає масив з даними про всі зупинки міста відсортований по назві зупинок.
     *
     * @return {array} $arr Масив з даними про всі зупинки міста.
     */
    public function getAllStationsOrderByName() {
        $sql = $this->findAll(array(
            'order' => 'name'
        )); 

        if ($sql) {
            foreach ($sql as $s) {
                $arr[] = array(
                    'id'        => $s->id,
                    'latitude'  => $s->latitude,
                    'longitude' => $s->longitude,
                    'name'      => $s->name
                );
            }
            return $arr;
        }
        else
            return null;
    }
}

?>