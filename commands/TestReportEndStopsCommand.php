<?php

/**
 * TestReportEndStopsCommand.php
 *
 * Команда для розрахунку звіту "Кінцеві зупинки".
 *
 * Copyright(c) 2014 Підприємство "Візор"
 *
 * http://mak.lutsk.ua/
 *
 */

// Імпортуємо необхідні моделі
Yii::import('application.models.DaysToReport');                        // Дані про дати для розрахунку звітів
Yii::import('application.models.StationsScenario');                    // Дані про сценарії зупинок міста
Yii::import('application.models.Route');                               // Дані про маршрути міста
Yii::import('application.models.Borts');                               // Дані про борти міста
Yii::import('application.models.LocationsFlights');                    // Дані про фактичний рух маршруток по графіку
Yii::import('application.models.TestExecutionsCommands');              // Дані про виконання команд
Yii::import('application.models.ScheduleTimes');                       // Дані про плановий рух маршруток по графіку
Yii::import('application.models.TestReportEndStops');                  // Розраховані дані звіту "Кінцеві зупинки"
Yii::import('application.models.ConditionsOfOutstandingStations');     // Дані про зупинки, які необхідно ігнорувати при розрахунку звітів

/**
 * Клас команди для розрахунку звіту "Кінцеві зупинки"
 */
class TestReportEndStopsCommand extends CConsoleCommand
{
    /**
     * Ця функція запускається при запуску команди на виконання
     *
     * @param {array} $args Аргументи командної стрічки
     * @return {int} $result Успішність виконання команди (0 - команда виконана успішно)
     */
    public function run($args)
    {
        /**
         * Масив з даними про дати для яких необхідно розрахувати звіт
         */
        $datesToCalcReport = $this->getDatesToCalcReport($args);

        /**
         * Масив всіх сценаріїв зупинок з точками контролю
         */
        $stationsScenario = StationsScenario::model()->getAllStationsScenarioWithPointsOfControl();

        /**
         * Індексований масив з даними про всі маршрути та перевізників, які за ними закріплені,
         * де в якості індексу масиву виступає ID маршруту, а елементами масиву є ID перевізника закріпленого
         * за даним маршрутом
         */
        $routesCarriers = Route::model()->getAllRoutesCarriers();

        /**
         * Масив з даними про всі борти
         */
        $borts = Borts::model()->getAllBorts();

        /**
         * Індексований масив з даними про всі борти, де в якості індексів масиву виступають ID бортів,
         * а елементами масиву є ID парків
         */
        $bortsIndexed = $this->getBortsIndexed($borts);

        /**
         * Масив із даними про всі кінцеві зупинки, які необхідно ігнорувати при розрахунку звітів
         */
        $conditionsOfOutstandingEndStations = ConditionsOfOutstandingStations::model()->getAllConditionsOfOutstandingEndStations();

        // Повторюємо розрахунок для кожного дня...
        for ($datesCounter = 0; $datesCounter < $datesToCalcReport['dates_count']; $datesCounter++) {
            /**
             * Масив з даними про час початку та час кінця обрахунку звіту для кожного дня
             */
            $reportCalcTimes[$datesCounter]['start'] = time();

            /**
             * Масив зі списком ID всіх активних розкладів для вказаної дати
             */
            $activeSchedules = LocationsFlights::model()->getAllActiveSchedulesByIDFromToForDate($datesToCalcReport['locations_flights_id_from'], $datesToCalcReport['locations_flights_id_to'], $datesToCalcReport['found_days'][$datesCounter]);

            /**
             * Кількість елементів масиву "activeSchedules"
             */
            $countActiveSchedules = count($activeSchedules);

            /**
             * Запис в таблиці "executions_commands"
             */
            $executionsCommands = new TestExecutionsCommands;

            $executionsCommands->date = date("Y-m-d");
            $executionsCommands->commands_id = 5;
            $executionsCommands->start_time = $reportCalcTimes[$datesCounter]['start'];

            // Якщо не отримано списку активних розкладів - команда виконана неуспішно
            if ($countActiveSchedules == 0) {
                $reportCalcTimes[$datesCounter]['end'] = time();

                $executionsCommands->end_time = $reportCalcTimes[$datesCounter]['end'];
                $executionsCommands->duration = $reportCalcTimes[$datesCounter]['end'] - $reportCalcTimes[$datesCounter]['start'];
                $executionsCommands->success = 'N';
                $executionsCommands->comment = "no found records in table for date " . $datesToCalcReport['found_days'][$datesCounter];
                $executionsCommands->save();
            }
            // Якщо отримано список активних розкладів - продовжуємо виконання команди...
            else {
                /**
                 * Рядок зі списком ID всіх активних розкладів для даного дня, розділих знаком ","
                 */
                $activeSchedulesString = [];

                foreach ($activeSchedules as $activeScheduleID) {
                    $activeSchedulesString[] = $activeScheduleID['schedules_id'];
                }

                $activeSchedulesString = implode(",", $activeSchedulesString);

                /**
                 * Список активних графіків
                 */
                $activeGraphsString = $this->getActiveGraphsString($activeSchedules);

                // Видаляємо дані з таблиці "test_report_end_stops" для поточної дати
                TestReportEndStops::model()->deleteByGraphsIDListForDate($activeGraphsString, $datesToCalcReport['found_days'][$datesCounter]);

                /**
                 * Початкове значення 'unixtime'
                 */
                $fromUnixtime = strtotime($datesToCalcReport['found_days'][$datesCounter]) + 3600;

                /**
                 * Кінцеве значення 'unixtime'
                 */
                $toUnixtime = strtotime($datesToCalcReport['found_days'][$datesCounter]) + 23 * 3600 + 59 * 60 + 60 + 3600;

                /**
                 * Масив з даними про фактичний рух маршруток по графіку
                 * для вказаного списку активних розкладів і проміжку 'unixtime'
                 */
                $actualMovementOnSchedule = LocationsFlights::model()->getLocationsInFlightsBySchedulesIDListForDate($activeSchedulesString, $fromUnixtime, $toUnixtime);

                /**
                 * Індексований масив зі списком ID всіх активних розкладів,
                 * де в якості індексів масиву виступають ID розкладів,
                 * а елементами масиву є ID маршрутів, графіків, бортів та парків
                 */
                $activeSchedulesIndexed = $this->getActiveSchedulesIndexed($actualMovementOnSchedule, $activeSchedules, $bortsIndexed);

                /**
                 * Масив з даними про плановий рух маршруток по графіку
                 * для вказаного списку активних розкладів
                 */
                $plannedMovementOnSchedule = ScheduleTimes::model()->getAllScheduleTimesForSchedulesIDArray($activeSchedulesString);

                // Видаляємо з масиву з даними про плановий рух маршруток по графіку
                // записи, що містять зупинки зі списку зупинок для пропуску при розрахунку звіту
                $plannedMovementOnSchedule = $this->deleteIgnoredStations($plannedMovementOnSchedule, $conditionsOfOutstandingEndStations, $activeSchedulesIndexed, $datesToCalcReport['found_days'][$datesCounter]);

                // Додаємо в масив з даними про плановий рух маршруток по графіку
                // дані про напрямки руху, номери та типи зупинок
                $plannedMovementOnSchedule = $this->addStationsInfo($plannedMovementOnSchedule, $activeSchedules, $stationsScenario);

                /**
                 * Масив з даними про всі пропущенні зупинки при русі маршруток по графіку
                 */
                $allMissingStations = $this->getAllMissingStations($plannedMovementOnSchedule, $actualMovementOnSchedule);

                /**
                 * Масив з даними про кінцеві пропущені зупинки при русі маршруток по графіку
                 */
                $endMissingStations = $this->getEndMissingStations($allMissingStations);

                /**
                 * Запис в таблиці звіту "Кінцеві зупинки"
                 */
                $reportEndStopsRecord = [];

                /**
                 *  Масив з даними для запису в таблицю звіту "Кінцеві зупинки"
                 */
                $reportEndStopsArray = [];

                foreach ($endMissingStations as $key => $schedule) {
                    /**
                     * ID поточного розкладу
                     */
                    $currentScheduleID = $key;

                    /**
                     * ID поточного графіку
                     */
                    $currentGraphsID = $activeSchedulesIndexed[$key]['graphs_id'];

                    /**
                     * ID поточного маршруту
                     */
                    $currentRoutesID = $activeSchedulesIndexed[$key]['routes_id'];

                    /**
                     * ID поточного перевізника
                     */
                    $currentCarriersID = $routesCarriers[$currentRoutesID]['carriers_id'];

                    foreach ($schedule as $key => $flight) {
                        /**
                         * ID поточного рейсу
                         */
                        $currentFlightNumber = $key;

                        foreach ($flight as $key => $station) {
                            /**
                             * ID поточної зупинки
                             */
                            $currentStationID = $station['stations_id'];

                            $reportEndStopsRecord['date'] = $datesToCalcReport['found_days'][$datesCounter];
                            $reportEndStopsRecord['routes_id'] = $currentRoutesID;
                            $reportEndStopsRecord['graphs_id'] = $currentGraphsID;
                            $reportEndStopsRecord['borts_id'] = $activeSchedulesIndexed[$currentScheduleID]['borts_id'];
                            $reportEndStopsRecord['flights_number'] = $currentFlightNumber;
                            $reportEndStopsRecord['stations_id'] = $currentStationID;
                            $reportEndStopsRecord['parks_id'] = $activeSchedulesIndexed[$currentScheduleID]['parks_id'];
                            $reportEndStopsRecord['carriers_id'] = $currentCarriersID;
                            $reportEndStopsRecord['time'] = $station['time'];
                            $reportEndStopsRecord['schedules_id'] = $currentScheduleID;

                            $reportEndStopsArray[] = $reportEndStopsRecord;
                        }
                    }
                }

                /**
                 * Кількість елементів масиву з даними для запису в таблицю звіту "Кінцеві зупинки"
                 */
                $countReportEndStopsArray = count($reportEndStopsArray);

                for ($counter = 0; $counter < $countReportEndStopsArray; $counter++) {
                    /**
                     * Запис в таблиці 'report_end_stops'
                     */
                    $testReportEndStops = new TestReportEndStops;

                    $testReportEndStops->date = $reportEndStopsArray[$counter]['date'];
                    $testReportEndStops->routes_id = $reportEndStopsArray[$counter]['routes_id'];
                    $testReportEndStops->graphs_id = $reportEndStopsArray[$counter]['graphs_id'];
                    $testReportEndStops->borts_id = $reportEndStopsArray[$counter]['borts_id'];
                    $testReportEndStops->flights_number = $reportEndStopsArray[$counter]['flights_number'];
                    $testReportEndStops->stations_id = $reportEndStopsArray[$counter]['stations_id'];
                    $testReportEndStops->parks_id = $reportEndStopsArray[$counter]['parks_id'];
                    $testReportEndStops->carriers_id = $reportEndStopsArray[$counter]['carriers_id'];
                    $testReportEndStops->arrival_plan = $reportEndStopsArray[$counter]['time'];
                    $testReportEndStops->schedules_id = $reportEndStopsArray[$counter]['schedules_id'];

                    $testReportEndStops->save();
                }

                $reportCalcTimes[$datesCounter]['end'] = time();

                // Вносимо в таблицю "executions_commands" дані про успішне виконання команди
                $executionsCommands->end_time = $reportCalcTimes[$datesCounter]['end'];
                $executionsCommands->duration = $reportCalcTimes[$datesCounter]['end'] - $reportCalcTimes[$datesCounter]['start'];
                $executionsCommands->success = 'Y';
                $executionsCommands->comment = "calc report on day " . $datesToCalcReport['found_days'][$datesCounter] . " " . ($datesCounter + 1) . " from " . $datesToCalcReport['dates_count'];
                $executionsCommands->save();
            }
        }

        // Повернення значення 0, яке вказує на успішне виконання команди
        return 0;
    }

    /**
     * Повертає масив з даними про дати для яких необхідно розрахувати звіт
     *
     * @param {array} $args Аргументи командної стрічки
     * @return {array} $result Результат з даними про дати для яких необхідно розрахувати звіт
     */
    public function getDatesToCalcReport($args)
    {
        /**
         * Результат
         */
        $result = [
            "id"                        => 0,                            // ID запису
            "date"                      => 0,                        // Дата
            "locations_flights_id_from" => 0,    // ID початкового запису в таблиці "locations_in_flights"
            "locations_flights_id_to"   => 0,        // ID кінцевого запису в таблиці "locations_in_flights"
            "dates_count"               => 0,                    // Кількість дат для розрахунку
            "found_days"                => []                // Масив з датами для розрахунку звіту
        ];

        // Якщо дата не вказана в командній стрічці...
        if (!count($args)) {
            /**
             * Витягуємо масив з датами з бази даних на основі поточної дати
             */
            $datesToCalcReportFromDB = DaysToReport::model()->getDaysToReportByDate(date('Y-m-d'));
        }
        // Якщо дата вказана в командній стрічці...
        else {
            /**
             * Витягуємо масив з датами з бази даних на основі дати вказаної в командній стрічці
             */
            $datesToCalcReportFromDB = DaysToReport::model()->getDaysToReportByDate($args[0]);
        }

        if ($datesToCalcReportFromDB) {
            /**
             * Масив з датами для розрахунку звіту
             */
            $datesArray = explode(",", $datesToCalcReportFromDB['found_days']);

            $result['id'] = $datesToCalcReportFromDB['id'];
            $result['date'] = $datesToCalcReportFromDB['date'];
            $result['locations_flights_id_from'] = $datesToCalcReportFromDB['locations_flights_id_from'];
            $result['locations_flights_id_to'] = $datesToCalcReportFromDB['locations_flights_id_to'];
            $result['dates_count'] = count($datesArray);
            $result['found_days'] = $datesArray;
        }
        return $result;
    }

    /**
     * Додає в масив з даними про плановий рух маршруток по графіку
     * дані про напрямки руху, номери та типи зупинок
     *
     * @param {array} $plannedMovementOnSchedule Дані про плановий рух маршруток по графіку
     * @param {array} $activeSchedules Список ID всіх активних розкладів
     * @param {array} $stationsScenario  Список всіх сценаріїв зупинок з точками контролю
     * @return {array} $result Дані про плановий рух маршруток по графіку
     */
    public function addStationsInfo($plannedMovementOnSchedule, $activeSchedules, $stationsScenario)
    {
        /**
         * Результат
         */
        $result = $plannedMovementOnSchedule;

        foreach ($activeSchedules as $schedule) {
            /**
             * ID поточного розкладу
             */
            $currentScheduleID = $schedule['schedules_id'];

            /**
             * ID поточного маршруту
             */
            $currentRouteID = $schedule['routes_id'];

            /**
             * Сценарії зупинок для поточного маршруту
             */
            $currentStationsScenario = $this->getStationsScenarioByRouteID($currentRouteID, $stationsScenario);

            /**
             * Кількість рейсів
             */
            $flightsCount = count($result[$currentScheduleID]);

            for ($flightsIterator = 1; $flightsIterator <= $flightsCount; $flightsIterator++) {
                /**
                 * Кількість зупинок в рейсі
                 */
                $stationsCount = count($result[$currentScheduleID][$flightsIterator]);

                /**
                 * ID напрямку руху по маршруті для початкової зупинки
                 */
                $startRouteDirectionsID = $currentStationsScenario[$result[$currentScheduleID][$flightsIterator][0]['stations_id']]['route_directions_id'];

                /**
                 * Установленність напрямків
                 */
                $isDirectionsSet = false;

                for ($stationsIterator = 0; $stationsIterator < $stationsCount; $stationsIterator++) {
                    /**
                     * Порядковий номер зупинки в маршруті
                     */
                    $number = $currentStationsScenario[$result[$currentScheduleID][$flightsIterator][$stationsIterator]['stations_id']]['number'];

                    /**
                     * ID напрямку руху по маршруті
                     */
                    $routeDirectionsID = $currentStationsScenario[$result[$currentScheduleID][$flightsIterator][$stationsIterator]['stations_id']]['route_directions_id'];

                    $result[$currentScheduleID][$flightsIterator][$stationsIterator]['number'] = $number;
                    $result[$currentScheduleID][$flightsIterator][$stationsIterator]['route_directions_id'] = $routeDirectionsID;

                    // Визначаємо та встановлюємо тип зупинок
                    if ($stationsIterator == 0)
                        $result[$currentScheduleID][$flightsIterator][$stationsIterator]['stations_type'] = 'end';
                    else {
                        if ($stationsIterator == ($stationsCount - 1))
                            $result[$currentScheduleID][$flightsIterator][$stationsIterator]['stations_type'] = 'end';
                        else {
                            if ((!$isDirectionsSet) && ($routeDirectionsID != $startRouteDirectionsID)) {
                                $result[$currentScheduleID][$flightsIterator][$stationsIterator - 1]['stations_type'] = 'end';
                                $result[$currentScheduleID][$flightsIterator][$stationsIterator]['stations_type'] = 'end';
                                $isDirectionsSet = true;
                            }
                            else
                                $result[$currentScheduleID][$flightsIterator][$stationsIterator]['stations_type'] = 'intermediate';
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Повертає масив із сценаріями зупинок для вказаного маршруту
     *
     * @param {string} $routeID ID маршруту
     * @param {array} $stationsScenario  Список всіх сценаріїв зупинок з точками контролю
     * @return {array} $result Список сценаріїв зупинок для вказаного маршруту
     */
    public function getStationsScenarioByRouteID($routeID, $stationsScenario)
    {
        /**
         * Результат
         */
        $result = [];

        /**
         * Сценарій зупинок
         */
        $scenario = [
            "number"              => 0,                // Номер зупинки
            "route_directions_id" => 0    // ID напрямку руху
        ];

        foreach ($stationsScenario as $stationScenario) {
            if ($stationScenario['routes_id'] == $routeID) {
                $scenario['number'] = $stationScenario['number'];
                $scenario['route_directions_id'] = $stationScenario['route_directions_id'];

                $result[(int)($stationScenario['stations_id'])] = $scenario;
            }
        }

        return $result;
    }

    /**
     * Порівнює масиви з плановими та фактичними даними про рух маршруток по графіку
     * та повертає масив з даними про всі пропущенні зупинки при русі маршруток по графіку
     *
     * @param {array} $plannedMovementOnSchedule Дані про плановий рух маршруток по графіку
     * @param {array} $actualMovementOnSchedule Дані про фактичний рух маршруток по графіку
     * @return {array} $result Дані про всі пропущенні зупинки при русі маршруток по графіку
     */
    public function getAllMissingStations($plannedMovementOnSchedule, $actualMovementOnSchedule)
    {
        /**
         * Результат
         */
        $result = [];

        foreach ($plannedMovementOnSchedule as $key => $schedule) {
            /**
             * ID поточного розкладу
             */
            $currentScheduleID = $key;

            foreach ($schedule as $key => $flight) {
                /**
                 * ID поточного рейсу
                 */
                $currentFlightNumber = $key;

                foreach ($flight as $key => $station) {
                    /**
                     * ID поточної зупинки
                     */
                    $currentStationID = $station['stations_id'];

                    if (array_key_exists($currentScheduleID, $actualMovementOnSchedule)) {
                        if (array_key_exists($currentFlightNumber, $actualMovementOnSchedule[$currentScheduleID])) {
                            /**
                             * Вказує на те, чи знайдена зупинка (true - знайдена, false - не знайдена)
                             */
                            $finded = false;

                            foreach ($actualMovementOnSchedule[$currentScheduleID][$currentFlightNumber] as $actualStation) {
                                if ($actualStation['stations_id'] == $currentStationID) {
                                    $finded = true;
                                    break;
                                }
                            }
                            if (!$finded)
                                $result[$currentScheduleID][$currentFlightNumber][] = $station;
                        }
                        else
                            $result[$currentScheduleID][$currentFlightNumber][] = $station;
                    }
                    else
                        $result[$currentScheduleID][$currentFlightNumber][] = $station;
                }
            }
        }

        return $result;
    }

    /**
     * Повертає масив з даними про кінцеві пропущенні зупинки при русі маршруток по графіку
     *
     * @param {array} $allMissingStations Дані про всі пропущенні зупинки при русі маршруток по графіку
     * @return {array} $result Дані про кінцеві пропущенні зупинки при русі маршруток по графіку
     */
    public function getEndMissingStations($allMissingStations)
    {
        /**
         * Результат
         */
        $result = [];

        foreach ($allMissingStations as $key => $schedule) {
            /**
             * ID поточного розкладу
             */
            $currentScheduleID = $key;

            foreach ($schedule as $key => $flight) {
                /**
                 * ID поточного рейсу
                 */
                $currentFlightNumber = $key;

                foreach ($flight as $key => $station) {
                    /**
                     * ID поточної зупинки
                     */
                    $currentStationID = $station['stations_id'];

                    if ($station['stations_type'] === 'end')
                        $result[$currentScheduleID][$currentFlightNumber][] = $station;
                }
            }
        }

        return $result;
    }

    /**
     * Повертає індексований масив зі списком ID всіх активних розкладів,
     * де в якості індексів масиву виступають ID розкладів,
     * а елементами масиву є ID маршрутів, графіків, бортів та парків
     *
     * @param {array} $actualMovementOnSchedule Масив з даними про фактичний рух маршруток по графіку
     * @param {array} $activeSchedules Масив зі списком ID всіх активних розкладів
     * @param {array} $bortsIndexed Індексований масив з даними про всі борти
     * @return {array} $result Результат
     */
    public function getActiveSchedulesIndexed($actualMovementOnSchedule, $activeSchedules, $bortsIndexed)
    {
        /**
         * Результат
         */
        $result = [];

        foreach ($activeSchedules as $schedule) {
            $result[$schedule['schedules_id']]['graphs_id'] = $schedule['graphs_id'];
            $result[$schedule['schedules_id']]['routes_id'] = $schedule['routes_id'];

            /**
             * Дані для поточного розкладу
             */
            $currentSchedule = current($actualMovementOnSchedule[$schedule['schedules_id']]);

            $result[$schedule['schedules_id']]['borts_id'] = $currentSchedule[0]['borts_id'];
            $result[$schedule['schedules_id']]['parks_id'] = $bortsIndexed[$result[$schedule['schedules_id']]['borts_id']]['parks_id'];
        }

        return $result;
    }

    /**
     * Повертає індексований масив  з даними про всі борти,
     * де в якості індексівмасиву виступають ID бортів, а елементами масиву є ID парків
     *
     * @param {array} $borts Масив зі списком всіх бортів
     * @return {array} $result Результат
     */
    public function getBortsIndexed($borts)
    {
        /**
         * Результат
         */
        $result = [];

        foreach ($borts as $bort) {
            $result[$bort['id']]['parks_id'] = $bort['parks_id'];
        }

        return $result;
    }

    /**
     * Повертає рядок, що містить список ID всіх активних графіків,
     * який формується на основі переданого списку ID всіх активних розкладів
     *
     * @param {array} $activeSchedules Масив зі списком ID всіх активних розкладів
     * @return {string} $result Рядок, що містить список ID всіх активних графіків
     */
    public function getActiveGraphsString($activeSchedules)
    {
        /**
         * Рядок зі списком ID всіх активних розкладів для даного дня, розділих знаком ","
         */
        $result = [];

        foreach ($activeSchedules as $activeScheduleID) {
            $result[] = $activeScheduleID['graphs_id'];
        }

        $result = implode(",", $result);

        return $result;
    }

    /**
     * Видаляє з масиву даних про плановий рух маршруток по графіку записи які відповідають
     * умовам вказаним в $conditionsOfOutstandingEndStations.
     *
     * @param {array} $plannedMovementOnSchedule Дані про плановий рух маршруток по графіку.
     * @param {array} $conditionsOfOutstandingEndStations Дані про зупинки, які необхідно ігнорувати при розрахунку звітів.
     * @param {array} $activeSchedulesIndexed Індексований масив зі списком ID всіх активних розкладів.
     * @param {array} $date Дата, для якої розраховується звіт.
     * @return {array} $result Масив з даними про плановий рух маршруток по графіку.
     */
    public function deleteIgnoredStations($plannedMovementOnSchedule, $conditionsOfOutstandingEndStations, $activeSchedulesIndexed, $date)
    {
        /**
         * Результат
         */
        $result = [];

        foreach ($plannedMovementOnSchedule as $key => $schedule) {
            /**
             * ID поточного розкладу
             */
            $currentScheduleID = $key;

            foreach ($schedule as $key => $flight) {
                /**
                 * ID поточного рейсу
                 */
                $currentFlightNumber = $key;

                foreach ($flight as $key => $station) {
                    /**
                     * ID поточної зупинки
                     */
                    $currentStationID = $station['stations_id'];

                    /**
                     * Ігнорування зупинки (true - ігнорувати, false - не ігнорувати)
                     */
                    $ignore = false;

                    foreach ($conditionsOfOutstandingEndStations as $key => $condition) {
                        // Перевірка ID зупинки
                        if ($currentStationID == $condition['stations_id']) {
                            // Перевірка дати
                            if ($condition['day_from'] != null) {
                                if ($date >= $condition['day_from']) {
                                    if ($condition['day_to'] != null) {
                                        if ($date <= $condition['day_to'])
                                            $ignore = true;
                                        else
                                            $ignore = false;
                                    }
                                    else
                                        $ignore = true;
                                }
                                else
                                    $ignore = false;
                            }
                            else {
                                if ($condition['day_to'] != null) {
                                    if ($date <= $condition['day_to'])
                                        $ignore = true;
                                    else
                                        $ignore = false;
                                }
                                else
                                    $ignore = true;
                            }

                            // Перевірка днів тижня
                            if ($ignore) {
                                /**
                                 * Масив з даними про дні тижня, які ігноруються
                                 */
                                $daysArray = explode(",", $condition['weekdays_list']);

                                if ($daysArray[0] != "") {
                                    foreach ($daysArray as $day) {
                                        /**
                                         * Мітка часу UNIX
                                         */
                                        $unixDate = getdate(strtotime($date));

                                        if ($unixDate['weekday'] == $day) {
                                            $ignore = true;
                                            break;
                                        }
                                        else
                                            $ignore = false;
                                    }
                                }
                                else
                                    $ignore = true;
                            }

                            // Перевірка періоду доби
                            if ($ignore) {
                                if ($condition['time_from'] != null) {
                                    if ($station['time'] >= $condition['time_from']) {
                                        if ($condition['time_to'] != null) {
                                            if ($station['time'] <= $condition['time_to'])
                                                $ignore = true;
                                            else
                                                $ignore = false;
                                        }
                                        else
                                            $ignore = true;
                                    }
                                    else
                                        $ignore = false;
                                }
                                else {
                                    if ($condition['time_to'] != null) {
                                        if ($station['time'] <= $condition['time_to'])
                                            $ignore = true;
                                        else
                                            $ignore = false;
                                    }
                                    else
                                        $ignore = true;
                                }
                            }

                            // Перевірка списку маршрутів
                            if ($ignore) {
                                /**
                                 * Масив з даними про ID маршрутів, які ігноруються
                                 */
                                $routesArray = explode(",", $condition['routes_id_list']);

                                if ($routesArray[0] != "") {
                                    foreach ($routesArray as $route) {
                                        if ($activeSchedulesIndexed[$currentScheduleID]['routes_id'] == $route) {
                                            $ignore = true;
                                            break;
                                        }
                                        else
                                            $ignore = false;
                                    }
                                }
                                else
                                    $ignore = true;
                            }
                        }
                    }

                    if (!$ignore)
                        $result[$currentScheduleID][$currentFlightNumber][] = $station;
                }
            }
        }

        return $result;
    }
}

?>