<?php
Yii::import('application.models.*');

class ReportPercentGraphsCommand extends CConsoleCommand
{
    public function run($dateToRecalc)
    {
        list($rewiew, $countDate) = getRewiew($dateToRecalc);
        for ($cd = 0; $cd < $countDate; $cd++) {
            $startTimeReport[$cd] = time();
            if ($rewiew[$cd] != null) {        //якщо треба перерахувати вручну за якийсь день
                $dayToCalc[$cd] = $rewiew[$cd];//присвоюємо час що ввели вручну

            }//якщо перерахунок
            if ($rewiew[$cd] == null) {                                 //робимо вночі кожного дня
                $dayToCalc[$cd] = date("Y-m-d", strtotime("yesterday"));//присвоюємо час що відповідає вчорашньому дню
            }//if calc yesterday
            //сам розрахунок
            $locate = ReportPercentageFlightsGraphs::model()->findAll([
                'condition' => 'date = :d',
                'params'    => [':d' => $dayToCalc[$cd]],
            ]);

            $countSql = count($locate);
            if ($countSql == 0) {
                $success[$cd] = 'N';
                $message[$cd] = "no found records in table for date " . $dayToCalc[$cd];
                $endTimeReport[$cd] = time();
                $newRecordReport = new ExecutionsCommands;
                $newRecordReport->date = date("Y-m-d");
                $newRecordReport->commands_id = 11;
                $newRecordReport->start_time = $startTimeReport[$cd];
                $newRecordReport->end_time = $endTimeReport[$cd];
                $newRecordReport->duration = $endTimeReport[$cd] - $startTimeReport[$cd];
                $newRecordReport->success = $success[$cd];
                $newRecordReport->comment = $message[$cd];
                $newRecordReport->save();
            }
            else if ($countSql != 0) {
                $success[$cd] = 'Y';
                foreach ($locate as $k) {
                    $arrayInsertFlights[] = [
                        'route_id'               => $k->routes_id,
                        'graphs_id'              => $k->graphs_id,
                        'borts_id'               => $k->borts_id,
                        'flights_number'         => $k->flights_number,
                        'percentage_realization' => $k->percentage_realization,
                        'percentage_stations'    => $k->percentage_stations,
                        'percentage_flight'      => $k->percentage_flight,
                        'count_stations_plan'    => $k->count_stations_plan,
                        'count_stations_fakt'    => $k->count_stations_fakt,
                        'count_flight_plan'      => $k->count_flight_plan,
                        'count_flight_fakt'      => $k->count_flight_fakt,
                        'percentage_end_stops'   => $k->percentage_end_stops,
                        'count_route_directions' => $k->count_route_directions,
                    ];
                }
                for ($i = 0; $i < count($arrayInsertFlights); $i++) {
                    //всього рейсів за планом
                    $arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_flight_plan'] = ($arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_flight_plan'] ?? 0) + ($arrayInsertFlights[$i]['count_flight_plan'] ?? 0);
                    //всього рейсів по факту
                    $arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_flight_fakt'] = ($arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_flight_fakt'] ?? 0) + ($arrayInsertFlights[$i]['count_flight_fakt'] ?? 0);
                    //всього % рейсів за ф/план*100,2
                    $arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['percentage_flight'] = round($arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_flight_fakt'] / $arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_flight_plan'] * 100, 2);
                    //всього точок за планом
                    $arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_stations_plan'] = ($arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_stations_plan'] ?? 0) + ($arrayInsertFlights[$i]['count_stations_plan'] ?? 0);
                    //всього точок по факту
                    $arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_stations_fakt'] = ($arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_stations_fakt'] ?? 0) + ($arrayInsertFlights[$i]['count_stations_fakt'] ?? 0);
                    //всього % точок за ф/план*100,2
                    $arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['percentage_stations'] = round($arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_stations_fakt'] / $arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_stations_plan'] * 100, 2);

                    //сума % реалізації
                    $arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['percentage_realization00'] = ($arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['percentage_realization00'] ?? 0) + ($arrayInsertFlights[$i]['percentage_realization'] ?? 0);
                    //сума % реалізації/кількіть рейсів - середнє арифметичне
                    $arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['percentage_realization11'] = round($arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['percentage_realization00'] / $arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_flight_plan'], 2);
                    //всього пів рейсів
                    $arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_route_directions'] = ($arrayInsertGraphs[$arrayInsertFlights[$i]['route_id']][$arrayInsertFlights[$i]['graphs_id']]['count_route_directions'] ?? 0) + ($arrayInsertFlights[$i]['count_route_directions'] ?? 0);
                }
                //print_r($arrayInsertGraphs);
                //видаляемо дані з таблиць
                ReportPercentageGraphs::model()->deleteAll([
                    'condition' => 'date = :d',
                    'params'    => [':d' => $dayToCalc[$cd]],
                ]);
                foreach ($arrayInsertGraphs as $routeid => $value) {
                    foreach ($value as $graphsid => $value1) {
                        if ($value1['percentage_realization11'] > $value1['percentage_stations']) {
                            $value1['percentage_realization11'] = $value1['percentage_stations'];
                        }
                        $ins = new ReportPercentageGraphs;
                        $ins->date = $dayToCalc[$cd];
                        $ins->routes_id = $routeid;
                        $ins->graphs_id = $graphsid;
                        $ins->percentage_realization = $value1['percentage_realization11'] ?? 0;
                        $ins->percentage_stations = $value1['percentage_stations'] ?? 0;
                        $ins->percentage_flight = $value1['percentage_flight'] ?? 0;
                        $ins->count_stations_plan = $value1['count_stations_plan'] ?? 0;
                        $ins->count_stations_fakt = $value1['count_stations_fakt'] ?? 0;
                        $ins->count_flight_plan = $value1['count_flight_plan'] ?? 0;
                        $ins->count_flight_fakt = $value1['count_flight_fakt'] ?? 0;
                        $ins->count_route_directions = $value1['count_route_directions'] ?? 0;
                        $ins->save();
                    }
                }
                foreach ($arrayInsertGraphs as $routeid => $value) {
                    $countGraph[$routeid] = count($value);
                    foreach ($value as $graphsid => $value1) {

                        $arrayInsertRoute[$routeid]['count_stations_plan'] = ($arrayInsertRoute[$routeid]['count_stations_plan'] ?? 0) + ($arrayInsertGraphs[$routeid][$graphsid]['count_stations_plan'] ?? 0);
                        $arrayInsertRoute[$routeid]['count_stations_fakt'] = ($arrayInsertRoute[$routeid]['count_stations_fakt'] ?? 0) + ($arrayInsertGraphs[$routeid][$graphsid]['count_stations_fakt'] ?? 0);
                        $arrayInsertRoute[$routeid]['count_flight_plan'] = ($arrayInsertRoute[$routeid]['count_flight_plan'] ?? 0) + ($arrayInsertGraphs[$routeid][$graphsid]['count_flight_plan'] ?? 0);
                        $arrayInsertRoute[$routeid]['count_flight_fakt'] = ($arrayInsertRoute[$routeid]['count_flight_fakt'] ?? 0) + ($arrayInsertGraphs[$routeid][$graphsid]['count_flight_fakt'] ?? 0);
                        $arrayInsertRoute[$routeid]['percentage_realization11'] = ($arrayInsertRoute[$routeid]['percentage_realization11'] ?? 0) + ($arrayInsertGraphs[$routeid][$graphsid]['percentage_realization11'] ?? 0);

                        $arrayInsertRoute[$routeid]['count_route_directions'] = ($arrayInsertRoute[$routeid]['count_route_directions'] ?? 0) + ($arrayInsertGraphs[$routeid][$graphsid]['count_route_directions'] ?? 0);

                    }
                }
                //видаляемо дані з таблиць
                ReportPercentageRoutesGraphs::model()->deleteAll([
                    'condition' => 'date = :d',
                    'params'    => [':d' => $dayToCalc[$cd]],
                ]);
                foreach ($arrayInsertRoute as $rout => $value) {
                    $rty = round($value['percentage_realization11'] / $countGraph[$rout], 2);
                    $rty1 = round($value['count_stations_fakt'] / $value['count_stations_plan'] * 100, 2);
                    if ($rty > $rty1) {
                        $rty = $rty1;
                    }
                    $ins = new ReportPercentageRoutesGraphs;
                    $ins->date = $dayToCalc[$cd];
                    $ins->routes_id = $rout;
                    $ins->percentage_realization = $rty;
                    $ins->percentage_stations = $rty1;
                    $ins->percentage_flight = round($value['count_flight_fakt'] / $value['count_flight_plan'] * 100, 2);
                    $ins->count_stations_plan = $value['count_stations_plan'];
                    $ins->count_stations_fakt = $value['count_stations_fakt'];
                    $ins->count_flight_plan = $value['count_flight_plan'];
                    $ins->count_flight_fakt = $value['count_flight_fakt'];
                    $ins->count_route_directions = $value['count_route_directions'];
                    $ins->save();
                }


                unset($locate);
                unset($countSql);
                unset($arrayInsertFlights);
                unset($arrayInsertGraphs);
                unset($countGraph);//
                unset($arrayInsertRoute);


                $endTimeReport[$cd] = time();
                $cdPlusOne = $cd + 1;
                $message[$cd] = "calc report on day " . $dayToCalc[$cd] . " " . $cdPlusOne . " from " . $countDate;
                $newRecordReport = new ExecutionsCommands;
                $newRecordReport->date = date("Y-m-d");
                $newRecordReport->commands_id = 11;
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