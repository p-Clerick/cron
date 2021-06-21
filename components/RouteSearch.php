<?php

class RouteSearch {
	/**
	 * Пріоритети пошуку маршруту
	 */
	private static $priorities = array(
		'FASTER' => '0',	// Швидший
		'CHEAPER' => '1' 	// Дешевший
	);

	/**
	 * Порівняння двох маршрутів за тривалістю руху по маршруті
	 *
	 * @param {array} $a Перший маршрут для порівняння.
     * @param {array} $b Другий маршрут для порівняння.
		 * @return {int} $result Результат порівняння (0 - маршрути рівні по тривалості, 
		 *                        1 - перший маршрут має більшу тривалість за другий, 
		 *                        -1 - перший маршрут має меншу тривалість за другий).
	 */
	public function cmpRoutesByDuration($a, $b) {
	    if ($a['duration']['value'] == $b['duration']['value'])
	        return 0;

	    return ($a['duration']['value'] < $b['duration']['value']) ? -1 : 1;
	}

	/**
	 * Порівняння двох маршрутів за вартістю проїзду.
	 * Маршрути з однаковою вартістю проїзду порівнюються за тривалістю руху по маршруті
	 *
	 * @param {array} $a Перший маршрут для порівняння.
     * @param {array} $b Другий маршрут для порівняння.
		 * @return {int} $result Результат порівняння (0 - маршрути рівні по вартості, 
		 *                        1 - перший маршрут має більшу вартість проїзду за другий, 
		 *                        -1 - перший маршрут має меншу вартість проїзду за другий).
	 */
	public function cmpRoutesByPrice($a, $b) {
	    if ($a['price'] == $b['price']) {
	        return (RouteSearch::cmpRoutesByDuration($a, $b));
	    }

	    return ($a['price'] < $b['price']) ? -1 : 1;
	}

	/**
     * Сортування маршрутів за вказаним пріоритетом
     *
     * @param {array} $routes Список маршрутів.
     * @param {array} $priority Пріоритет.
		 * @return {array} $result Масив відсортованих, за вказаним пріоритетом, маршрутів.
     */
    public function sortRoutes($routes, $priority) {
    	/**
         * Результат
         */
        $result = $routes;

        if ($priority === RouteSearch::$priorities['FASTER'])
        	usort($result, "RouteSearch::cmpRoutesByDuration");
        else
        	usort($result, "RouteSearch::cmpRoutesByPrice");

		return $result;
    }

	/**
     * Перетворює отримане значення відстані в метрах у рядок із значенням відстані в кілометрах,
     * у форматі "х,х"
     *
     * @param {float} $value Відстань в метрах.
		 * @return {string} $result Рядок із значенням відстані в кілометрах у форматі "х,х".
     */
    public function convertDistanceToText($value) {
		return (str_replace(".", ",", (round($value / 1000, 1))));
    }

    /**
     * Перетворює отримане значення вартості проїзду у рядок
     *
     * @param {float} $value Вартість проїзду.
		 * @return {string} $result Рядок із значенням відстані в кілометрах у форматі "х,х".
     */
    public function convertPriceToText($value) {
		return (str_replace(".", ",", $value));
    }

	/**
     * Визначення відстані руху пішки між двома точками за їхніми координатами
     *
     * @param {float} $startPointLat Широта початкової точки.
     * @param {float} $startPointLng Довгота початкової точки.
		 * @param {float} $endPointLat Широта кінцевої точки.
     * @param {float} $endPointLng Довгота кінцевої точки.
		 * @return {array} $result Масив з даними про відстань руху пішки між двома точками.
     */
    public function getWalkDistanceBetweenTwoPoints($startPointLat, $startPointLng, $endPointLat, $endPointLng) {
    	/**
         * Результат
         */
        $result = array(
            "text" => 0,	// Текстове значення відстані в кілометрах
            "value" => 0	// Відстань в метрах
        );

        $result['value'] = round(RouteSearch::computeDistanceBetweenHaversine($startPointLat, $startPointLng, $endPointLat, $endPointLng));
        $result['text'] = RouteSearch::convertDistanceToText($result['value']);

		return $result;
    }

    /**
     * Визначення відстані руху пішки по маршруті 
     *
     * @param {float} $walkToStationDistance Відстань руху пішки від точки А до початкової зупинки в метрах.
     * @param {float} $walkFromStationDistance Відстань руху пішки від кінцевої зупинки до точки B в метрах.
		 * @return {array} $result Масив з даними про відстань руху пішки по маршруті.
     */
    public function getWalkDistance($walkToStationDistance, $walkFromStationDistance) {
    	/**
         * Результат
         */
        $result = array(
            "text" => 0,	// Текстове значення відстані в кілометрах
            "value" => 0	// Відстань в метрах
        );
        
        $result['value'] = $walkToStationDistance + $walkFromStationDistance;
        $result['text'] = RouteSearch::convertDistanceToText($result['value']);

		return $result;
    }

    /**
     * Визначення відстані руху транспортом між двома зупинками 
     *
     * @param {string} $startStationID ID початкової зупинки.
     * @param {string} $endStationID ID кінцевої зупинки.
     * @param {array} $stationsDistances Список всіх відстаней між зупинками.
		 * @return {array} $result Мисив з даними про відстань руху транспортом між двома зупинками.
     */
    public function getTransoprtDistanceBetweenTwoStations($startStationID, $endStationID, $stationsDistances) {
        /**
		 * Результат
		 */
		$result = array(
			"id"  => 0,					// ID запису в базі даних
            "stations_id_from" => 0,	// ID першої зупинки
            "stations_id_to" => 0,		// ID другої зупинки
            "distance_in_meters" => 0	// Відстань в метрах
		);

        foreach ($stationsDistances as $stationDistance) {
        	if (($startStationID == $stationDistance['stations_id_from']) && ($endStationID == $stationDistance['stations_id_to'])) {
        		$result['id'] = $stationDistance['id'];
        		$result['stations_id_from'] = $stationDistance['stations_id_from'];
        		$result['stations_id_to'] = $stationDistance['stations_id_to'];
        		$result['distance_in_meters'] = $stationDistance['distance_in_meters'];
        	}
        }

        return $result;
    }

    /**
     * Визначення відстані руху по маршруті між двома зупинками
     *
     * @param {string} $startStationID ID початкової зупинки маршруту.
     * @param {string} $endStationID ID кінцевої зупинки маршруту.
     * @param {array} $stationsDistances Масив відстаней між зупинками.
		 * @return {array} $result Масив з даними про відстань руху транспортом по маршруті між двома зупинками.
     */
    public function getTransportDistance($startStationID, $endStationID, $stationsDistances) {
    	/**
         * Результат
         */
        $result = array(
            "text" => 0,	// Текстове значення відстані в кілометрах
            "value" => 0	// Відстань в метрах
        );

        /**
         * Відстань руху між початковою та кінцевою зупинками
         */
        $distanceBetweenStations = 0;

        /**
		 * Булеве значення, вказує чи потрібно продовжувати розрахунок
		 */ 
    	$isCalculation = false;

        foreach ($stationsDistances as $distance) {
        	if(($distance['stations_id_from'] === $startStationID) || $isCalculation) {
        		$distanceBetweenStations += $distance['distance_in_meters'];
        		if ($distance['stations_id_to'] === $endStationID)
        			$isCalculation = false;
        		else
        			$isCalculation = true;
        	}
        }
        
        $result['value'] = $distanceBetweenStations;
        $result['text'] = RouteSearch::convertDistanceToText($result['value']);

		return $result;
    }

    /**
     * Визначення відстані руху по маршруті
     *
     * @param {float} $walkToStationDistance Відстань від точки А до початкової зупинки.
     * @param {float} $walkFromStationDistance Відстань від кінцевої зупинки до точки B.
     * @param {float} $transportDistance Відстань руху транспортом по маршруті.
		 * @return {array} $result Масив з даними про відстань руху пішки між двома точками.
     */
    public function getRouteDistance($walkToStationDistance, $walkFromStationDistance, $transportDistance) {
    	/**
         * Результат
         */
        $result = array(
            "text" => 0,	// Текстове значення відстані в кілометрах
            "value" => 0	// Відстань в метрах
        );

        $result['value'] = $walkToStationDistance + $transportDistance + $walkFromStationDistance;
        $result['text'] = RouteSearch::convertDistanceToText($result['value']);

		return $result;
    }

    /**
     * Визначення тривалості руху пішки між двома точками на основі відстані між ними
     *
     * @param {float} $distance Відстань між точками в метрах.
		 * @return {array} $result Масив з даними про тривалість руху пішки між двома точками.
     */
    public function getWalkDurationBetweenTwoPoints($distance) {
    	/**
         * Результат
         */
        $result = array(
            "text" => 0,	// Текстове значення тривалості в хвилинах
            "value" => 0	// Тривалість в секундах
        );

        /**
    	 * Швидкість руху людини пішки, м/с
    	 */
    	$velocity = 1.111; // Приблизно 4 км/год.

        $result['value'] = round($velocity * $distance);
        $result['text'] = (string)(round($result['value'] / 60));

		return $result;
    }

    /**
     * Визначення тривалості руху пішки по маршруті 
     *
     * @param {float} $walkToStationDuration Тривалість руху пішки від точки А до початкової зупинки в секундах.
     * @param {float} $walkFromStationDuration Тривалість руху пішки від кінцевої зупинки до точки B в секундах.
		 * @return {array} $result Масив з даними про тривалість руху пішки по маршруті.
     */
    public function getWalkDuration($walkToStationDuration, $walkFromStationDuration) {
    	/**
         * Результат
         */
        $result = array(
            "text" => 0,	// Текстове значення тривалості в хвилинах
            "value" => 0	// Тривалість в секундах
        );
        
        $result['value'] = $walkToStationDuration + $walkFromStationDuration;
        $result['text'] = (string)(round($result['value'] / 60));

		return $result;
    }

    /**
     * Визначення тривалості руху транспортом по маршруті 
     *
     * @param {string} $startStationID ID початкової зупинки маршруту.
     * @param {string} $endStationID ID кінцевої зупинки маршруту.
     * @param {array} $dayIntervalStations Масив інтервалів руху між зупиками.
		 * @return {array} $result Масив з даними про тривалість руху транспортом по маршруті.
     */
    public function getTransportDuration($startStationID, $endStationID, $dayIntervalStations) {
    	/**
         * Результат
         */
        $result = array(
            "text" => 0,	// Текстове значення тривалості в хвилинах
            "value" => 0	// Тривалість в секундах
        );

        /**
         * Тривалість руху між початковою та кінцевою зупинками
         */
        $durationBetweenStations = 0;

        /**
		 * Булеве значення, вказує чи потрібно продовжувати розрахунок
		 */ 
    	$isCalculation = false;

        foreach ($dayIntervalStations as $interval) {
        	if(($interval['stations_id_from'] === $startStationID) || $isCalculation) {
        		$durationBetweenStations += $interval['interval'];
        		if ($interval['stations_id_to'] === $endStationID)
        			$isCalculation = false;
        		else
        			$isCalculation = true;
        	}
        }
        
        $result['value'] = $durationBetweenStations;
        $result['text'] = (string)(round($result['value'] / 60));

		return $result;
    }

    /**
     * Визначення тривалості руху по маршруті
     *
     * @param {float} $walkToStationDuration Тривалість руху пішки від точки А до початкової зупинки в секундах.
     * @param {float} $walkFromStationDuration Тривалість руху пішки від кінцевої зупинки до точки B в секундах.
     * @param {float} $transportDuration Тривалість руху транспортом по маршруті.
		 * @return {array} $result Масив з даними про тривалість руху по маршруті.
     */
    public function getRouteDuration($walkToStationDuration, $walkFromStationDuration, $transportDuration) {
    	/**
         * Результат
         */
        $result = array(
            "text" => 0,	// Текстове значення тривалості в хвилинах
            "value" => 0	// Тривалість в секундах
        );
        
        $result['value'] = $walkToStationDuration + $transportDuration + $walkFromStationDuration;
        $result['text'] = (string)(round($result['value'] / 60));

		return $result;
    }


    /**
     * Конвертація координат з представлення Google у внутрішнє представлення.
     *
     * @param {array} $latitude Широта у представленні Google.
     * @param {array} $longitude Довгота у представленні Google.
		 * @return {array} $result Масив з координатами у внутрішньому представленні.
     */
    public function convertCoordsFromGoogleToUkr($latitude, $longitude) {
    	/**
         * Результат
         */
        $result = array(
            "latitude" => 0,	// Широта
            "longitude" => 0	// Довгота
        );

    	/**
    	 * Широта у представленні Google.
    	 */
    	$result['latitude'] = floor((floor($latitude) + ($latitude - floor($latitude)) / 100 * 60) * 1000000) / 10000;

    	/**
    	 * Довгота у представленні Google.
    	 */
    	$result['longitude'] = floor((floor($longitude) + ($longitude - floor($longitude)) / 100 * 60) *1000000) / 10000;

		return $result;
    }

    /**
     * Конвертація координат з внутрішнього представлення в представлення Google
     *
     * @param {array} $latitude Широта у внутрішньому представленні.
     * @param {array} $longitude Довгота у внутрішньому представленні.
		 * @return {array} $result Масив з координатами у представленні Google.
     */
    public function convertCoordsFromUkrToGoogle($latitude, $longitude) {
    	/**
         * Результат
         */
        $result = array(
            "latitude" => 0,	// Широта
            "longitude" => 0	// Довгота
        );

    	/**
    	 * Широта у представленні Google.
    	 */
    	$result['latitude'] = (floor($latitude / 100) * 100 + (($latitude - floor($latitude / 100) * 100) * 100 / 60)) / 100;

    	/**
    	 * Довгота у представленні Google.
    	 */
    	$result['longitude'] = (floor($longitude / 100) * 100 + (($longitude - floor($longitude / 100) * 100) * 100 / 60)) / 100;

		return $result;
    }

   	/**
     * Визначення відстані між двома точками за їхніми координатами в метрах на основі формули гаверсинусів
     *
     * @param {array} $startPointLat Широта початкової точки.
     * @param {array} $startPointLng Довгота початкової точки.
		 * @param {array} $endPointLat Широта кінцевої точки.
     * @param {array} $endPointLng Довгота кінцевої точки.
		 * @return {array} $result Відстань між двома точками в метрах.
     */
    public function computeDistanceBetweenHaversine($startPointLat, $startPointLng, $endPointLat, $endPointLng) {
    	/**
    	 * Координати початкової точки у представленні Google
    	 */
    	$startPointGoogleLatLng = RouteSearch::convertCoordsFromUkrToGoogle($startPointLat, $startPointLng);

    	/**
    	 * Координати кінцевої точки у представленні Google
    	 */
    	$endPointGoogleLatLng = RouteSearch::convertCoordsFromUkrToGoogle($endPointLat, $endPointLng);

    	/**
    	 * Радіус Землі у метрах
    	 */
    	$R = 6378137;

    	$dLat = deg2rad($endPointGoogleLatLng['latitude'] - $startPointGoogleLatLng['latitude']);
		$dLng = deg2rad($endPointGoogleLatLng['longitude'] - $startPointGoogleLatLng['longitude']);

		$a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($startPointGoogleLatLng['latitude'])) * cos(deg2rad($endPointGoogleLatLng['latitude'])) * sin($dLng / 2) * sin($dLng / 2);

		$c = 2 * atan2(sqrt($a), sqrt(1 - $a));

		return ($R * $c);
    }

	/**
     * Перевірка коректності маршруту
     *
		 * @param {array} $startStation Початкова зупинка.
		 * @param {array} $endStation Кінцева зупинка.
     * @param {array} $startPoint Початкова точка.
		 * @param {array} $endPoint Кінцева точка. 		 
		 * @return {array} $result Булевий результат (TRUE - маршрут коректний, FALSE - маршрут не коректний).
     */
    public function checkRoute($startStation, $endStation, $startPoint, $endPoint) { 
    	// Якщо номер початкової зупинки менше номеру кінцевої зупинки
    	if ($startStation['number'] < $endStation['number']) {
    		/**
    		 * Відстань між точками А та B в метрах
    		 */
    		$distanceBetweenAB = RouteSearch::computeDistanceBetweenHaversine($startPoint['latitude'], $startPoint['longitude'], $endPoint['latitude'], $endPoint['longitude']);

    		/**
    		 * Відстань між точкою А та початковою зупинкою в метрах
    		 */
    		$distanceBetweenAStartStation = RouteSearch::computeDistanceBetweenHaversine($startPoint['latitude'], $startPoint['longitude'], $startStation['latitude'], $startStation['longitude']);

    		/**
    		 * Відстань між точкою B та кінцевою зупинкою в метрах
    		 */
    		$distanceBetweenBEndStation = RouteSearch::computeDistanceBetweenHaversine($endPoint['latitude'], $endPoint['longitude'], $endStation['latitude'], $endStation['longitude']);
    		
    		if ($distanceBetweenAB > (($distanceBetweenAStartStation + $distanceBetweenBEndStation) * 2))
    			return true;
    		else
    			return false;
    	}
    	else
    		return false;
    }

	/**
     * Пошук наступної найближчої протилежної зупинки від вказаної точки
     *
		 * @param {array} $point Точка.
		 * @param {array} $currentRouteDirectionsID ID напрямку руху поточної зупинки.
		 * @param {array} $stations Список зупинок.
		 * @return {array} $result Знайдена найближча протилежна зупинка від вказаної точки.
     */
    public function findNextNearestStation($point, $currentRouteDirectionsID, $stations) {
        /**
         * Максимальна відстань до найближчої зупинки
         */ 
        if (!defined('MAX_DISTANCE')) 
            define('MAX_DISTANCE', 100);

        /**
         * Мінімальна відстань
         */
        $min = PHP_INT_MAX;

        /**
         * Результат
         */
        $result = array(
        	"id"  => 0,					// ID зупинки
            "latitude" => 0,			// Широта
            "longitude" => 0,			// Довгота
            "name" => "",				// Назва
            "number" => 0,				// Номер
            "route_directions_id" => 0	// ID напрямку руху
        );

        foreach ($stations as $station) {
            $distance = pow($point['latitude'] - $station['latitude'], 2) + pow($point['longitude'] - $station['longitude'], 2);
            
            if (($distance < $min) && ($distance < MAX_DISTANCE) && ($station['route_directions_id'] != $currentRouteDirectionsID)) {
            	$min = $distance;
	            $result['id'] = $station['id'];
	            $result['latitude'] = $station['latitude'];
	            $result['longitude'] = $station['longitude'];
	            $result['name'] = $station['name'];
	            $result['number'] = $station['number'];
	            $result['route_directions_id'] = $station['route_directions_id'];
            }
        }

        return $result;
    }

	/**
     * Пошук найближчої зупинки від вказаної точки
     *
		 * @param {array} $point Точка.
		 * @param {array} $stations Список зупинок.
		 * @return {array} $result Знайдена найближча зупинка від вказаної точки.
     */ 
    public function findNearestStation($point, $stations) {
        /**
         * Максимальна відстань до найближчої зупинки
         */ 
        if (!defined('MAX_DISTANCE')) 
            define('MAX_DISTANCE', 100);

        /**
         * Мінімальна відстань
         */
        $min = PHP_INT_MAX;

        /**
         * Результат
         */
        $result = array(
        	"id"  => 0,					// ID зупинки
            "latitude" => 0,			// Широта
            "longitude" => 0,			// Довгота
            "name" => "",				// Назва
            "number" => 0,				// Номер
            "route_directions_id" => 0	// ID напрямку руху
        );

        foreach ($stations as $station) {
            $distance = pow($point['latitude'] - $station['latitude'], 2) + pow($point['longitude'] - $station['longitude'], 2);
            
            if (($distance < $min) && ($distance < MAX_DISTANCE)) {
            	$min = $distance;
	            $result['id'] = $station['id'];
	            $result['latitude'] = $station['latitude'];
	            $result['longitude'] = $station['longitude'];
	            $result['name'] = $station['name'];
	            $result['number'] = $station['number'];
	            $result['route_directions_id'] = $station['route_directions_id'];
            }
        }

        return $result;
    }

    /**
     * Повернення інформації про вказаний маршрут
     *
     * @param {array} $route Маршрут.
     * @param {array} $stations Список всіх зупинок міста.
     * @param {array} $stationsScenario Список всіх сценаріїв зупинок міста.
     * @param {array} $stationsDistances Список відстаней між всіхма зупинками міста.
     * @param {array} $dayIntervalRoute Список відповідностей "ID маршруту - ID інтервалу руху по маршруті".
     * @param {array} $dayIntervalStations Список інтервалів руху між зупиками.
		 * @return {array} $result Список зупинок для вказаного маршруту.
     */
    public function getRouteInfo($route, $stations, $stationsScenario, $stationsDistances, $dayIntervalRoute, $dayIntervalStations) {
    	/**
         * Результат
         */
        $result = array(
        	"id"  => 0,								// ID маршруту
            "name" => "",							// Назва маршруту
            "price" => 0,							// Вартість проїзду
            "start_station_sending_number" => 0,	// Номер початкової зупинки відправлення
            "stop_station_coming_number" => 0,		// Номер кінцевої зупинки прибуття
            "stop_station_sending_number" => 0,		// Номер кінцевої зупинки відпавлення
            "start_station_coming_number" => 0,		// Номер початкової зупинки прибуття.
            "start_route_directions_id" => 0,		// ID напрямку для початкової зупинки
            "end_route_directions_id" => 0,			// ID напрямку для кінцевої зупинки
            "stations_distances" => array(),		// Список відстаней між зупинками
            "day_interval_stations_id" => 0,		// ID інтервалу руху по маршруті
            "day_interval_stations" => array(),		// Список інтервалів руху між зупинками
            "stations" => array()					// Список зупинок через які проходить маршрут
        );

        /**
         * Зупинка
         */
        $station = array(
        	"id"  => 0,					// ID зупинки
            "latitude" => 0,			// Широта
            "longitude" => 0,			// Довгота
            "name" => "",				// Назва
            "number" => 0,				// Номер
            "route_directions_id" => 0	// ID напрямку руху
        );

        /**
         * Масив зупинок через які проходить маршрут
         */
        $stationsOfRoute = array();

        /**
         * Сценарій зупинок
         */
        $scenario = array(
        	"id" => 0,					// ID сценарію зупинки
        	"stations_id" => 0,			// ID зупинки
        	"number" => 0,				// Номер зупинки
            "route_directions_id" => 0,	// Номер напрямку руху
        );

        /**
         * Сценарії зупинок для вибраного маршруту
         */
        $stationsScenarioForRoute = array();

        $result['id'] = $route['id'];
		$result['name'] = $route['name'];
		$result['price'] = $route['cost'];

        foreach ($stationsScenario as $stationScenario) {
    		if ($stationScenario['routes_id'] == $route['id']) {
				$scenario['id'] = $stationScenario['id'];
				$scenario['stations_id'] = $stationScenario['stations_id'];
				$scenario['number'] = $stationScenario['number'];
				$scenario['route_directions_id'] = $stationScenario['route_directions_id'];

				$stationsScenarioForRoute[] = $scenario;
    		}
    	}

    	// Сортуємо масив
    	asort($stationsScenarioForRoute);

    	$result['start_station_sending_number'] = $stationsScenarioForRoute[0]['number'];
    	$result['start_station_coming_number'] = $stationsScenarioForRoute[count($stationsScenarioForRoute) - 1]['number'];

    	/**
    	 * ID напрямку початкової зупинки
    	 */
    	$directionID = $stationsScenarioForRoute[0]['route_directions_id'];

    	/**
		 * Установленність напрямків
		 */ 
    	$isDirectionsSet = false;

    	$result['start_route_directions_id'] = $directionID;

    	foreach ($stationsScenarioForRoute as $stationScenario) {
    		foreach ($stations as $currentStation) {
	    		if ($currentStation['id'] == $stationScenario['stations_id']) {
    				$station['id'] = $currentStation['id'];
    				$station['latitude'] = $currentStation['latitude'];
    				$station['longitude'] = $currentStation['longitude'];
    				$station['name'] = $currentStation['name'];
    				$station['number'] = $stationScenario['number'];
    				$station['route_directions_id'] = $stationScenario['route_directions_id'];
	    		}
	    	}

    		$stationsOfRoute[] = $station;

    		if ((!$isDirectionsSet) && ($stationScenario['route_directions_id'] != $directionID)) {
    			$result['stop_station_coming_number'] = $stationScenario['number'] - 1;
    			$result['stop_station_sending_number'] = $stationScenario['number'];
    			$result['end_route_directions_id'] = $stationScenario['route_directions_id'];

    			$isDirectionsSet = true;
    		}
    	}

    	$result['stations'] = $stationsOfRoute;

    	/**
    	 * Ітератор циклу
     	 */
    	$iterator = 0;

    	foreach ($result['stations'] as $station) {
    		if (($iterator + 1) < count($result['stations']))
        		$result['stations_distances'][] = RouteSearch::getTransoprtDistanceBetweenTwoStations($station['id'], $result['stations'][++$iterator]['id'], $stationsDistances);
    	}

    	foreach ($dayIntervalRoute as $interval) {
    		if ($interval['routes_id'] === $route['id']) {
    			$result['day_interval_stations_id'] = $interval['day_interval_city_id'];
    			break;
    		}
    	}

    	foreach ($dayIntervalStations as $interval) {
    		if ($interval['day_interval_city_id'] === $result['day_interval_stations_id'])
    			$result['day_interval_stations'][] = $interval;
    	}

    	return $result;
    }

    /**
     * Пошук та складання списку маршрутів
     *
     * @param {array} $startPoint Початкова точка.
		 * @param {array} $endPoint Кінцева точка.
		 * @param {array} $stations Список всіх зупинок міста.
		 * @param {array} $routes Список всіх маршрутів міста.
		 * @param {array} $stationsScenario Список всіх сценаріїв зупинок міста.
		 * @param {array} $stationsDistances Список всіх відстаней між зупинками.
		 * @param {array} $dayIntervalRoute Список відповідностей "ID маршруту - ID інтервалу руху по маршруті".
		 * @param {array} $dayIntervalStations Список інтервалів руху між зупиками.
		 * @param {string} $priority Пріоритет пошуку маршруту.
		 * @return {array} $result Список знайдених маршрутів.
     */
    public function findRoutes($startPoint, $endPoint, $stations, $routes, $stationsScenario, $stationsDistances, $dayIntervalRoute, $dayIntervalStations, $priority) {
    	/**
         * Результат
         */
        $result = array(
            'status'  => '',           // Статус пошуку маршруту
            'routes' => array()        // Список знайдених маршрутів
        );

        /**
         * Знайдений маршрут
         */
        $findedRoute = array(
            "id"  => 0,						// ID маршруту
            "name"  => 0,					// Назва маршруту
            "price" => 0,					// Вартість проїзду
            "startStation" => 0,			// Зупинка відправлення на маршруті
            "endStation" => 0,				// Зупинка прибуття на маршруті
            "walkToStation" => array(		// Відстань і тривалість руху від точки А до початкової зупинки
            	"distance" => array(),		// Відстань руху від точки А до початкової зупинки
            	"duration" => array()		// Тривалість руху від точки А до початкової зупинки
            ),
            "walkFromStation" => array(		// Відстань і тривалість руху від кінцевої зупинки до точки B
            	"distance" => array(),		// Відстань руху від кінцевої зупинки до точки B
            	"duration" => array()		// Тривалість руху від кінцевої зупинки до точки B
            ),
            "distance" => array(),			// Відстань руху по маршруті
            "duration" => array(),			// Повна тривалість руху по маршруті
            "transportDistance" => array(),	// Відстань руху трансортом
            "walkDistance" => array(),		// Відстань руху пішки
            "transportDuration" => array(),	// Тривалість руху трансортом
            "walkDuration" => array()		// Тривалість руху пішки
        );

        /**
         * Початкова зупинка маршруту
         */
        $startStation = array();

        /**
         * Кінцева зупинка маршруту
         */
        $endStation = array();

        /**
         * Інформація про маршрут
         */
        $routeInfo = array();

        // Для кожного маршруту з масиву маршрутів
    	foreach ($routes as $route) {
    		/**
    		 * Отримуємо інформацію про поточний маршрут
    		 */
    		$routeInfo = RouteSearch::getRouteInfo($route, $stations, $stationsScenario, $stationsDistances, $dayIntervalRoute, $dayIntervalStations);

    		/**
	         * Найближча зупинка від початкової точки
	         */ 
	        $nearestStationFromStartPoint = RouteSearch::findNearestStation($startPoint, $routeInfo['stations']);

	        /**
	         * Найближча зупинка від кінцевої точки
	         */ 
	        $nearestStationFromEndPoint = RouteSearch::findNearestStation($endPoint, $routeInfo['stations']);

    		foreach ($routeInfo['stations'] as $station) {
    			if ($station['id'] === $nearestStationFromStartPoint['id'])
    				$startStation = $station;
    			if ($station['id'] === $nearestStationFromEndPoint['id'])
    				$endStation = $station;
    		}

    		// Якщо знайдено зупинки
	        if ($nearestStationFromStartPoint['id'] != 0 && $nearestStationFromEndPoint['id'] != 0) {
            	// Якщо зупинки лежать в одному напрямку руху по знайденому маршруті
            	if ($startStation['route_directions_id'] === $endStation['route_directions_id']) {
            		// Якщо номер початкової зупинки більший номеру кінцевої зупинки
            		if ($startStation['number'] > $endStation['number']) {
			    		/**
				         * Протилежна найближча зупинка від початкової точки
				         */ 
				        $nearestStationFromStartPoint = RouteSearch::findNextNearestStation($startPoint, $startStation['route_directions_id'], $routeInfo['stations']);

				        /**
				         * Протилежна найближча зупинка від кінцевої точки
				         */ 
				        $nearestStationFromEndPoint = RouteSearch::findNextNearestStation($endPoint, $endStation['route_directions_id'], $routeInfo['stations']);

				        $startStation = $nearestStationFromStartPoint;
				        $endStation = $nearestStationFromEndPoint;
            		}
            	}
	            // Якщо зупинки лежать в різних напрямках руху по знайденому маршруті
            	else {
            		/**
            		 * Початкове значення  стартової зупинки 
            		 */
            		$startStationOriginal = $startStation;

        			/**
			         * Протилежна найближча зупинка від початкової точки
			         */ 
			        $nearestStationFromStartPoint = RouteSearch::findNextNearestStation($startPoint, $startStation['route_directions_id'], $routeInfo['stations']);

			        $startStation = $nearestStationFromStartPoint;

			        // Якщо номер початкової зупинки менший номеру кінцевої зупинки
            		if ($startStation['number'] > $endStation['number']) {
            			$startStation = $startStationOriginal;

            			/**
				         * Протилежна найближча зупинка від кінцевої точки
				         */ 
				        $nearestStationFromEndPoint = RouteSearch::findNextNearestStation($endPoint, $endStation['route_directions_id'], $routeInfo['stations']);

				        $endStation = $nearestStationFromEndPoint;
            		}
            	}

            	// Якщо маршрут коректний, додаємо його у список знайдених маршрутів
            	if (RouteSearch::checkRoute($startStation, $endStation, $startPoint, $endPoint)) {
        			// Маршрут знайдено
        			$result['status'] = 'OK';

			        $findedRoute['id'] = $route['id'];
			        $findedRoute['name'] = $route['name'];
			        $findedRoute['price'] = RouteSearch::convertPriceToText($route['cost']);
			        $findedRoute['startStation'] = $startStation;
			        $findedRoute['endStation'] = $endStation;
			        $findedRoute['walkToStation']['distance'] = RouteSearch::getWalkDistanceBetweenTwoPoints($startPoint['latitude'], $startPoint['longitude'], $startStation['latitude'], $startStation['longitude']);
			        $findedRoute['walkToStation']['duration'] = RouteSearch::getWalkDurationBetweenTwoPoints($findedRoute['walkToStation']['distance']['value']);
			        $findedRoute['walkFromStation']['distance'] = RouteSearch::getWalkDistanceBetweenTwoPoints($endStation['latitude'], $endStation['longitude'], $endPoint['latitude'], $endPoint['longitude']);
			        $findedRoute['walkFromStation']['duration'] = RouteSearch::getWalkDurationBetweenTwoPoints($findedRoute['walkFromStation']['distance']['value']);
			        $findedRoute['transportDistance'] = RouteSearch::getTransportDistance($startStation['id'], $endStation['id'], $routeInfo['stations_distances']);
			        $findedRoute['walkDistance'] = RouteSearch::getWalkDistance($findedRoute['walkToStation']['distance']['value'], $findedRoute['walkFromStation']['distance']['value']);
			        $findedRoute['distance'] = RouteSearch::getRouteDistance($findedRoute['walkToStation']['distance']['value'], $findedRoute['walkFromStation']['distance']['value'], $findedRoute['transportDistance']['value']);
			        $findedRoute['transportDuration'] = RouteSearch::getTransportDuration($startStation['id'], $endStation['id'], $routeInfo['day_interval_stations']);
			        $findedRoute['walkDuration'] = RouteSearch::getWalkDuration($findedRoute['walkToStation']['duration']['value'], $findedRoute['walkFromStation']['duration']['value']);
			        $findedRoute['duration'] = RouteSearch::getRouteDuration($findedRoute['walkToStation']['duration']['value'], $findedRoute['walkFromStation']['duration']['value'], $findedRoute['transportDuration']['value']);
            		
            		$result['routes'][] = $findedRoute;
            	}
	        }
    	}

    	// Сортуємо знайдені маршрути згідно заданого пріоритету
    	$result['routes'] = RouteSearch::sortRoutes($result['routes'], $priority);

    	if (count($result['routes']) < 1) {
	    	// Маршрут не знайдено
	        $result['status'] = 'NOT_FOUND';
	    }

    	return $result;
    }
}

?>