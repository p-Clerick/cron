<?php
class ProblemsMoveOnLineCatalogController extends Controller  
{
	public function actionRead()//на посилання з гет
	{
		/*function getaddress($lat,$lng)
		{
			$url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&sensor=false';
			$json = @file_get_contents($url);
			$data=json_decode($json);
			$status = $data->status;
			if($status=="OK")
			return $data->results[0]->formatted_address;
			else
			return false;
			}
			$lat= 50.74558; //latitude
			$lng= 25.336876; //longitude
			$address= getaddress($lat,$lng);
			if($address)
			{
			echo $address;
			}
			else
			{
			echo "Not found";
		}*/
		$rows[0]=array(
			'npp'=>1,
	        'typeProblems'=>Yii::app()->session['OnboardDevice'], 
	        'descriptionProblems'=>Yii::app()->session['OnboardDeviceDescription'], 
	        'exampleProblems'=> Yii::app()->session['OnboardDeviceExampleProblems']
		);
		$rows[1]=array(
			'npp'=>2,
	        'typeProblems'=>Yii::app()->session['Clock'], 
	        'descriptionProblems'=>Yii::app()->session['ClockDescription'], 
	        'exampleProblems'=> Yii::app()->session['ClockExampleProblems']
		);
		$rows[2]=array(
			'npp'=>3,
	        'typeProblems'=>Yii::app()->session['ComplianceSchedule'], 
	        'descriptionProblems'=>Yii::app()->session['ComplianceScheduleDescription'], 
	        'exampleProblems'=> Yii::app()->session['ComplianceScheduleExampleProblems']
		);
		$rows[3]=array(
			'npp'=>4,
	        'typeProblems'=>Yii::app()->session['Communication'], 
	        'descriptionProblems'=>Yii::app()->session['CommunicationDescription'], 
	        'exampleProblems'=> Yii::app()->session['CommunicationExampleProblems']
		);
		$rows[4]=array(
			'npp'=>5,
	        'typeProblems'=>Yii::app()->session['Map'], 
	        'descriptionProblems'=>Yii::app()->session['MapDescription1'], 
	        'exampleProblems'=> Yii::app()->session['MapExampleProblems1']
		);
		$rows[5]=array(
			'npp'=>6,
	        'typeProblems'=>Yii::app()->session['Map'], 
	        'descriptionProblems'=>Yii::app()->session['MapDescription2'], 
	        'exampleProblems'=> Yii::app()->session['MapExampleProblems5']
		);
		$rows[6]=array(
			'npp'=>7,
	        'typeProblems'=>Yii::app()->session['Map'], 
	        'descriptionProblems'=>Yii::app()->session['MapDescription2'],  
	        'exampleProblems'=> Yii::app()->session['MapExampleProblems4']
		);
		$rows[7]=array(
			'npp'=>8,
	        'typeProblems'=>Yii::app()->session['Map'], 
	        'descriptionProblems'=>Yii::app()->session['MapDescription3'], 
	        'exampleProblems'=> Yii::app()->session['MapExampleProblems3']
		);
		$rows[8]=array(
			'npp'=>9,
	        'typeProblems'=>Yii::app()->session['Order'], 
	        'descriptionProblems'=>Yii::app()->session['OrderDescription1'], 
	        'exampleProblems'=> Yii::app()->session['OrderExampleProblems1']
		);
		$rows[9]=array(
			'npp'=>10,
	        'typeProblems'=>Yii::app()->session['Order'], 
	        'descriptionProblems'=>Yii::app()->session['OrderDescription2'], 
	        'exampleProblems'=> Yii::app()->session['OrderExampleProblems2']
		);
		$rows[10]=array(
			'npp'=>11,
	        'typeProblems'=>Yii::app()->session['Order'], 
	        'descriptionProblems'=>Yii::app()->session['OrderDescription3'], 
	        'exampleProblems'=> Yii::app()->session['OrderExampleProblems3']
		);
		$rows[11]=array(
			'npp'=>12,
	        'typeProblems'=>Yii::app()->session['Order'], 
	        'descriptionProblems'=>Yii::app()->session['OrderDescription4'], 
	        'exampleProblems'=> Yii::app()->session['OrderExampleProblems4']
		);
		$rows[12]=array(
			'npp'=>13,
	        'typeProblems'=>Yii::app()->session['Order'], 
	        'descriptionProblems'=>Yii::app()->session['OrderDescription5'], 
	        'exampleProblems'=> Yii::app()->session['OrderExampleProblems5']
		);
		$rows[13]=array(
			'npp'=>14,
	        'typeProblems'=>Yii::app()->session['SpeedLevel'], 
	        'descriptionProblems'=>Yii::app()->session['MoveOnMapProblemsSpeedText'], 
	        'exampleProblems'=> Yii::app()->session['MoveOnMapProblemsSpeedText'].": 72 ".Yii::app()->session['KmPerHoursText']
		);
		
		$countRows=count($rows);		
		$result = array('success' => true, 'rows'=>$rows, 'totalCount'=>$countRows);
		echo CJSON::encode($result);
	}
}	