<?php
Yii::import('application.models.*');

class ReportPercentFlightsCommand extends CConsoleCommand
{
    public function run($dateToRecalc)
    {
        list($rewiew, $countDate) = getRewiew($dateToRecalc);
        $ds = StationsScenario::model()->findAll();
        foreach ($ds as $key111) {
            $arrayPlanDirections[$key111->routes_id][$key111->stations_id] = $key111->route_directions_id;
        }
        for ($cd = 0; $cd < $countDate; $cd++) {
            $startTimeReport[$cd] = time();
            if ($rewiew[$cd] != null) {        //якщо треба перерахувати вручну за якийсь день
                $dayToCalc[$cd] = $rewiew[$cd];//присвоюємо час що ввели вручну
                //видаляемо дані з таблиць
                /*	ReportPercentageFlightsGraphs::model()->deleteAll(array(
                        'condition' => 'date = :d',
                        'params' => array(':d' => $dayToCalc[$cd])));
                    ReportPercentageGraphs::model()->deleteAll(array(
                        'condition' => 'date = :d',
                        'params' => array(':d' => $dayToCalc[$cd])));
                    ReportPercentageRoutesGraphs::model()->deleteAll(array(
                        'condition' => 'date = :d',
                        'params' => array(':d' => $dayToCalc[$cd])));*/
            }//якщо перерахунок
            if ($rewiew[$cd] == null) {                                 //робимо вночі кожного дня
                $dayToCalc[$cd] = date("Y-m-d", strtotime("yesterday"));//присвоюємо час що відповідає вчорашньому дню
            }//if calc yesterday
            //сам розрахунок
            $dayToCalcFrom[$cd] = strtotime($dayToCalc[$cd]) + 3600;
            $dayToCalcTo[$cd] = strtotime($dayToCalc[$cd]) + 23 * 3600 + 59 * 60 + 60 + 3600;
            //шукаемо всі дані за потрібний день
            $locate = LocationsFlights::model()->findAll([
                'select'    => 'borts_id, routes_id, graphs_id, stations_id, schedules_id, flights_number',
                'condition' => 'unixtime >= :f AND unixtime <= :t',
                'params'    => [':f' => $dayToCalcFrom[$cd], ':t' => $dayToCalcTo[$cd]],
            ]);
            $countSql = count($locate);
            if ($countSql == 0) {
                $success[$cd] = 'N';
                $message[$cd] = "no found records in table for date " . $dayToCalc[$cd];
                $endTimeReport[$cd] = time();
                $newRecordReport = new ExecutionsCommands;
                $newRecordReport->date = date("Y-m-d");
                $newRecordReport->commands_id = 7;
                $newRecordReport->start_time = $startTimeReport[$cd];
                $newRecordReport->end_time = $endTimeReport[$cd];
                $newRecordReport->duration = $endTimeReport[$cd] - $startTimeReport[$cd];
                $newRecordReport->success = $success[$cd];
                $newRecordReport->comment = $message[$cd];
                $newRecordReport->save();
            }
            else if ($countSql != 0) {
                $success[$cd] = 'Y';
                foreach ($locate as $klocate) {
                    $arrayFact[$klocate->schedules_id][$klocate->flights_number][$klocate->stations_id][] = $klocate->stations_id;
                    $arraySchedules[$klocate->schedules_id] = [$klocate->routes_id, $klocate->graphs_id];
                    $arrayBorts[$klocate->graphs_id] = $klocate->borts_id;

                }
                //шукаемо по плану
                foreach ($arraySchedules as $schedid => $value) {
                    $r = ScheduleTimes::model()->findAll([
                        'select'    => 'flights_number, stations_id',
                        'condition' => 'schedules_id = :sid',
                        'params'    => [':sid' => $schedid],
                    ]);
                    foreach ($r as $k) {
                        $arrayPlan[$schedid][$k->flights_number][$k->stations_id] = $k->stations_id;
                        $arrayPlanDirectionsStations[$schedid][$k->flights_number][$arrayPlanDirections[$arraySchedules[$schedid][0]][$k->stations_id]] = $arrayPlanDirections[$arraySchedules[$schedid][0]][$k->stations_id];
                    }
                }
                //print_r($arrayPlanDirectionsStations);
                //порівнюємо
                foreach ($arrayPlan as $schedid => $arFl) {
                    foreach ($arFl as $flN => $arSt) {
                        $countFact[$schedid][$flN] = count($arrayFact[$schedid][$flN]);
                        $countPlan[$schedid][$flN] = count($arrayPlan[$schedid][$flN]);

                    }
                }
                //шукаемо кінцеві
                $endStops = ReportEndStops::model()->findAll([
                    'condition' => 'date = :date',
                    'select'    => 'routes_id, graphs_id, flights_number',
                    'params'    => [':date' => $dayToCalc[$cd]],
                ]);
                foreach ($endStops as $k) {
                    $arrayEndStops[$k->routes_id][$k->graphs_id][$k->flights_number] = ($arrayEndStops[$k->routes_id][$k->graphs_id][$k->flights_number] ?? 0) + 1;
                }

                //insert
                foreach ($countPlan as $schedid => $value) {
                    foreach ($value as $flN => $value1) {
                        if ($countFact[$schedid][$flN] == 0) {
                            $u[$schedid][$flN] = 0;
                        }
                        if ($countFact[$schedid][$flN] != 0) {
                            $u[$schedid][$flN] = 1;
                        }
                        $kkkk[$schedid][$flN] = round($countFact[$schedid][$flN] / $countPlan[$schedid][$flN] * 100, 2);
                        if (isset($arrayEndStops) && $arrayEndStops[$arraySchedules[$schedid][0]][$arraySchedules[$schedid][1]][$flN] >= 1) {
                            $kkkk[$schedid][$flN] = 0;
                        }
                        if (round($countFact[$schedid][$flN] / $countPlan[$schedid][$flN] * 100, 2) == 100) {
                            $kkkk[$schedid][$flN] = 100;
                        }
                        $arrayInsertFlights[] = [
                            'route_id'               => $arraySchedules[$schedid][0],
                            'graphs_id'              => $arraySchedules[$schedid][1],
                            'flight'                 => $flN,
                            'countFlightPlan'        => 1,
                            'countFlightFakt'        => $u[$schedid][$flN],
                            'percentFlight'          => round($u[$schedid][$flN] / 1 * 100, 2),
                            'countStationPlan'       => $countPlan[$schedid][$flN],
                            'countStationFakt'       => $countFact[$schedid][$flN],
                            'percentStation'         => round($countFact[$schedid][$flN] / $countPlan[$schedid][$flN] * 100, 2),
                            'percentrealization'     => $kkkk[$schedid][$flN],
                            'count_route_directions' => count($arrayPlanDirectionsStations[$schedid][$flN]),
                        ];
                    }
                }
                //print_r($arrayInsertFlights);
                //видаляемо дані з таблиць
                ReportPercentageFlightsGraphs::model()->deleteAll([
                    'condition' => 'date = :d',
                    'params'    => [':d' => $dayToCalc[$cd]],
                ]);
                $countAIF = count($arrayInsertFlights);
                for ($i = 0; $i < $countAIF; $i++) {
                    if ($arrayInsertFlights[$i]['percentrealization'] > $arrayInsertFlights[$i]['percentStation']) {
                        $arrayInsertFlights[$i]['percentrealization'] = $arrayInsertFlights[$i]['percentStation'];
                    }
                    if ($arrayInsertFlights[$i]['percentStation'] == $arrayInsertFlights[$i]['percentrealization']) {
                        if ($arrayInsertFlights[$i]['percentStation'] == 0) {
                            $arrayInsertFlights[$i]['percentage_end_stops'] = 0;
                        }
                        if ($arrayInsertFlights[$i]['percentStation'] > 0) {
                            $arrayInsertFlights[$i]['percentage_end_stops'] = 100;
                        }
                    }
                    if ($arrayInsertFlights[$i]['percentStation'] != $arrayInsertFlights[$i]['percentrealization']) {
                        $arrayInsertFlights[$i]['percentage_end_stops'] = 100 - round(100 * ($arrayEndStops[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']][$arrayInsertFlights[$i]['flight']]) / 4, 2);
                    }

                    /*	$arrayFindDoubleSchedules[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']][$arrayInsertFlights[$i]['flight']][]=array(
                            'borts_id'=>$arrayBorts[$arrayInsertFlights[$i]['graphs_id']],
                            'percentage_realization'=>$arrayInsertFlights[$i]['percentrealization'],
                            'percentage_stations'=>$arrayInsertFlights[$i]['percentStation'],
                            'percentage_flight'=>$arrayInsertFlights[$i]['percentFlight'],
                            'count_stations_plan'=>$arrayInsertFlights[$i]['countStationPlan'],
                            'count_stations_fakt'=>$arrayInsertFlights[$i]['countStationFakt'],
                            'count_flight_plan'=>$arrayInsertFlights[$i]['countFlightPlan'],
                            'count_flight_fakt'=>$arrayInsertFlights[$i]['countFlightFakt'],
                            'percentage_end_stops'=>$arrayInsertFlights[$i]['percentage_end_stops'],
                            'count_route_directions'=>$arrayInsertFlights[$i]['count_route_directions']
                        );*/
                    $ins = new ReportPercentageFlightsGraphs;
                    $ins->date = $dayToCalc[$cd];
                    $ins->routes_id = $arrayInsertFlights[$i]['route_id'];
                    $ins->graphs_id = $arrayInsertFlights[$i]['graphs_id'];
                    $ins->borts_id = $arrayBorts[$arrayInsertFlights[$i]['graphs_id']];
                    $ins->flights_number = $arrayInsertFlights[$i]['flight'];
                    $ins->percentage_realization = $arrayInsertFlights[$i]['percentrealization'];
                    $ins->percentage_stations = $arrayInsertFlights[$i]['percentStation'];
                    $ins->percentage_flight = $arrayInsertFlights[$i]['percentFlight'];
                    $ins->count_stations_plan = $arrayInsertFlights[$i]['countStationPlan'];
                    $ins->count_stations_fakt = $arrayInsertFlights[$i]['countStationFakt'];
                    $ins->count_flight_plan = $arrayInsertFlights[$i]['countFlightPlan'];
                    $ins->count_flight_fakt = $arrayInsertFlights[$i]['countFlightFakt'];
                    $ins->percentage_end_stops = $arrayInsertFlights[$i]['percentage_end_stops'];
                    $ins->count_route_directions = $arrayInsertFlights[$i]['count_route_directions'];
                    $ins->save();
                }

                //print_r($arrayFindDoubleSchedules);


                unset($locate);
                unset($countSql);
                unset($arrayFact);                  //
                unset($arraySchedules);             //
                unset($arrayBorts);                 //
                unset($countFact);                  //
                unset($countPlan);                  //
                unset($arrayPlan);                  //
                unset($arrayPlanDirectionsStations);//
                unset($arrayEndStops);              //
                unset($u);                          //
                unset($kkkk);                       //
                unset($arrayInsertFlights);         //
                unset($countAIF);

                $endTimeReport[$cd] = time();
                $cdPlusOne = $cd + 1;
                $message[$cd] = "calc report on day " . $dayToCalc[$cd] . " " . $cdPlusOne . " from " . $countDate;
                $newRecordReport = new ExecutionsCommands;
                $newRecordReport->date = date("Y-m-d");
                $newRecordReport->commands_id = 10;
                $newRecordReport->start_time = $startTimeReport[$cd];
                $newRecordReport->end_time = $endTimeReport[$cd];
                $newRecordReport->duration = $endTimeReport[$cd] - $startTimeReport[$cd];
                $newRecordReport->success = $success[$cd];
                $newRecordReport->comment = $message[$cd];
                $newRecordReport->save();
            }
        }
    }
}

?>