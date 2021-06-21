<?php

/**
 * Контроллер RouteSearchController
 *
 */
class RouteSearchController extends Controller {
    public function actionSearchRoute() {
        /**
         * Тип транспортного засобу
         */ 
        $transportType = Yii::app()->request->getParam('transportType');

        /**
         * Пріоритет в пошуку маршруту
         */ 
        $priority = Yii::app()->request->getParam('priority');

        /**
         * Наявність пересадок
         */ 
        $hasTransshipment = Yii::app()->request->getParam('hasTransshipment');

        /**
         * Масив точок маршруту
         */ 
        $points = CJSON::decode((Yii::app()->request->getParam('pointsJSON')));

        /**
         * Початкова точка маршруту
         */ 
        $startPoint = $points['startPoint'];

        /**
         * Кінцева точка маршруту
         */ 
        $endPoint = $points['endPoint'];

        /**
         * Масив всіх зупинок міста
         */
    	$stations = Stations::model()->getAllStations();

        /**
         * Масив всіх активних маршрутів міста
         */
    	$routes = Route::model()->getAllActiveRoutes();

        /**
         * Масив всіх сценаріїв зупинок
         */
    	$stationsScenario = StationsScenario::model()->getAllStationsScenario();

        /**
         * Масив відстаней між зупинками
         */
        $stationsDistances = DistanceStations::model()->getAllStationsDistances();

        /**
         * Масив відповідностей "ID маршруту - ID інтервалу руху по маршруті" 
         */
        $dayIntervalRoute = DayIntervalRoute::model()->getAllDayIntervalRoute();

        /**
         * Масив інтервалів руху між зупиками 
         */
    	$dayIntervalStations = DayIntervalStations::model()->getAllDayIntervalStations();

        // Знаходимо та складаємо список маршрутів
        $result = RouteSearch::findRoutes($startPoint, $endPoint, $stations, $routes, $stationsScenario, $stationsDistances, $dayIntervalRoute, $dayIntervalStations, $priority);

        // Кодуємо результат у формат JSON
        echo CJSON::encode($result);
	}

    public function actionGetStations(){
        /**
         * Масив всіх зупинок міста
         */
        $stations = Stations::model()->getAllStations();

        // Кодуємо результат у формат JSON
        echo CJSON::encode($stations);
    }
}

?>
