<?php

/** 
 * IgnoreStationsController.php
 *
 * Контоллер для веб-інтерфейсу таблиці "conditions_of_outstanding_stations".
 *
 * Copyright(c) 2014 Підприємство "Візор"
 * 
 * http://mak.lutsk.ua/
 *
 */

class IgnoreStationsController extends Controller {
    /**
     * Створює новий запис в таблиці
     */
    public function actionCreate() {
        /**
         * Інформація для збереження
         */ 
        $data = CJSON::decode(Yii::app()->request->getPost('data'), true);

        /**
         * Новий запис в таблиці таблиці "conditions_of_outstanding_stations"
         */
        $record = new ConditionsOfOutstandingStations;

        while (list($key, $value) = each($data)) {
            switch ($key) {
                case "stations_id":
                    if ($value == '')
                        $record->$key = null;
                    else
                        $record->$key = $value;
                    break;
                case "routes_id_list":
                    if ($value == '')
                        $record->$key = null;
                    else
                        $record->$key = $value;
                    break;
                case "day_from":
                    if ($value == '')
                        $record->$key = null;
                    else
                        $record->$key = $value;
                    break;
                case "day_to":
                    if ($value == '')
                        $record->$key = null;
                    else
                        $record->$key = $value;
                    break;
                case "time_from":
                    if ($value == '')
                        $record->$key = null;
                    else
                        $record->$key = $this->hmsToSeconds($value);
                    break;
                case "time_to":
                    if ($value == '')
                        $record->$key = null;
                    else
                        $record->$key = $this->hmsToSeconds($value);
                    break;
                case "weekdays_list":
                    if ($value == '')
                        $record->$key = null;
                    else
                        $record->$key = $value;
                    break;
                case "is_end":
                    if ($value)
                        $record->$key = 'yes';
                    else
                        $record->$key = 'no';                    
                    break;
                default:
                    $record->$key = $value;
                    break;
            }
        }

        // Зберігаємо запис
        $record->save();

        // Кодуємо результат у формат JSON
        echo CJSON::encode(array(
            'success' => true,
            'data' => $record
        ));
    }

	/**
	 * Читає записи з таблиці
	 */
    public function actionRead() {
    	/**
    	 * Результат
    	 */
    	$result = array();

    	/**
         * Масив всіх умов ігнорування зупинок
         */
    	$ignoreStations = ConditionsOfOutstandingStations::model()->getAllConditionsOfOutstandingStations();

    	/**
    	 * Запис в масиві $result
    	 */
    	$resultRecord = array();

    	foreach ($ignoreStations as $ignoreStation) {

    		$resultRecord['id'] = $ignoreStation['id'];
            $resultRecord['stations_id'] = $ignoreStation['stations_id'];
            $resultRecord['routes_id_list'] = $ignoreStation['routes_id_list'];
    		$resultRecord['day_from'] = $ignoreStation['day_from'];
    		$resultRecord['day_to'] = $ignoreStation['day_to'];

    		$resultRecord['time_from'] = $this->secondsToHms($ignoreStation['time_from']);
            $resultRecord['time_to'] = $this->secondsToHms($ignoreStation['time_to']);

    		$resultRecord['weekdays_list'] = $ignoreStation['weekdays_list'];

            if ($ignoreStation['is_end'] == 'yes')
                $resultRecord['is_end'] = true;
            else
                $resultRecord['is_end'] = false;

    		$result[] = $resultRecord;
    	}

        // Кодуємо результат у формат JSON
        echo CJSON::encode(array(
        	'success' => true,
        	'data' => $result
        ));
	}

	/**
	 * Оновлює записи в таблиці
	 */
	public function actionUpdate() {
		/**
         * Інформація для збереження
         */ 
        $data = CJSON::decode(Yii::app()->request->getPut('data'));

        /**
         * Запис в таблиці з умовами ігнорування зупинок
         */
        $record = ConditionsOfOutstandingStations::model()->findByPk($data['id']);

    	while (list($key, $value) = each($data)) {
            switch ($key) {
                case "stations_id":
                    if ($value == '')
                        $record->$key = null;
                    else
                        $record->$key = $value;
                    break;
                case "routes_id_list":
                    if ($value == '')
                        $record->$key = null;
                    else
                        $record->$key = $value;
                    break;
                case "day_from":
                    if ($value == '')
                        $record->$key = null;
                    else
                        $record->$key = $value;
                    break;
                case "day_to":
                    if ($value == '')
                        $record->$key = null;
                    else
                        $record->$key = $value;
                    break;
                case "time_from":
                    if ($value == '')
                        $record->$key = null;
                    else
                        $record->$key = $this->hmsToSeconds($value);
                    break;
                case "time_to":
                    if ($value == '')
                        $record->$key = null;
                    else
                        $record->$key = $this->hmsToSeconds($value);
                    break;
                case "weekdays_list":
                    if ($value == '')
                        $record->$key = null;
                    else
                        $record->$key = $value;
                    break;
                case "is_end":
                    if ($value)
                        $record->$key = 'yes';
                    else
                        $record->$key = 'no';                    
                    break;
                default:
                    $record->$key = $value;
                    break;
            }
		}
        
        $record->save();

		// Кодуємо результат у формат JSON
        echo CJSON::encode(array(
        	'success' => true,
        	'data' => $record
        ));
	}

	/**
	 * Видаляє записи з таблиці для вказаного списку ідентифікаторів
	 */
	public function actionDeleteByIDList() {
		/**
         * Список ідентифікаторів записів для видалення
         */ 
        $arrayID = CJSON::decode((Yii::app()->request->getParam('id_list')));

        // Видаляємо записи з таблиці для вказного списку ідентифікаторів
        ConditionsOfOutstandingStations::model()->deleteByIDList(implode(",", $arrayID));
    }

    /**
     * Приймає кількість секунд від початку доби та перетворює 
     * в час у форматі "h:m:s" (години:хвилини:секунди).
     *
     * @param {int} $seconds Кількість секунд від початку доби.
     * @return {string} $result Рядок з часом у форматі "h:m:s" (години:хвилини:секунди).
     */
    private function secondsToHms($seconds) {
        /**
         * Результат роботи функції
         */
        $result = "";

        if ($seconds != null) {
            /**
             * Кількість годин
             */
            $hour = floor($seconds / 3600);

            /**
             * Рядок, що містить кількість годин
             */
            $hourStr = "";

            if ($hour < 10)
                $hourStr = '0' . $hour;
            else
                $hourStr = $hour;

            $seconds %= 3600;

            /**
             * Кількість хвилин
             */
            $minutes = floor($seconds / 60);

            /**
             * Рядок, що містить кількість хвилин
             */
            $minutesStr = "";

            if ($minutes < 10)
                $minutesStr = '0' . $minutes;
            else
                $minutesStr = $minutes;

            $seconds %= 60;

            /**
             * Рядок, що містить кількість секунд
             */
            $secondsStr = "";

            if ($seconds < 10)
                $secondsStr = '0' .$seconds;
            else
                $secondsStr = $seconds;

            $result = $hourStr . ':' . $minutesStr . ':' . $secondsStr;
        }

        return $result;
    }

    /**
     * Приймає кількість секунд від початку доби та перетворює 
     * в час у форматі "h:m:s" (години:хвилини:секунди).
     *
     * @param {string} $hms Рядок з часом у форматі "h:m:s" (години:хвилини:секунди).
     * @return {int} $result Кількість секунд від початку доби.
     */
    private function hmsToSeconds($hms) {
        /**
         * Результат роботи функції
         */
        $result = null;

        list($hours, $minutes, $seconds) = split('[:]', $hms);

        $result = $hours * 60 * 60 + $minutes * 60 + $seconds;

        return $result;
    }
}

?>