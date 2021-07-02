<?php
Yii::import('application.models.*');

class ReportAverageGraphsCommand extends CConsoleCommand
{
    public function run($dateToRecalc)
    {
        $rewiew = $dateToRecalc;
        $countDate = count($rewiew);
        if ($countDate == 0) {
            $day = date('Y-m-d');
            $find = DaysToReport::model()->findByAttributes([
                'date' => $day,
            ]);
            $dy = $find->found_days;
            $dyy = explode(",", $dy);
            foreach ($dyy as $key => $value) {
                $rewiew[$key] = $value;
            }
            $countDate = count($rewiew);
        }
        for ($cd = 0; $cd < $countDate; $cd++) {
            $startTimeReport[$cd] = time();
            if ($rewiew[$cd] != null) {        //якщо треба перерахувати вручну за якийсь день
                $dayToCalc[$cd] = $rewiew[$cd];//присвоюємо час що ввели вручну
                //видаляемо дані з таблиць
                if ($dayToCalc[$cd]) {
                    ReportAverageGraphs::model()->deleteAll([
                        'condition' => 'date = :d',
                        'params'    => [':d' => $dayToCalc[$cd]],
                    ]);
                }
            }
            //якщо перерахунок вручну
            if ($rewiew[$cd] == null) {                                 //робимо вночі кожного дня
                $dayToCalc[$cd] = date("Y-m-d", strtotime("yesterday"));//присвоюємо час що відповідає вчорашньому дню
            }//if calc yesterday
            $tdfrom[$cd] = strtotime($dayToCalc[$cd]) + 3600;
            $tdto[$cd] = strtotime($dayToCalc[$cd]) + 23 * 3600 + 59 * 60 + 59 + 3600;
            $sql = Yii::app()->db->createCommand()
                ->select()
                ->from('locations_in_flights')
                ->where('unixtime>=' . $tdfrom[$cd] . ' and unixtime<=' . $tdto[$cd])
                ->order('schedules_id')
                ->queryAll();
            //print_r($sql);
            $countSql = count($sql);
            if ($countSql == 0) {
                $success[$cd] = 'N';
                $message[$cd] = "no found records in table for date " . $dayToCalc[$cd];
                $endTimeReport[$cd] = time();
                $newRecordReport = new ExecutionsCommands;
                $newRecordReport->date = date("Y-m-d");
                $newRecordReport->commands_id = 4;
                $newRecordReport->start_time = $startTimeReport[$cd];
                $newRecordReport->end_time = $endTimeReport[$cd];
                $newRecordReport->duration = $endTimeReport[$cd] - $startTimeReport[$cd];
                $newRecordReport->success = $success[$cd];
                $newRecordReport->comment = $message[$cd];
                $newRecordReport->save();
            }
            else if ($countSql != 0) {
                $success[$cd] = 'Y';
                foreach ($sql as $key => $value) {
                    foreach ($value as $key2 => $value2) {
                        if ($key2 == 'time_difference') {
                            if ($value2 >= 3) {
                                $arrayDiffPlus[$sql[$key]['routes_id']][$sql[$key]['graphs_id']][] = $value2;
                            }
                            else if ($value2 <= (-3)) {
                                $arrayDiffMinus[$sql[$key]['routes_id']][$sql[$key]['graphs_id']][] = $value2;
                            }
                            else {
                                $arrayDiff[$sql[$key]['routes_id']][$sql[$key]['graphs_id']][] = $value2;
                            }
                            if (isset($countAll[$sql[$key]['routes_id']][$sql[$key]['graphs_id']])) {
                                $countAll[$sql[$key]['routes_id']][$sql[$key]['graphs_id']] = $countAll[$sql[$key]['routes_id']][$sql[$key]['graphs_id']] + 1;
                            }
                            else {
                                $countAll[$sql[$key]['routes_id']][$sql[$key]['graphs_id']] = 1;
                            }
                        }
                    }
                }
                if (isset($arrayDiffPlus)) {
                    foreach ($arrayDiffPlus as $key => $value) {
                        foreach ($value as $key1 => $value1) {
                            $countPlus[$key][$key1] = count($value1);
                            $avPlus[$key][$key1] = round(array_sum($value1) / $countPlus[$key][$key1], 1);
                            $plusaver[$key][$key1] = round($countPlus[$key][$key1] / $countAll[$key][$key1] * 100, 0);
                        }
                    }
                }
                if (isset($arrayDiffMinus)) {
                    foreach ($arrayDiffMinus as $key => $value) {
                        foreach ($value as $key1 => $value1) {
                            $countMinus[$key][$key1] = count($value1);
                            $avMinus[$key][$key1] = round(array_sum($value1) / $countMinus[$key][$key1], 1);
                            $minusaver[$key][$key1] = round($countMinus[$key][$key1] / $countAll[$key][$key1] * 100, 0);
                        }
                    }
                }
                if (isset($arrayDiff)) {
                    foreach ($arrayDiff as $key => $value) {
                        foreach ($value as $key1 => $value1) {
                            $countDiff[$key][$key1] = count($value1);
                            $avDiff[$key][$key1] = round(array_sum($value1) / $countDiff[$key][$key1], 1);
                            $diffaver[$key][$key1] = 100 - ($minusaver[$key][$key1] ?? 0) - ($plusaver[$key][$key1] ?? 0);
                        }
                    }
                }
                //$routes = Route::model()->findAll(array('condition' => 'status = "yes"'));
                $arrayCarriers = Graphs::model()->findAll();
                foreach ($arrayCarriers as $k) {
                    $arrCarr[$k->id] = $k->carriers_id;
                }
                foreach ($countAll as $key => $value) {
                    foreach ($value as $key1 => $value1) {
                        /*foreach ($routes as $route) {
                            if ($key == $route->id) {
                                $carriers_id = $route->carriers_id;
                            }
                        }*/
                        $w = new ReportAverageGraphs;
                        $w->date = $dayToCalc[$cd];
                        $w->carriers_id = $arrCarr[$key1];
                        $w->routes_id = $key;
                        $w->graphs_id = $key1;
                        $w->lateness_percentage = $minusaver[$key][$key1] ?? 0;
                        $w->lateness_average = $avMinus[$key][$key1] ?? 0;
                        $w->advance_percentage = $plusaver[$key][$key1] ?? 0;
                        $w->advance_average = $avPlus[$key][$key1] ?? 0;
                        $w->ontime_percentage = $diffaver[$key][$key1] ?? 0;
                        $w->ontime_average = $avDiff[$key][$key1] ?? 0;
                        $w->save();
                    }
                }
                unset($arrayDiffPlus);
                unset($arrayDiffMinus);
                unset($arrayDiff);
                unset($countAll);
                unset($countPlus);
                unset($avPlus);
                unset($plusaver);
                unset($countMinus);
                unset($avMinus);
                unset($minusaver);
                unset($countDiff);
                unset($avDiff);
                unset($diffaver);
                $endTimeReport[$cd] = time();
                $cdPlusOne = $cd + 1;
                $message[$cd] = "calc report on day " . $dayToCalc[$cd] . " " . $cdPlusOne . " from " . $countDate;
                $newRecordReport = new ExecutionsCommands;
                $newRecordReport->date = date("Y-m-d");
                $newRecordReport->commands_id = 4;
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