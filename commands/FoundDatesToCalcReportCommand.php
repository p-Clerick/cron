<?php
Yii::import('application.models.*');
class FoundDatesToCalcReportCommand extends CConsoleCommand {
//	public function run() {
	public function run($args) {
	error_log(print_r($args));
		$lastLocationsFlightId=Yii::app()->db->createCommand("SELECT max(locations_flights_id_to) from days_to_calc_report")->queryScalar();
error_log('lastLocationsFlightId: '.$lastLocationsFlightId);
		$toId=Yii::app()->db->createCommand("SELECT max(id) from locations_in_flights")->queryScalar();
error_log('toId: '.$toId);
		$findDate = Yii::app()->db->createCommand("SELECT unixtime from locations_in_flights where id>".$lastLocationsFlightId." order by unixtime DESC")->queryAll();
error_log(print_r($findDate));
		foreach ($findDate as $key => $value) {
error_log('findDate: '.$value);
			$date=date("Y-m-d",$value['unixtime']);
			if ($date<date("Y-m-d",strtotime('today'))) {
				if ($date>date("Y-m-d", strtotime("-90 days"))) {
					$foundDatas[$date]=$date;
				}
				
			}
		}
		
		$r=0;
		foreach ($foundDatas as $key => $value) {
			if ($r==0) {$days=$key;}
			else {$days=$days.",".$key;}
			$r=$r+1;
		}
		//вставляэмо даны
		$c = new DaysToReport;
		$c->date=date("Y-m-d",strtotime('today'));
		$c->locations_flights_id_from=$lastLocationsFlightId+1;
		$c->locations_flights_id_to=$toId;
		$c->found_days=$days;
		$c->save();	
    }
}
?>
