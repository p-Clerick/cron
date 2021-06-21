<?php

class DayInterval extends CActiveRecord
{
    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function relations(){
        return array(
            'intervals' => array(self::HAS_MANY, 'ControlPointInterval', 'dayintervalid'),
            'route' => array(self::BELONGS_TO, 'Route', 'rouiteid'),
        );
    }

    /**
     *
     * @return Time Час початку періода доби як об'єкт 
     */
    public function getStartTime(){
        return new Time($this->starttime);
    }

    /**
     *
     * @return Time Час закінчення періода доби як об'єкт
     */
    public function getEndTime(){
        return new Time($this->endtime);
    }

    /**
     * Створює інтервал $interval для сценарію $scenarioId контрольної точки
     * та додає цей інтервал до даного періоду доби
     * @param integer $scenarioId ID сценарія контрольної точки
     * @param integer $interval Інтерал до наступної по-порядку конрольної точки
     * маршрута (в секундах) 
     * @return boolean Успіх операції
     */
    public function addControlPointInterval($scenarioId, $interval){
        $controlPointInterval = new ControlPointInterval;
        $controlPointInterval->dayintervalid = $this->id;
        $controlPointInterval->point_scenario_id = $scenarioId;
        $controlPointInterval->interval = $interval;
        return $controlPointInterval->save();
    }

    /**
     * Перевіряє чи належить час $time до даного періоду доби
     * 
     * @param $time час, який перевіряють на належність
     * @return Boolean Результат перевірки
     */
    public function belongs($time){
        $startTime = new Time($this->starttime);
        $endTime = new Time($this->endtime);
        if ($time->getTimeInSeconds() >= 86400) {
            $time = new Time($time->getTimeInSeconds() - 60 * 60 * 24);
        }
        if( (Time::compare($time, $startTime) == 1 || 
                Time::compare($time, $startTime) == -1) 
                    && Time::compare($endTime, $time) == 1 ){            
            return true;
        } else {
            return false;
        }
    }

    /**
    * Знаходить період доби, до якого належить час $time
    * @param Route $route
    * @param Time $time 
    * @return DayInterval Період доби, до якого належить час $time
    */
    public static function searchDayIntByTime($route, $time){
        $dayInts = $route->dayintervals;
        foreach($dayInts as $item){
            if($item->belongs($time)){
                return $item;
            }
        }
        throw new CException('Не знайдено період доби для часу '.$time->getFormattedTime());        
    }

    public function tableName(){
        return 'day_interval';
    }
}