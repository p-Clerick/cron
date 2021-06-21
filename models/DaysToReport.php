<?php

/** 
 * DaysToReport.php
 *
 * Модель для таблиці з даними про дати для розрахунку звітів.
 *
 * Copyright(c) 2014 Підприємство "Візор"
 * 
 * http://mak.lutsk.ua/
 *
 */

class DaysToReport extends CActiveRecord {
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
        return 'days_to_calc_report';
    }

    /**
     * Повертає дані про дати для розрахунку звітів за вказаною датою.
     *
     * @param {string} $date Дата у форматі [Y-m-d].
     * @return {int} Масив з даними про дати для розрахунку звіту.
     */
    public function getDaysToReportByDate($date) {
    	$sql = $this->findByAttributes(array('date' => $date));

        return $sql ? array (
            'id'                  		=> $sql->id,
            'date'           			=> $sql->date,
            'locations_flights_id_from' => $sql->locations_flights_id_from,
            'locations_flights_id_to'   => $sql->locations_flights_id_to,
            'found_days' 				=> $sql->found_days
        ) : [];
    }
}

