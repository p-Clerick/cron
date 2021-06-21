<?php
Yii::import('application.models.*');
class ReportEndStopsCommand extends CConsoleCommand
{
    public function run($dateToRecalc) {
        $rewiew=$dateToRecalc;
        $countDate=count($rewiew);
        if ($countDate==0){
            $day=date('Y-m-d');
            $find=DaysToReport::model()->findByAttributes(array(
                'date'=>$day));
            $dy=$find->found_days;
            $dyy=explode(",", $dy);
            foreach ($dyy as $key => $value) {
                $rewiew[$key]=$value;
            }
            $countDate=count($rewiew);
        }
        //шукаемо точки контролю по сценарію
        $pc=StationsScenario::model()->findAll(array(
            'condition'=>'pc_status = :st',
            'params'   =>array(':st'=>'yes'),
            'order'    => 'number'));
        foreach ($pc as $k) {
            $arrPc[$k->routes_id][]=array(
                'routeId'=>$k->routes_id,
                'direct' => $k->route_directions_id,
                'number'=>$k->number,
                'stid'=>$k->stations_id,
                'pcst'=>$k->pc_status,
                'id'=>$k->id
            );
        }
        //шукаемо кінцеві
        foreach ($arrPc as $rout => $value) {
            $countRec[$rout]=count($value);
            $e[$rout]=1;
            foreach ($value as $n => $value1) {
                foreach ($value1 as $key => $value2) {
                    if (($key=='direct') && ($arrPc[$rout][$n]['direct']!=$arrPc[$rout][$n-1]['direct']) && ($n!=0)) {
                        $arrkin[$rout][$e[$rout]]=$arrPc[$rout][$n-1]['stid'];
                        $e[$rout]=$e[$rout]+1;
                        $arrkin[$rout][$e[$rout]]=$arrPc[$rout][$n]['stid'];
                        $e[$rout]=$e[$rout]+1;
                    }
                    if (($key=='number') && ($value2==1)) {
                        $arrkin[$rout][$e[$rout]]=$arrPc[$rout][$n]['stid'];
                        $e[$rout]=$e[$rout]+1;
                    }
                    if (($key=='number') && ($n==$countRec[$rout]-1)) {
                        $arrkin[$rout][$e[$rout]]=$arrPc[$rout][$n]['stid'];
                        $e[$rout]=$e[$rout]+1;
                    }
                    $countKin[$rout]=count($arrkin[$rout]);
                }
            }
        }
        //print_r($arrkin);
        $arrayPark=Borts::model()->findAll();
        foreach ($arrayPark as $k) {
            $arrayParkToInsert[$k->id]=$k->parks_id;
        }

        $arrayCarriers=Graphs::model()->findAll();
        foreach ($arrayCarriers as $k) {
            $arrCarr[$k->id]=$k->carriers_id;
        }

        for ($cd=0; $cd < $countDate; $cd++) {
            $startTimeReport[$cd]=time();
            if ($rewiew[$cd]!=null) {//якщо треба перерахувати вручну за якийсь день
                $dayToCalc[$cd]=$rewiew[$cd];//присвоюємо час що ввели вручну
                //видаляемо дані з таблиць
                ReportEndStops::model()->deleteAll(array(
                    'condition' => 'date = :d',
                    'params' => array(':d' => $dayToCalc[$cd])));
            }//якщо перерахунок
            if ($rewiew[$cd]==null){//робимо вночі кожного дня
                $dayToCalc[$cd]=date("Y-m-d",strtotime ("yesterday"));//присвоюємо час що відповідає вчорашньому дню
            }//if calc yesterday
            //сам розрахунок
            $todayFrom[$cd]=strtotime($dayToCalc[$cd])+3600;
            $todayTo[$cd]=strtotime($dayToCalc[$cd])+23*3600+59*60+60+3600;
            $a=Yii::app()->db->createCommand(
                "SELECT routes_id, graphs_id, borts_id, flights_number, stations_id, schedules_id
					from locations_in_flights
					where  unixtime between '".$todayFrom[$cd]."' and '".$todayTo[$cd]."'")->queryAll();
            //print_r($a);
            $countA=count($a);
            if ($countA==0) {
                $success[$cd]='N';
                $message[$cd]="no found records in table for date ".$dayToCalc[$cd];
                $endTimeReport[$cd]=time();
                $newRecordReport = new ExecutionsCommands;
                $newRecordReport->date=date("Y-m-d");
                $newRecordReport->commands_id=5;
                $newRecordReport->start_time=$startTimeReport[$cd];
                $newRecordReport->end_time=$endTimeReport[$cd];
                $newRecordReport->duration=$endTimeReport[$cd]-$startTimeReport[$cd];
                $newRecordReport->success=$success[$cd];
                $newRecordReport->comment=$message[$cd];
                $newRecordReport->save();
            }
            else if ($countA!=0) {
                $success[$cd]='Y';
                foreach ($a as $key => $value) {
                    $arrayBort[$value['schedules_id']]=$value['borts_id'];
                    $arrayFakt[$value['schedules_id']][$value['flights_number']][$value['stations_id']]=$value['stations_id'];
                    $arraySchedules[$value['schedules_id']]=array(
                        $value['routes_id'],
                        $value['graphs_id']
                    );
                }
                foreach ($arrayFakt as $key => $value) {
                    $b=ScheduleTimes::model()->findAll(array(
                        'condition'=> 'schedules_id = :stid',
                        'params'   =>array(':stid'=>$key),
                        'order'    => 'id'));
                    foreach ($b as $k) {
                        $arrayPlan[$k->schedules_id][$k->flights_number][$k->stations_id]=$k->stations_id;
                        $arrayPlanTime[$k->schedules_id][$k->flights_number][$k->stations_id]=$k->time;
                    }
                }
                foreach ($arrayPlan as $sc => $value) {
                    foreach ($value as $fl => $stat) {
                        foreach ($stat as $key => $value1) {
                            if (!isset($arrayFakt[$sc][$fl][$key])) {
                                $arrayNePopav[$sc][$fl][$key]=$value1;
                            }
                        }
                    }
                }

                if (isset($arrayNePopav)){
                    foreach ($arrayNePopav as $sced => $arsch) {
                        foreach ($arsch as $fl => $stat) {
                            foreach ($stat as $key => $value) {
                                for ($q=1; $q <= 20; $q++) {
                                    if ($value==$arrkin[$arraySchedules[$sced][0]][$q]) {
                                        $arrayNePopavKinets[$sced][$fl][$q]=$value;
                                    }
                                }
                            }
                        }
                    }
                }

                if (isset($arrayNePopavKinets)){
                    foreach ($arrayNePopavKinets as $sced => $arsch) {
                        foreach ($arsch as $fl => $stat) {
                            foreach ($stat as $key => $value) {
                                if ($key==1) {
                                    $fln=$fl;
                                    if (isset($arrayNePopavKinets[$sced][$fln-1])) {
                                        if (isset($arrayNePopavKinets[$sced][$fln-1][$countKin[$arraySchedules[$sced][0]]])) {

                                            $arrayToInsert[]=array(
                                                'fl'=>$fl-1,
                                                'stationId'=>$arrayNePopavKinets[$sced][$fln-1][$countKin[$arraySchedules[$sced][0]]],
                                                'scheduleId'=>$sced
                                            );
                                            $arrayToInsert[]=array(
                                                'fl'=>$fl,
                                                'stationId'=>$value,
                                                'scheduleId'=>$sced
                                            );
                                        }
                                    }
                                }
                                if ($key!=1) {
                                    if ($key & 1) {
                                        if (isset($arrayNePopavKinets[$sced][$fl][$key-1])) {
                                            $arrayToInsert[]=array(
                                                'fl'=>$fl,
                                                'stationId'=>$arrayNePopavKinets[$sced][$fl][$key-1],
                                                'scheduleId'=>$sced
                                            );
                                            $arrayToInsert[]=array(
                                                'fl'=>$fl,
                                                'stationId'=>$value,
                                                'scheduleId'=>$sced
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $countInsert=count($arrayToInsert);
                //шукаемо парк
                for ($i=0; $i < $countInsert; $i++) {
                    $bortInsert=$arrayBort[$arrayToInsert[$i]['scheduleId']];
                    $ee = new ReportEndStops;
                    $ee->date=$dayToCalc[$cd];
                    $ee->routes_id=$arraySchedules[$arrayToInsert[$i]['scheduleId']][0];
                    $ee->graphs_id=$arraySchedules[$arrayToInsert[$i]['scheduleId']][1];
                    $ee->borts_id=$arrayBort[$arrayToInsert[$i]['scheduleId']];
                    $ee->flights_number=$arrayToInsert[$i]['fl'];
                    $ee->stations_id=$arrayToInsert[$i]['stationId'];
                    $ee->parks_id=$arrayParkToInsert[$bortInsert];
                    $route=$arraySchedules[$arrayToInsert[$i]['scheduleId']][0];
                    $graph=$arraySchedules[$arrayToInsert[$i]['scheduleId']][1];
                    $ee->carriers_id=$arrCarr[$graph];
                    $ee->arrival_plan=$arrayPlanTime[$arrayToInsert[$i]['scheduleId']][$arrayToInsert[$i]['fl']][$arrayToInsert[$i]['stationId']];
                    $ee->save();
                }
                unset($arrayToInsert);//
                unset($arrayNePopavKinets);//
                unset($arrayNePopav);//
                unset($arrayBort);//////////
                unset($arrayFakt);//////////
                unset($arraySchedules);/////
                unset($arrayPlan);/////////
                unset($arrayPlanTime);/////
                unset($a);
                $endTimeReport[$cd]=time();
                $cdPlusOne=$cd+1;
                $message[$cd]="calc report on day ".$dayToCalc[$cd]." ".$cdPlusOne." from ".$countDate;
                $newRecordReport = new ExecutionsCommands;
                $newRecordReport->date=date("Y-m-d");
                $newRecordReport->commands_id=5;
                $newRecordReport->start_time=$startTimeReport[$cd];
                $newRecordReport->end_time=$endTimeReport[$cd];
                $newRecordReport->duration=$endTimeReport[$cd]-$startTimeReport[$cd];
                $newRecordReport->success=$success[$cd];
                $newRecordReport->comment=$message[$cd];
                $newRecordReport->save();
            }

        }
    }
}
?>