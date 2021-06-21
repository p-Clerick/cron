<?php

class ExportController extends Controller
{
	public function actionCreate () {
		if( Yii::app()->user->checkAccess('exportReport') ){
		
		error_reporting(E_ALL);
		set_time_limit(10);

		$_SESSION['excel_data']		 = json_decode($_REQUEST['data'], true);
		$_SESSION['excel_colheader'] = json_decode($_REQUEST['colheader'], true);
		$_SESSION['header_text']	 = json_decode($_REQUEST['header_text'], true);
		$_SESSION['excel_zvit_type'] = json_decode($_REQUEST['type'], true); 
		$_SESSION['t_rizn_limit']	 = json_decode($_REQUEST['t_rizn_limit'], true);

		echo '{success: true}';
		}
	}
	
	public function actionRead () {
		if( Yii::app()->user->checkAccess('exportReport') ){
			
			$data           = $_SESSION['excel_data'];
			$colheader      = $_SESSION['excel_colheader'];
			$header_text    = $_SESSION['header_text'];
			$zvit_type      = $_SESSION['excel_zvit_type'];
			$t_rizn_limit   = $_SESSION['t_rizn_limit']; 
			
			$filename = iconv('UTF-8', 'CP1251',$header_text['title'].Yii::app()->session['PeriodOf'].$header_text['start_date'].Yii::app()->session['ToText'].$header_text['end_date']). ".xls";

			function xlsBOF(){
				echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
				return;
			}
			function xlsEOF(){
				echo pack("ss", 0x0A, 0x00);
				return;
			}
			function WriteNumber($Row, $Col, $Value){
				echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
				echo pack("d", $Value);
				return;
			}
			function WriteLabel($Row, $Col, $Value ){
				$L = strlen($Value);
				echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
				echo $Value;
				return;
			}
			function xlsCodepage($codepage) {
				$record	= 0x0042;	// Codepage Record identifier
				$length	= 0x0002;	// Number of bytes to follow
				$header	= pack('vv', $record, $length);
				$data	  = pack('v',  $codepage);
				echo $header , $data;
			}
		
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment; filename=\"$filename\""); 
			header("Content-Transfer-Encoding: binary ");
			xlsBOF();
			xlsCodepage(1251);
			$first = 0;
			WriteLabel($first, 0,  iconv('UTF-8', 'CP1251',$header_text['title']));
			WriteLabel($first, 1, "");
			WriteLabel($first, 2, "");
			WriteLabel($first, 3, "");
			++$first;
			if(isset($header_text['time'])){
				WriteLabel($first, 0, iconv('UTF-8', 'CP1251',Yii::app()->session['DataText']));
				WriteLabel($first, 1, iconv('UTF-8', 'CP1251',$header_text['date']));
				++$first;
				WriteLabel($first, 0, iconv('UTF-8', 'CP1251',Yii::app()->session['TimeText']));
				WriteLabel($first, 1, iconv('UTF-8', 'CP1251',$header_text['time']));
				++$first;
			} else {
				if($header_text['start_date'] === $header_text['end_date']){
					WriteLabel($first, 0, iconv('UTF-8', 'CP1251',Yii::app()->session['DataText']));
					WriteLabel($first, 1, iconv('UTF-8', 'CP1251',$header_text['start_date']));
					++$first;
				} else{
					WriteLabel($first, 0, iconv('UTF-8', 'CP1251',Yii::app()->session['PeriodOf'].$header_text['start_date'].Yii::app()->session['ToText'].$header_text['end_date']));
					WriteLabel($first, 1, "");
					WriteLabel($first, 2, "");
					++$first;
				}
				WriteLabel($first, 0, iconv('UTF-8', 'CP1251',Yii::app()->session['TypeOfTZ']));
				WriteLabel($first, 1, iconv('UTF-8', 'CP1251',$header_text['type']));
				++$first;
				
				if(isset($header_text['race'])){
					WriteLabel($first, 0, iconv('UTF-8', 'CP1251',Yii::app()->session['RouteText']));
					WriteLabel($first, 1, iconv('UTF-8', 'CP1251',$header_text['race']));
					++$first;
				}
				if(isset($header_text['graf'])){
					WriteLabel($first, 0, iconv('UTF-8', 'CP1251',Yii::app()->session['GrafikText']));
					WriteLabel($first, 1, iconv('UTF-8', 'CP1251',$header_text['graf']));
					++$first;
				}
			}
			$i = 0;
			++$first;
			foreach($colheader as $header) {
				WriteLabel($first, $i,  iconv('UTF-8', 'CP1251',$header['colheader']));
				$j = $first+1;
				foreach($data as $value){
					if($zvit_type == "vidhyl"){ 
						if($value['t_rizn'] > $t_rizn_limit){
							WriteLabel($j, $i, iconv('UTF-8', 'CP1251',$value[$header['dataindex']]));
						}
						else{
							WriteLabel($j, $i, iconv('UTF-8', 'CP1251',$value[$header['dataindex']]));
						}
					} else if($zvit_type == 'speed'){
						if($value['spl_count'] > 0){
							WriteLabel($j, $i, iconv('UTF-8', 'CP1251',$value[$header['dataindex']]));
						}
						else{
							WriteLabel($j, $i, iconv('UTF-8', 'CP1251',$value[$header['dataindex']]));
						}
					}
					else{
						WriteLabel($j, $i, iconv('UTF-8', 'CP1251',$value[$header['dataindex']]));
					}
					++$j;
				}
				++$i;
			}
			xlsEOF();
		} else {
			echo 'Not permitted';
		}
	}
}
