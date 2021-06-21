<?php

/** 
 * ScheduleTimes.php
 *
 * Модель для таблиці з даними про плановий рух маршруток по графіку.
 *
 * Copyright(c) 2014 Підприємство "Візор"
 * 
 * http://mak.lutsk.ua/
 *
 */

class ScheduleTimes extends CActiveRecord {
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
     * Повертає масив з даними про зв'язки між класами.
     *
     * @return {array} Дані про зв'язки між класами.
     */
    public function relations() {
    	return array(
    		'schedules' => array(self::BELONGS_TO, 'Schedules', 'schedules_id'),
    		'points_control_scenario' => array(self::BELONGS_TO, 'PointsControlScenario', 'points_control_scenario_id'),
            'scenario' => array(self::BELONGS_TO, 'ContolPointScenario', 'points_control_scenario_id')
	    );
    }

    public function getTime() {
        return new Time($this->time);
    }

    public function copyFrom($time, $targetSchedule) {
        $this->schedules_id = $targetSchedule->id;
        $this->flight_number = $time->flight_number;
        $this->points_control_scenario_id = $time->points_control_scenario_id;
        $this->points_control_scenario_id = $time->points_control_scenario_id;
        $this->time = $time->time;
    }
    
    /**
     * Повертає рядок, що містить ім'я таблиці.
     *
     * @return {string} Ім'я таблиці.
     */
	public function tableName() {
        return 'schedule_times';
    }

    /**
     * Повертає масив з даними про всі розклади руху по маршруту.
     *
     * @param {string} $fromID Початкове значення ID.
     * @param {string} $toID Кінцеве значення ID.
     * @return {array} $result Масив з даними про дати для розрахунку звіту.
     */
    public function getAllScheduleTimes() {
        $sql = $this->findAll(array(
            'order' => 'id'
        ));

        foreach ($sql as $s) {
            $arr[] = array(
                'id'                  => $s->id,
                'schedules_id'        => $s->schedules_id,
                'time'                => $s->time,
                'flights_number'      => $s->flights_number,
                'stations_id'         => $s->stations_id,
                'pc_number'           => $s->pc_number
            );
        }

        return $arr;
    }

    /**
     * Повертає масив з даними про всі розклади руху по маршруту для списку із вказаних ID розкладів.
     *
     * TODO: Знайти спосіб однозначного і правильного сортування вибірки.
     *
     * @param {string} $activeSchedulesIDString Рядок, що містить масив активних розкладів.
     * @return {array} $result Масив з даними про всі розклади руху по маршруту 
     *                         для списку із вказаних ID розкладів.
     */
    public function getAllScheduleTimesForSchedulesIDArray($activeSchedulesIDString) {
        /**
         * Результат
         */
        $result = array();

        /**
         *SQL-запит
         */
        $sql = Yii::app()->db->createCommand(
            "SELECT schedules_id, flights_number, stations_id, time
            from schedule_times
            where  schedules_id in (" . $activeSchedulesIDString . ") order by schedules_id, flights_number, pc_number")->queryAll();

        foreach ($sql as $value) {
            $result[(int)($value['schedules_id'])][(int)($value['flights_number'])][] = array(
                'stations_id' => $value['stations_id'], 
                'time' => $value['time']
            );
        }

        return $result;
    }
}

?>