<?php
class ReportEndStopsCarriersController extends CController  {
	public function actionRead()//на посилання з гет
	{
        function getAverageValueFromArrayByIndex($array,$countRows,$index){
            $tempVariable = 0;
            for ($i=0; $i<=$countRows;$i++){
                if($i == $countRows){
                    $tempVariable = $tempVariable/$countRows;
                    return round($tempVariable,2);
                }
                if (isset($array[$i][$index])) {
                    $tempVariable = $tempVariable + $array[$i][$index];
                }
            }
        }
        function getSummaryValuesFromArrayByIndex($array,$countRows,$index){
            $tempVariable = 0;
            for ($i=0; $i<=$countRows;$i++){
                if($i == $countRows){
                    return $tempVariable;
                }
                if (isset($array[$i][$index])) {
                    $tempVariable = $tempVariable + $array[$i][$index];
                }
            }
        }
		$fromDate=Yii::app()->request->getParam('fromDate');
		$toDate=Yii::app()->request->getParam('toDate');
		$caridSelect=Yii::app()->request->getParam('carid');
		$r=Route::model()->with('carrier')->findAll();
		foreach ($r as $k) {
			if ($k->carriers_id!=3) {
				if ($k->carriers_id!=10) {
					if ($k->carriers_id!=0) {
                        $arrScheds[$k->carriers_id] = '';
						$carriers[$k->carriers_id]=$k->carrier->name;
						$arrayRoutes[$k->id]=$k->carriers_id;
						$arrScheds[$k->carriers_id] = $arrScheds[$k->carriers_id]." ".$k->name;
					}
				}	
			}
		}
        $i = 1;
		if ($caridSelect==0) {
            $graphPerformance = ReportPercentageRoutesGraphs::model()->getGraphPerformanceGroupByCarriers($fromDate, $toDate);
            $endpointsPerformance = ReportEndStops::model()->getEndPointsPerformanceGroupByCarriers($fromDate, $toDate);
            $complianceSchedule = ReportAverageGraphs::model()->getComplianceScheduleGroupByCarriers($fromDate, $toDate);

        }
        if ($caridSelect!=0) {
            $res = 0;
            $graphPerformance = ReportPercentageRoutesGraphs::model()->getGraphPerformanceGroupByRoutesForCarrier($fromDate,$toDate,$caridSelect);
            $endpointsPerformance = ReportEndStops::model()->getEndPointsPerformanceGroupByRoutesForCarrier($fromDate, $toDate,$caridSelect);
            $complianceSchedule = ReportAverageGraphs::model()->getComplianceScheduleGroupByRoutesForCarrier($fromDate, $toDate,$caridSelect);
        }
            foreach ($graphPerformance as $graphPerformanceList) {
                if($caridSelect){
                    $index = $graphPerformanceList->routes_id;
                }
                else{
                    $index = $graphPerformanceList->carriers_id;
                }
                $reportArray[$index] = array(
                    'carid' => $graphPerformanceList->carriers_id,
                    'npp' => $i++,
                    'carrierName' => $graphPerformanceList->carrier_name,
                    'scheds' => $graphPerformanceList->routes_names,
                    'percentAll' => round($graphPerformanceList->percent_performance_graph_by_all, 2),
                    'percentPoes' => round($graphPerformanceList->percent_performance_graph_by_points, 2),
                    'countPoesFact' => $graphPerformanceList->count_points_fact,
                    'countPoesPlan' => $graphPerformanceList->count_points_plan,
                    'percentFlights' => round($graphPerformanceList->percent_performance_graph_by_flights, 2),
                    'countFlightsFact' => $graphPerformanceList->count_flights_fact,
                    'countFlightsPlan' => $graphPerformanceList->count_flights_plan,
                    'date' => $fromDate . " - " . $toDate
                );
            }
            if (isset($endpointsPerformance)) {
                foreach ($endpointsPerformance as $endpointsPerformanceList) {
                    if ($caridSelect) {
                        $index = $endpointsPerformanceList->routes_id;
                    } else {
                        $index = $endpointsPerformanceList->carriers_id;
                    }
                    $reportArray[$index]['countEndStopsNePopavFact'] =
                        $endpointsPerformanceList->count_is_not_marked_endpoints;
                    $reportArray[$index]['percentEndStops'] =
                        round(($endpointsPerformanceList->count_is_not_marked_endpoints * 100) /
                            $reportArray[$index]['countPoesPlan'], 2);
                }
            }
            foreach($complianceSchedule as $complianceScheduleList){
                if($caridSelect){
                    $index = $complianceScheduleList->routes_id;
                }
                else{
                    $index = $complianceScheduleList->carriers_id;
                }
                $reportArray[$index]['advP'] = round($complianceScheduleList->advance_percentage,2);
                $reportArray[$index]['advA'] = round($complianceScheduleList->advance_average,2);
                $reportArray[$index]['lateP'] = round($complianceScheduleList->lateness_percentage,2);
                $reportArray[$index]['lateA'] = round($complianceScheduleList->lateness_average,2);
                $reportArray[$index]['ontP'] = round($complianceScheduleList->ontime_percentage,2);
                $reportArray[$index]['ontA'] = round($complianceScheduleList->ontime_average,2);
            }
            foreach($reportArray as $index => $reportArrayList){
                $indexReportArray[] = $reportArray[$index];
            }
            $countReportArrayRows = count($indexReportArray);
            $inGeneralArray = array();
            $inGeneralArray['percentPoes'] = getAverageValueFromArrayByIndex($indexReportArray,$countReportArrayRows,'percentPoes');
            $inGeneralArray['percentAll'] = getAverageValueFromArrayByIndex($indexReportArray,$countReportArrayRows,'percentAll');
            $inGeneralArray['countPoesFact'] = getSummaryValuesFromArrayByIndex($indexReportArray,$countReportArrayRows,'countPoesFact');
            $inGeneralArray['countPoesPlan'] = getSummaryValuesFromArrayByIndex($indexReportArray,$countReportArrayRows,'countPoesPlan');
            $inGeneralArray['percentFlights'] = getAverageValueFromArrayByIndex($indexReportArray,$countReportArrayRows,'percentFlights');
            $inGeneralArray['countFlightsFact'] = getSummaryValuesFromArrayByIndex($indexReportArray,$countReportArrayRows,'countFlightsFact');
            $inGeneralArray['countFlightsPlan'] = getSummaryValuesFromArrayByIndex($indexReportArray,$countReportArrayRows,'countFlightsPlan');
            $inGeneralArray['countEndStopsNePopavFact'] = getSummaryValuesFromArrayByIndex($indexReportArray,$countReportArrayRows,'countEndStopsNePopavFact');
            $inGeneralArray['percentEndStops'] = getAverageValueFromArrayByIndex($indexReportArray,$countReportArrayRows,'percentEndStops');
            $inGeneralArray['advP'] = getAverageValueFromArrayByIndex($indexReportArray,$countReportArrayRows,'advP');
            $inGeneralArray['advA'] = getAverageValueFromArrayByIndex($indexReportArray,$countReportArrayRows,'advA');
            $inGeneralArray['lateP'] = getAverageValueFromArrayByIndex($indexReportArray,$countReportArrayRows,'lateP');
            $inGeneralArray['lateA'] = getAverageValueFromArrayByIndex($indexReportArray,$countReportArrayRows,'lateA');
            $inGeneralArray['ontP'] = getAverageValueFromArrayByIndex($indexReportArray,$countReportArrayRows,'ontP');
            $inGeneralArray['ontA'] = getAverageValueFromArrayByIndex($indexReportArray,$countReportArrayRows,'ontA');
            $inGeneralArray['carrierName'] = 'Загалом';
            $inGeneralArray['npp'] = $countReportArrayRows+1;
            $indexReportArray[$countReportArrayRows] = $inGeneralArray;
            $result = array('success' => true, 'rows'=>$indexReportArray, 'totalCount'=>$countReportArrayRows+1);
            echo CJSON::encode($result);


//-----------------------------------------------------------------------------		
		/*if ($caridSelect!=0) {

			//шукаемо процент
			$a = ReportPercentageRoutesGraphs::model()->with('route')->findAll(array(
					'condition'=> 'date >= :f AND date <= :t',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate),
					'order'    => 't.id'));
			foreach ($a as $k) {
				if ($k->route->carriers_id==$caridSelect) {
					$dataCountFlightPlanRoutes[$k->routes_id]=$dataCountFlightPlanRoutes[$k->routes_id]+$k->count_flight_plan;
					$dataCountFlightFactRoutes[$k->routes_id]=$dataCountFlightFactRoutes[$k->routes_id]+$k->count_flight_fakt;
					$dataCountPoesPlanRoutes[$k->routes_id]=$dataCountPoesPlanRoutes[$k->routes_id]+$k->count_stations_plan;
					$dataCountPoesFactRoutes[$k->routes_id]=$dataCountPoesFactRoutes[$k->routes_id]+$k->count_stations_fakt;
					$dataPercentageRealization[$k->routes_id][]=$k->percentage_realization;
					$arrayRoutesName[$k->routes_id]=$k->route->name;
				}
			}
			foreach ($dataCountFlightPlanRoutes as $key => $value) {
				$rows[]=array('carid'=>$caridSelect,'carrierName'=>$carriers[$caridSelect]." ".Yii::app()->session['RouteTextFull']." ".$arrayRoutesName[$key],'date'=>$fromDate." - ".$toDate,'scheds'=>$arrayRoutesName[$key],'routId'=>$key);
			}
			$countRows=count($rows);
			
			//кінцеві зупинки
			$es=Yii::app()->db->createCommand("SELECT routes_id,count(routes_id) from report_end_stops where date>='".$fromDate."' and date<='".$toDate."' and carriers_id='".$caridSelect."' GROUP by routes_id")->queryAll();
			foreach ($es as $key => $value) {
				$arrayEndStops[$value['routes_id']]=$value['count(routes_id)'];
			}
			$a = ReportAverageGraphs::model()->with('route')->findAll(array(
					'condition'=> 'date >= :f AND date <= :t',
					'params'   =>array(':f'=>$fromDate, ':t'=>$toDate),
					'order'    => 't.id'));
				foreach ($a as $k) {
					if ($k->route->carriers_id==$caridSelect) {
						$dateOTP[$k->routes_id][]=$k->ontime_percentage;
						$dateOTA[$k->routes_id][]=$k->ontime_average;
						$dateLP[$k->routes_id][]=$k->lateness_percentage;
						$dateLA[$k->routes_id][]=$k->lateness_average;
						$dateAP[$k->routes_id][]=$k->advance_percentage;
						$dateAA[$k->routes_id][]=$k->advance_average;
					}
				}
				foreach ($dateOTP as $routeId => $value) {
					$countRecord=count($value);
					$sumRecord=array_sum($value);
					$percent=round($sumRecord/$countRecord,2);
					$rowsData[$routeId]['ontP']=$percent;
				}
				foreach ($dateOTA as $routeId => $value) {
					$countRecord=count($value);
					$sumRecord=array_sum($value);
					foreach ($value as $key => $value1) {
						if ($value1==null) {
							$nnn[$routeId]=$nnn[$routeId]+1;
						}
					}
					if ($nnn[$routeId]==$countRecord) {
						$percent=round($sumRecord/($countRecord),2);
					}
					if ($nnn[$routeId]!=$countRecord) {
						$percent=round($sumRecord/($countRecord-$nnn[$routeId]),2);
					}
					$rowsData[$routeId]['ontA']=$percent;
				}
				foreach ($dateLA as $routeId => $value) {
					$countRecord=count($value);
					$sumRecord=array_sum($value);
					foreach ($value as $key => $value1) {
						if ($value1==null) {
							$nn[$routeId]=$nn[$routeId]+1;
						}
					}
					if ($nn[$routeId]==$countRecord) {
						$percent=round($sumRecord/($countRecord),2);
					}
					if ($nn[$routeId]!=$countRecord) {
						$percent=round($sumRecord/($countRecord-$nn[$routeId]),2);
					}
					$rowsData[$routeId]['lateA']=$percent;
				}
				foreach ($dateLP as $routeId => $value) {
					$countRecord=count($value);
					$sumRecord=array_sum($value);
					$percent=round($sumRecord/$countRecord,2);
					$rowsData[$routeId]['lateP']=$percent;
				}
				foreach ($dateAA as $routeId => $value) {
					$countRecord=count($value);
					$sumRecord=array_sum($value);
					foreach ($value as $key => $value1) {
						if ($value1==null) {
							$n[$routeId]=$n[$routeId]+1;
						}
					}
					if ($n[$routeId]==$countRecord) {
						$percent=round($sumRecord/($countRecord),2);
					}
					if ($n[$routeId]!=$countRecord) {
						$percent=round($sumRecord/($countRecord-$n[$routeId]),2);
					}
					$rowsData[$routeId]['advA']=$percent;
				}
				foreach ($dateAP as $routeId => $value) {
					$rowsData[$routeId]['advP']=100-$rowsData[$routeId]['lateP']-$rowsData[$routeId]['ontP'];
				}
			for ($i=0; $i <$countRows ; $i++) { 
				$rows[$i]['countFlightsPlan']=$dataCountFlightPlanRoutes[$rows[$i]['routId']];
				$rows[$i]['countFlightsFact']=$dataCountFlightFactRoutes[$rows[$i]['routId']];
				if ($rows[$i]['countFlightsPlan']!=0) {
					$rows[$i]['percentFlights']=round($rows[$i]['countFlightsFact']/$rows[$i]['countFlightsPlan']*100,2);
				}
				
				$rows[$i]['countPoesPlan']=$dataCountPoesPlanRoutes[$rows[$i]['routId']];
				$rows[$i]['countPoesFact']=$dataCountPoesFactRoutes[$rows[$i]['routId']];
				if ($rows[$i]['countPoesPlan']!=0) {
					$rows[$i]['percentPoes']=round($rows[$i]['countPoesFact']/$rows[$i]['countPoesPlan']*100,2);
				}
				if (count($dataPercentageRealization[$rows[$i]['routId']])!=0) {
					$rows[$i]['percentAll']=round(array_sum($dataPercentageRealization[$rows[$i]['routId']])/count($dataPercentageRealization[$rows[$i]['routId']]),2);
					$sumPersAll[$i]=array_sum($dataPercentageRealization[$rows[$i]['routId']]);
					$countSumPercAll[$i]=count($dataPercentageRealization[$rows[$i]['routId']]);
				}
				$rows[$i]['countEndStopsNePopavFact']=$arrayEndStops[$rows[$i]['routId']];
				if ($rows[$i]['countPoesPlan']!=0) {
					$rows[$i]['percentEndStops']=round($rows[$i]['countEndStopsNePopavFact']/$rows[$i]['countPoesPlan']*100,2);
				}

				$rows[$i]['ontP']=$rowsData[$rows[$i]['routId']]['ontP'];
				$rows[$i]['ontA']=$rowsData[$rows[$i]['routId']]['ontA'];
				$rows[$i]['lateP']=$rowsData[$rows[$i]['routId']]['lateP'];
				$rows[$i]['lateA']=$rowsData[$rows[$i]['routId']]['lateA'];
				$rows[$i]['advP']=$rowsData[$rows[$i]['routId']]['advP'];
				$rows[$i]['advA']=$rowsData[$rows[$i]['routId']]['advA'];

				//загалом
				$All['percentAll']=$All['percentAll']+$rows[$i]['percentAll'];
				$All['percentPoes']=$All['percentPoes']+$rows[$i]['percentPoes'];
				$All['countPoesPlan']=$All['countPoesPlan']+$rows[$i]['countPoesPlan'];
				$All['countPoesFact']=$All['countPoesFact']+$rows[$i]['countPoesFact'];
				$All['percentFlights']=$All['percentFlights']+$rows[$i]['percentFlights'];
				$All['countFlightsPlan']=$All['countFlightsPlan']+$rows[$i]['countFlightsPlan'];
				$All['countFlightsFact']=$All['countFlightsFact']+$rows[$i]['countFlightsFact'];
				$All['percentEndStops']=$All['percentEndStops']+$rows[$i]['percentEndStops'];
				$All['countEndStopsNePopavFact']=$All['countEndStopsNePopavFact']+$rows[$i]['countEndStopsNePopavFact'];
				$All['ontP']=$All['ontP']+$rows[$i]['ontP'];
				$All['ontA']=$All['ontA']+$rows[$i]['ontA'];
				$All['lateP']=$All['lateP']+$rows[$i]['lateP'];
				$All['lateA']=$All['lateA']+$rows[$i]['lateA'];
				$All['advP']=$All['advP']+$rows[$i]['advP'];
				$All['advA']=$All['advA']+$rows[$i]['advA'];
			}
			function sortByPercent ($a,$b) {
				if ($a['percentAll']==$b['percentAll']) {
					return 0;
				}
				if ($a['percentAll']>$b['percentAll']) {
					return -1;
				}
				if ($a['percentAll']<$b['percentAll']) {
					return 1;
				}
			}
			if (isset($rows)) {
				usort($rows, "sortByPercent");
			}
			for ($i=0; $i <$countRows ; $i++) { 
				$rows[$i]['npp']=$i+1;
			}
			$rows[$countRows]['carrierName']=Yii::app()->session['InGeneral'];
			$rows[$countRows]['npp']=$countRows+1;
			//$rows[$countRows]['percentAll']=round($All['percentAll']/$countRows,2);
			$rows[$countRows]['percentAll']=round(array_sum($sumPersAll)/array_sum($countSumPercAll),2);
			$rows[$countRows]['percentPoes']=round($All['countPoesFact']/$All['countPoesPlan']*100,2);
			$rows[$countRows]['countPoesPlan']=$All['countPoesPlan'];
			$rows[$countRows]['countPoesFact']=$All['countPoesFact'];
			$rows[$countRows]['percentFlights']=round($All['countFlightsFact']/$All['countFlightsPlan']*100,2);
			$rows[$countRows]['countFlightsPlan']=$All['countFlightsPlan'];
			$rows[$countRows]['countFlightsFact']=$All['countFlightsFact'];
			$rows[$countRows]['percentEndStops']=round($All['countEndStopsNePopavFact']/$rows[$countRows]['countPoesPlan']*100,2);
			$rows[$countRows]['countEndStopsNePopavFact']=$All['countEndStopsNePopavFact'];
			$rows[$countRows]['ontP']=round($All['ontP']/$countRows,2);
			$rows[$countRows]['lateP']=round($All['lateP']/$countRows,2);
			$rows[$countRows]['advP']=round($All['advP']/$countRows,2);
			$rows[$countRows]['ontA']=round($All['ontA']/$countRows,2);
			$rows[$countRows]['lateA']=round($All['lateA']/$countRows,2);
			$rows[$countRows]['advA']=round($All['advA']/$countRows,2);
			if(Yii::app()->user->name != "guest"){
		        $carrier = Yii::app()->user->checkUser(Yii::app()->user);
		    }
		    //print_r($carrier);
		    if ($carrier) {
		    	for ($i=0; $i <$countRows ; $i++) { 
		    		if ($rows[$i]['carid']==$carrier['carrier_id']) {
		    			$rowCarrier[]=$rows[$i];
		    		}
		    	}
		    	$result = array('success' => true, 'rows'=>$rowCarrier , 'totalCount'=>1); 
				echo CJSON::encode($result);
		    }
		    else {
		    	$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows+1); 
				echo CJSON::encode($result);
		    }
		}*/
	}
}
?>	