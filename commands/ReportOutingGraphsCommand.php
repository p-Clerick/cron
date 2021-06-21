<?php
Yii::import('application.models.*');

class ReportOutingGraphsCommand extends CConsoleCommand
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
            if (!$dy){
                echo "No days to report\n";
                return false;
            }
            $dyy = explode(",", $dy);
            foreach ($dyy as $key => $value) {
                $rewiew[$key] = $value;
            }
            $countDate = count($rewiew);
        }

        for ($cd = 0; $cd < $countDate; $cd++) {
            $ab = Borts::model()->with('park')->findAll();
            $arrayBorts = [];
            foreach ($ab as $k) {
                //if ($k->status=='yes') {
                $arrayBorts[$k->id] = [
                    'bortId'      => $k->id,
                    'parks_id'    => $k->parks_id,
                    'carriers_id' => $k->park->carriers_id,
                    'st'          => $k->status,
                ];
                //}
            }
            $countBorts = count($arrayBorts);
            $grAll = Graphs::model()->findAll();
            $arrayInsert = [];
            foreach ($grAll as $k) {
                //if ($k->status=='yes') {
                $arrayInsert[] = [
                    'graphId'     => $k->id,
                    'routes_id'   => $k->routes_id,
                    'carriers_id' => $k->carriers_id,
                ];
                //}
            }
            $countAllGraphs = count($arrayInsert);
            //print_r($grAllInsert);
            if (!isset($day)) {
                $day = date("Y-m-d");
            }
            $startTimeReport = $dayToCalc[$cd] = time();

            if ($rewiew[$cd] != null) {        //якщо треба перерахувати вручну за якийсь день
                $dayToCalc[$cd] = $rewiew[$cd];//присвоюємо час що ввели вручну
            }//якщо перерахунок
            if ($rewiew[$cd] == null) {                                 //робимо вночі кожного дня
                $dayToCalc[$cd] = date("Y-m-d", strtotime("yesterday"));//присвоюємо час що відповідає вчорашньому дню
            }//if calc yesterday
            //сам розрахунок
            $todayFrom[$cd] = strtotime($dayToCalc[$cd]) + 3600;
            $todayTo[$cd] = strtotime($dayToCalc[$cd]) + 23 * 3600 + 59 * 60 + 60 + 3600;
            $statusBortsOut = $arrayBorts;
            for ($i = 0; $i < $countAllGraphs; $i++) {
                $e = LocationsFlights::model()->find([
                    'select'    => 'borts_id',
                    'distinct'  => true,
                    'condition' => 'graphs_id = :grid AND unixtime >= :f AND unixtime <= :t',
                    'params'    => [':grid' => $arrayInsert[$i]['graphId'], ':f' => $todayFrom[$cd], ':t' => $todayTo[$cd]]/*,
					'order'    => 'unixtime DESC'*/
                ]);
                if (isset($e)) {
                    $arrayInsert[$i]['bortId'] = $e->borts_id;
                    $arrayInsert[$i]['st'] = 'Y';
                    $statusBortsOut[$e->borts_id]['statusOut'] = 'check';
                }
                if (!isset($e)) {
                    $arrayInsert[$i]['st'] = 'N';
                    //шукаемо по плану маршрут і графік
                    $planOut = Orders::model()->findAll([
                        'condition' => 't.graphs_id = :gi AND t.from = :f AND t.to = :t',
                        'params'    => [':f' => $dayToCalc[$cd], ':t' => $dayToCalc[$cd], ':gi' => $arrayInsert[$i]['graphId']]/*,
							'order'    => 't.id'*/
                    ]);
                    foreach ($planOut as $notout) {
                        $arrayInsert[$i]['bortId'] = $notout->borts_id;
                        $statusBortsOut[$notout->borts_id]['statusOut'] = 'check';
                    }
                }
            }
            //добавляємо борти без наряду
            foreach ($statusBortsOut as $bortId => $value) {
                if (isset($value['statusOut']) && ($value['statusOut'] != 'check') && ($value['st'] == 'yes')) {
                    $arrayInsert[] = [
                        'bortId'      => $value['bortId'],
                        'parks_id'    => $value['parks_id'],
                        'carriers_id' => $value['carriers_id'],
                        'st'          => 'N',
                    ];
                }
            }
            //print_r($arrayInsert);
            //видаляємо дані за день
            ReportOutingGraphs::model()->deleteAll([
                'condition' => 'date = :d',
                'params'    => [':d' => $dayToCalc[$cd]],
            ]);
            $countAllInsert = count($arrayInsert);
            for ($i = 0; $i < $countAllInsert; $i++) {
                $q = new ReportOutingGraphs;
                $q->date = $dayToCalc[$cd];
                $q->borts_id = $arrayInsert[$i]['bortId'] ?? '';
                $q->routes_id = $arrayInsert[$i]['routes_id'] ?? '';
                $q->graphs_id = $arrayInsert[$i]['graphId'] ?? '';
                $q->outing_status = $arrayInsert[$i]['st'] ?? '';
                $q->parks_id = isset($arrayInsert[$i]['bortId']) ? $arrayBorts[$arrayInsert[$i]['bortId']]['parks_id'] : '';
                $q->carriers_id = $arrayInsert[$i]['carriers_id'] ?? '';
                $q->save();
            }
            /*	$countAllInsert=count($arrayInsert);
                for ($i=0; $i < $countAllInsert ; $i++) {
                    if ($i<$countAllGraphs) {
                        $rogf=ReportOutingGraphs::model()->findByAttributes(array('graphs_id' => $arrayInsert[$i]['graphId'],'date' => $dayToCalc[$cd]));
                        if (isset($rogf)) {
                            $rogf->borts_id=$arrayInsert[$i]['bortId'];
                            $rogf->parks_id=$arrayBorts[$arrayInsert[$i]['bortId']]['parks_id'];
                            $rogf->carriers_id=$arrayInsert[$i]['carriers_id'];
                            $rogf->outing_status=$arrayInsert[$i]['st'];
                            $rogf->routes_id=$arrayInsert[$i]['routes_id'];
                            $rogf->save();
                        }
                        if (!isset($rogf)) {
                            $q= new ReportOutingGraphs;
                            $q->date=$dayToCalc[$cd];
                            $q->borts_id=$arrayInsert[$i]['bortId'];
                            $q->routes_id=$arrayInsert[$i]['routes_id'];
                            $q->graphs_id=$arrayInsert[$i]['graphId'];
                            $q->outing_status=$arrayInsert[$i]['st'];
                            $q->parks_id=$arrayBorts[$arrayInsert[$i]['bortId']]['parks_id'];
                            $q->carriers_id=$arrayInsert[$i]['carriers_id'];
                            $q->save();
                        }
                    }
                    else {
                        $rogf2=ReportOutingGraphs::model()->findByAttributes(array('borts_id' => $arrayInsert[$i]['bortId'],'date' => $dayToCalc[$cd]));
                        if (isset($rogf2)) {
                            $rogf2->parks_id=$arrayBorts[$arrayInsert[$i]['bortId']]['parks_id'];
                            $rogf2->carriers_id=$arrayInsert[$i]['carriers_id'];
                            $rogf2->outing_status=$arrayInsert[$i]['st'];
                            $rogf2->save();
                        }
                        if (!isset($rogf2)) {
                            $q= new ReportOutingGraphs;
                            $q->date=$dayToCalc[$cd];
                            $q->borts_id=$arrayInsert[$i]['bortId'];
                            $q->routes_id=$arrayInsert[$i]['routes_id'];
                            $q->graphs_id=$arrayInsert[$i]['graphId'];
                            $q->outing_status=$arrayInsert[$i]['st'];
                            $q->parks_id=$arrayBorts[$arrayInsert[$i]['bortId']]['parks_id'];
                            $q->carriers_id=$arrayInsert[$i]['carriers_id'];
                            $q->save();
                        }
                    }
                }*/
            unset($arrayInsert);
            unset($statusBortsOut);
            unset($e);

            $success[$cd] = 'Y';
            $endTimeReport[$cd] = time();
            $cdPlusOne = $cd + 1;
            $message[$cd] = "calc report on day " . $dayToCalc[$cd] . " " . $cdPlusOne . " from " . $countDate;
            $newRecordReport = new ExecutionsCommands;
            $newRecordReport->date = date("Y-m-d");
            $newRecordReport->commands_id = 6;
            $newRecordReport->start_time = $startTimeReport[$cd];
            $newRecordReport->end_time = $endTimeReport[$cd];
            $newRecordReport->duration = $endTimeReport[$cd] - $startTimeReport[$cd];
            $newRecordReport->success = $success[$cd];
            $newRecordReport->comment = $message[$cd];
            $newRecordReport->save();
        }//for ($cd=0; $cd < $countDate; $cd++) {
    }
}