<?php

/** 
 * TestReportEndStops.php
 *
 * Модель для таблиці з даними рохрахунку звітів "Кінцеві зупинки".
 *
 * Copyright(c) 2014 Підприємство "Візор"
 * 
 * http://mak.lutsk.ua/
 *
 */

class TestReportEndStops extends CActiveRecord {
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
            'route' => array(self::BELONGS_TO, 'Route', 'routes_id'),
            'graph' => array(self::BELONGS_TO, 'Graphs', 'graphs_id'),
            'station'=> array(self::BELONGS_TO, 'Stations', 'stations_id'),
            'bort' => array(self::BELONGS_TO, 'Borts', 'borts_id'),
            'park' => array(self::BELONGS_TO, 'Parks', 'parks_id'),
            'carrier' => array(self::BELONGS_TO, 'Carriers', 'carriers_id'),
        );
    }

    /**
     * Повертає рядок, що містить ім'я таблиці.
     *
     * @return {string} Ім'я таблиці.
     */
	public function tableName() {
		return 'report_end_stops';
	}	

    /**
     * Видаляє записи з таблиці "test_report_end_stops" для вказаного 
     * списку активних графіків та дати.
     *
     * @param {string} $activeGraphsString Рядок, що містить масив активних графіків.
     * @param {string} $date Рядок, що містить дату у форматі [Y-m-d].
     */
    public function deleteByGraphsIDListForDate($activeGraphsString, $date) {
        $this->model()->deleteAll(array(
            'condition' => 'graphs_id in (' . $activeGraphsString . ') and date = :date',
            'params' => array(':date' => $date))
        );
    }
}