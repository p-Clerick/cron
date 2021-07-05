<?php
Yii::import('application.models.*');

class ReportSpeedCommand extends CConsoleCommand
{
    public function run($dateToRecalc)
    {


        $newRecordReport = new ExecutionsCommands;
        $newRecordReport->date = date("Y-m-d");
        $newRecordReport->commands_id = 13;
        $newRecordReport->start_time = strtotime(date("Y-m-d H:i:s"));
        //$newRecordReport->end_time=$endTimeReport[$cd];
        //$newRecordReport->duration=$endTimeReport[$cd]-$startTimeReport[$cd];
        //$newRecordReport->success=$success[$cd];
        //$newRecordReport->comment=$message[$cd];
        $newRecordReport->save();


        list($rewiew, $countDate) = getRewiew($dateToRecalc);
        $borts = Borts::model()->findAll([
            'order' => 'id',
        ]);
        $arrayBorts = [];
        foreach ($borts as $k) {
            $arrayBorts[] = $k->id;
        }

        $graphs = Graphs::model()->findAll();
        foreach ($graphs as $k) {
            $arrayGraphs[$k->id] = $k->routes_id;
        }
        $maxTimeToNoData = 30;
        $car = Route::model()->findAll();
        foreach ($car as $k) {
            $arrayCarriers[$k->id] = $k->carriers_id;
        }
        for ($cd = 0; $cd < $countDate; $cd++) {
            $startTimeReport[$cd] = time();
            if ($rewiew[$cd] != null) {        //якщо треба перерахувати вручну за якийсь день
                $dayToCalc[$cd] = $rewiew[$cd];//присвоюємо час що ввели вручну
            }
            else {                                                      //робимо вночі кожного дня
                $dayToCalc[$cd] = date("Y-m-d", strtotime("yesterday"));//присвоюємо час що відповідає вчорашньому дню
            }//if calc yesterday
            $tdfrom[$cd] = strtotime($dayToCalc[$cd]) + 3 * 3600;
            $tdto[$cd] = strtotime($dayToCalc[$cd]) + 26 * 3600 + 59 * 60 + 59 + 3600;
            // шукаемо всі борти
            for ($bi = 0; $bi < count($arrayBorts); $bi++) {
                $locations = Locations::model()->findAll([
                    'condition' => 'unixtime >= :f AND unixtime <= :t AND borts_id = :bid',
                    'params'    => [':f' => $tdfrom[$cd], ':t' => $tdto[$cd], ':bid' => $arrayBorts[$bi]],
                    'order'     => 'unixtime',
                ]);
                foreach ($locations as $k) {
                    $arraySpeedForOneBorts[$k->graphs_id][] = [
                        'lat'      => $k->latitude,
                        'long'     => $k->longitude,
                        'unixtime' => $k->unixtime,
                        'speed'    => $k->speed,
                    ];
                }
                $arraySpeedBeetwen = [];
                if (isset($arraySpeedForOneBorts)) {
                    foreach ($arraySpeedForOneBorts as $gid => $gidArray) {

                        if ($gid != 0) {
                            for ($i = 0; $i < count($gidArray) - 1; $i++) {
                                $timeBetween = $gidArray[$i + 1]['unixtime'] - $gidArray[$i]['unixtime'];
                                if ($timeBetween > $maxTimeToNoData) {
                                    $arraySpeedBeetwen[$arrayBorts[$bi]][$gid][999] = ($arraySpeedBeetwen[$arrayBorts[$bi]][$gid][999] ?? 0) + $timeBetween;
                                }
                                else if ($timeBetween <= $maxTimeToNoData) {
                                    if ($timeBetween == 0) {
                                        //$arraySpeedBeetwen[$arrayBorts[$bi]][$gid][999]=$arraySpeedBeetwen[$arrayBorts[$bi]][$gid][999]+$timeBetween;
                                    }
                                    else {
                                        $R = 6372795;
                                        $φA = (double)(floor($gidArray[$i]['lat'] / 100) * 100 + (($gidArray[$i]['lat'] - floor($gidArray[$i]['lat'] / 100) * 100) * 100 / 60)) / 100;
                                        $λA = (double)(floor($gidArray[$i]['long'] / 100) * 100 + (($gidArray[$i]['long'] - floor($gidArray[$i]['long'] / 100) * 100) * 100 / 60)) / 100;
                                        $φB = (double)(floor($gidArray[$i + 1]['lat'] / 100) * 100 + (($gidArray[$i + 1]['lat'] - floor($gidArray[$i + 1]['lat'] / 100) * 100) * 100 / 60)) / 100;
                                        $λB = (double)(floor($gidArray[$i + 1]['long'] / 100) * 100 + (($gidArray[$i + 1]['long'] - floor($gidArray[$i + 1]['long'] / 100) * 100) * 100 / 60)) / 100;
                                        // перевести координаты в радианы
                                        $lat1 = $φA * M_PI / 180;
                                        $lat2 = $φB * M_PI / 180;
                                        $long1 = $λA * M_PI / 180;
                                        $long2 = $λB * M_PI / 180;
                                        // косинусы и синусы широт и разницы долгот
                                        $cl1 = cos($lat1);
                                        $cl2 = cos($lat2);
                                        $sl1 = sin($lat1);
                                        $sl2 = sin($lat2);
                                        $delta = $long2 - $long1;
                                        $cdelta = cos($delta);
                                        $sdelta = sin($delta);

                                        // вычисления длины большого круга
                                        $y = sqrt(pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2));
                                        $x = $sl1 * $sl2 + $cl1 * $cl2 * $cdelta;

                                        //
                                        $ad = atan2($y, $x);
                                        $dist = round($ad * $R, 0);
                                        $Distance = $dist;
                                        $speed = round(($Distance / $timeBetween) * 3.6, 2);
                                        $roundToInt = intval($speed);
                                        $newKey = 0;
                                        if (isset($roundToInt)) {
                                            if ($roundToInt < 120) {
                                                if (($gidArray[$i]['speed'] == 0) || ($gidArray[$i + 1]['speed'] == 0)) {
                                                    if ($roundToInt >= 80) {
                                                        $newKey = 1;
                                                    }
                                                    if ($roundToInt < 80) {
                                                        $newKey = 0;
                                                    }
                                                }
                                                else {
                                                    $newKey = 0;
                                                }
                                            }
                                            if ($roundToInt >= 120) {
                                                $newKey = 1;
                                            }
                                            if ($newKey == 1) {
                                                $arraySpeedBeetwen[$arrayBorts[$bi]][$gid][999] = ($arraySpeedBeetwen[$arrayBorts[$bi]][$gid][999] ?? 0) + $timeBetween;
                                            }
                                            if ($newKey == 0) {
                                                $arraySpeedBeetwen[$arrayBorts[$bi]][$gid][$roundToInt] = ($arraySpeedBeetwen[$arrayBorts[$bi]][$gid][$roundToInt] ?? 0) + $timeBetween;
                                            }

                                        }
                                    }
                                }
                            }
                        }
                    }
                    ReportSpeedBorts::model()->deleteAll([
                        'condition' => 'date = :d AND borts_id = :b',
                        'params'    => [':d' => $dayToCalc[$cd], ':b' => $arrayBorts[$bi]],
                    ]);
                    if (isset($arraySpeedBeetwen)) {
                        foreach ($arraySpeedBeetwen as $bid => $bidArray) {
                            foreach ($bidArray as $gid => $gidArray) {
                                foreach ($gidArray as $key => $value) {
                                    if ($key != 999) {
                                        $insert = new ReportSpeedBorts;
                                        $insert->date = $dayToCalc[$cd];
                                        $insert->borts_id = $bid;
                                        if(isset($arrayGraphs[$gid]) && isset($arrayCarriers[$arrayGraphs[$gid]])) {
                                            $insert->carriers_id = $arrayCarriers[$arrayGraphs[$gid]];
                                        }
                                        if(isset($arrayGraphs[$gid])) {
                                            $insert->routes_id = $arrayGraphs[$gid];
                                        }
                                        $insert->graphs_id = $gid;
                                        $insert->speed_level = $key;
                                        $insert->time_sum = $value;
                                        $insert->save();
                                    }
                                    if ($key == 999) {
                                        $insert = new ReportSpeedBorts;
                                        $insert->date = $dayToCalc[$cd];
                                        $insert->borts_id = $bid;
                                        if(isset($arrayGraphs[$gid]) && isset($arrayCarriers[$arrayGraphs[$gid]])) {
                                            $insert->carriers_id = $arrayCarriers[$arrayGraphs[$gid]];
                                        }
                                        if(isset($arrayGraphs[$gid])) {
                                            $insert->routes_id = $arrayGraphs[$gid];
                                        }
                                        $insert->graphs_id = $gid;
                                        $insert->speed_level = null;
                                        $insert->time_sum = $value;
                                        $insert->save();
                                    }
                                }
                            }
                        }
                        unset($arraySpeedForOneBorts);
                        unset($arraySpeedBeetwen);
                    }

                    //echo $arrayBorts[$bi]."\r\n";
                    $newRecordReport = new ExecutionsCommands;
                    $newRecordReport->date = date("Y-m-d");
                    $newRecordReport->commands_id = 13;
                    $newRecordReport->start_time = strtotime(date("Y-m-d H:i:s"));
                    //$newRecordReport->end_time=$endTimeReport[$cd];
                    //$newRecordReport->duration=$endTimeReport[$cd]-$startTimeReport[$cd];
                    //$newRecordReport->success=$success[$cd];
                    $newRecordReport->comment = $arrayBorts[$bi];
                    $newRecordReport->save();
                }
            }//for ($bi=0; $bi < count($arrayBorts) ; $bi++)
            //print_r($arraySpeedForOneBorts);
            $endTimeReport[$cd] = time();
            $cdPlusOne = $cd + 1;
            $success[$cd] = 'Y';
            $message[$cd] = "calc report on day " . $dayToCalc[$cd] . " " . $cdPlusOne . " from " . $countDate;
            $newRecordReport = new ExecutionsCommands;
            $newRecordReport->date = date("Y-m-d");
            $newRecordReport->commands_id = 13;
            $newRecordReport->start_time = $startTimeReport[$cd];
            $newRecordReport->end_time = $endTimeReport[$cd];
            $newRecordReport->duration = $endTimeReport[$cd] - $startTimeReport[$cd];
            $newRecordReport->success = $success[$cd];
            $newRecordReport->comment = $message[$cd];
            $newRecordReport->save();
        }//for ($cd=0; $cd < $countDate; $cd++)
    }//public function run($dateToRecalc)
}

?>