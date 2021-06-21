<?php

/** 
 * ConditionsOfOutstandingStations.php
 *
 * Модель для таблиці з даними про зупинки, які необхідно ігнорувати при розрахунку звітів.
 *
 * Copyright(c) 2014 Підприємство "Візор"
 * 
 * http://mak.lutsk.ua/
 *
 */

class ConditionsOfOutstandingStations extends CActiveRecord {
    /**
     * Повертає статичну модель ActiveRecord.
     * 
     * @param {string} $className Ім'я класу.
     * @return {object} Статична модель класу.
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * Повертає рядок, що містить ім'я таблиці.
     *
     * @return {string} Ім'я таблиці.
     */
    public function tableName() {
        return 'conditions_of_outstanding_stations';
    }

    /**
     * Повертає масив із даними про всі зупинки, які необхідно ігнорувати при розрахунку звітів.
     *
     * @return {array} $result Масив із даними про всі кінцеві зупинки, 
     *                         які необхідно ігнорувати при розрахунку звітів.
     */
    public function getAllConditionsOfOutstandingStations() {
        /**
         * Результат
         */
        $result = array();

        /**
         * SQL-запит до бази даних
         */
        $sql = $this->findAll(array(
            'order' => 'id'
        ));

        foreach ($sql as $s) {
            $result[] = array(
                'id' => $s->id,
                'stations_id' => $s->stations_id,
                'routes_id_list' => $s->routes_id_list,
                'day_from' => $s->day_from,
                'day_to' => $s->day_to,
                'time_from' => $s->time_from,
                'time_to' => $s->time_to,
                'weekdays_list' => $s->weekdays_list,
                'is_end' => $s->is_end
            );
        }

        return $result;
    }

    /**
     * Повертає масив із даними про всі кінцеві зупинки, які необхідно ігнорувати при розрахунку звітів.
     *
     * @return {array} $result Масив із даними про всі кінцеві зупинки, 
     *                         які необхідно ігнорувати при розрахунку звітів.
     */
    public function getAllConditionsOfOutstandingEndStations() {
        /**
         * Результат
         */
        $result = array();

        /**
         * SQL-запит до бази даних
         */
        $sql = $this->findAll(array(
            'condition' => 'is_end = :is_end',
            'params' => array(':is_end' => 'yes'),
            'order' => 'id'
        ));

        foreach ($sql as $s) {
            $result[] = array(
                'id' => $s->id,
                'stations_id' => $s->stations_id,
                'routes_id_list' => $s->routes_id_list,
                'day_from' => $s->day_from,
                'day_to' => $s->day_to,
                'time_from' => $s->time_from,
                'time_to' => $s->time_to,
                'weekdays_list' => $s->weekdays_list,
                'is_end' => $s->is_end
            );
        }

        return $result;
    }

    /**
     * Видаляє записи для вказаного списку ідентифікаторів.
     *
     * @param {string} $idArrayString Рядок, що містить масив ідентифікаторів.
     */
    public function deleteByIDList($idArrayString) {
        $this->model()->deleteAll(array(
            'condition' => 'id in (' . $idArrayString . ')'
        ));
    }
}

?>