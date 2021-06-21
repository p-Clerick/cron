<?php

class LocationsFlights extends CActiveRecord {
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }
    
    public function relations() {
        return array(
            'route' => array(self::BELONGS_TO, 'Route', 'routes_id'),
            'bort'=>array(self::BELONGS_TO, 'Borts', 'borts_id'),
            'schedule'=>array(self::BELONGS_TO, 'Schedules', 'schedules_id'),
            'stations'=>array(self::BELONGS_TO, 'Stations', 'stations_id'),
            'graphs'=>array(self::BELONGS_TO, 'Graphs', 'graphs_id'),
        );
    }

    /**
     * Повертає рядок, що містить ім'я таблиці
     *
     * @return {string} Ім'я таблиці
     */
    public function tableName() {
        return 'locations_in_flights';
    }

    /**
     * Дана функція повертає дані таблиці у вказаному проміжку 'unixtime' для вказаної дати
     *
     * @param {string} $from Початкове значення 'unixtime'
     * @param {string} $to Кінцеве значення 'unixtime'
     * @return {array} $result Масив з даними про дати для розрахунку звіту
     */
    public function getLocationsInFlightsByUnixtimeFromToForDate($from, $to) {
        /**
         * Результат
         */
        $result = array();

        /**
         *SQL-запит
         */
        $sql = Yii::app()->db->createCommand(
            "SELECT schedules_id, flights_number, stations_id, unixtime, borts_id
            from locations_in_flights
            where  unixtime between '" . $from . "' and '". $to . "' order by schedules_id, flights_number, unixtime")->queryAll();

        foreach ($sql as $value) {
            $result[(int)($value['schedules_id'])][(int)($value['flights_number'])][] = array(
                'stations_id' => $value['stations_id'], 
                'unixtime' => $value['unixtime'],
                'borts_id' => $value['borts_id']
            );
        }

        return $result;
    }

    /**
     * Дана функція повертає дані таблиці у вказаному проміжку ID для вказаної дати
     *
     * @param {string} $fromID Початкове значення ID
     * @param {string} $toID Кінцеве значення ID
     * @param {string} $date Рядок, що містить дату у форматі [Y-m-d]
     * @return {array} $result Масив з даними про дати для розрахунку звіту
     */
    public function getLocationsInFlightsByIDFromToForDate($fromID, $toID, $date) {
        /**
         * Результат
         */
        $result = array();

        /**
         *SQL-запит
         */
        $sql = Yii::app()->db->createCommand(
            "SELECT schedules_id, flights_number, stations_id, unixtime, borts_id
            from locations_in_flights
            where  id between '" . $fromID . "' and '". $toID . "' and date(from_unixtime(unixtime)) = '" . $date . "' order by schedules_id, flights_number, unixtime")->queryAll();

        foreach ($sql as $value) {
            $result[(int)($value['schedules_id'])][(int)($value['flights_number'])][] = array(
                'stations_id' => $value['stations_id'], 
                'unixtime' => $value['unixtime'],
                'borts_id' => $value['borts_id']
            );
        }

        return $result;
    }

    /**
     * Дана функція повертає дані таблиці у вказаному проміжку ID для вказаної дати
     *
     * @param {string} $fromUnixtime Початкове значення 'unixtime'
     * @param {string} $toUnixtime Кінцеве значення 'unixtime'
     * @param {string} $date Рядок, що містить дату у форматі [Y-m-d]
     * @return {array} $result Масив з даними про дати для розрахунку звіту
     */
    public function getLocationsInFlightsBySchedulesIDListForDate($schedulesIDList, $fromUnixtime, $toUnixtime) {
        /**
         * Результат
         */
        $result = array();

        /**
         *SQL-запит
         */
        $sql = Yii::app()->db->createCommand(
            "SELECT schedules_id, flights_number, stations_id, unixtime, borts_id
            from locations_in_flights
            where  schedules_id in (" . $schedulesIDList . ") and unixtime between '" . $fromUnixtime . "' and '". $toUnixtime . "' order by schedules_id, flights_number, unixtime")->queryAll();

        foreach ($sql as $value) {
            $result[(int)($value['schedules_id'])][(int)($value['flights_number'])][] = array(
                'stations_id' => $value['stations_id'], 
                'unixtime' => $value['unixtime'],
                'borts_id' => $value['borts_id']
            );
        }

        return $result;
    }

    /**
     * Дана функція повертає згруповані дані про активні розклади для вказаної дати
     * та у вказаному проміжку ID
     *
     * @param {string} $fromID Початкове значення ID
     * @param {string} $toID Кінцеве значення ID
     * @param {string} $date Рядок, що містить дату у форматі [Y-m-d]
     * @return {array} $result Масив із згрупованими даними про активні розклади для вказаної дати
     */
    public function getAllActiveSchedulesByIDFromToForDate($fromID, $toID, $date) {
        /**
         *SQL-запит
         */
        $sql = Yii::app()->db->createCommand(
            "SELECT schedules_id, graphs_id, routes_id
            from locations_in_flights
            where  id between '" . $fromID . "' and '". $toID . "' and date(from_unixtime(unixtime)) = '" . $date . "' group by schedules_id order by schedules_id")->queryAll();

        return $sql;
    }
}

?>