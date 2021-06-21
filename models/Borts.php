<?php

/** 
 * Borts.php
 *
 * Модель для таблиці з даними про всі борти.
 *
 * Copyright(c) 2014 Підприємство "Візор"
 * 
 * http://mak.lutsk.ua/
 *
 */

class Borts extends CActiveRecord {
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
            'model' => array(self::BELONGS_TO, 'Models', 'models_id'),
            'park' => array(self::BELONGS_TO, 'Parks', 'parks_id'),
            'moveonmap' => array(self::HAS_ONE, 'MoveOnMap', 'borts_id'),
            'moveonschedule' => array(self::HAS_ONE, 'MoveOnSchedule', 'borts_id'),
            'order' => array(self::HAS_ONE, 'Orders', 'borts_id'),
            'orderload' => array(self::HAS_ONE, 'OrdersLoad', 'borts_id'),
            'bortloc' => array(self::HAS_ONE, 'Locations', 'borts_id'),
            'ip' => array(self::HAS_ONE, 'Ip', 'borts_id')
        );
    }

    /**
     * Повертає рядок, що містить ім'я таблиці.
     *
     * @return {string} Ім'я таблиці.
     */
    public function tableName() {
        return 'borts';
    }

    /**
     * Повертає масив із даними про всі борти.
     *
     * @return {array} $arr Масив із даними про всі борти.
     */
    public function getAllBorts() {
        /**
         * SQL-запит до бази даних
         */
        $sql = $this->findAll(array('order' => 'id')); 

        foreach ($sql as $s) {
            $arr[] = array(
                'id' => $s->id,
                'number' => $s->number,
                'state_number' => $s->state_number,
                'parks_id' => $s->parks_id
            );
        }

        return $arr;
    }

    public function getBortById($bort_id){
        $bort = Borts::model()->findByPk($bort_id);
        return (count($bort)>0) ? $bort : false;
    }

    public function getBortByNumber($bortNumber){
        $bort = Borts::model()->find(array(
            'condition'=>'t.number=:bortNumber',
            'params'=>array(':bortNumber'=>$bortNumber)
        ));
        return $bort;
        return (count($bort)>0) ? $bort : false;
    }
}

?>