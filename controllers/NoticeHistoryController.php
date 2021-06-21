<?php
class NoticeHistoryController extends Controller
{
	public $result = array(
		'success'=>"true",
		'data'=>array()
	);	
    public function actionRead(){
		$this->result['total'] = NoticeHistory::model()->count(array(
			'condition'=>'date(from_unixtime(unixtime_of_receive))=:dt',
	        'params'=>array(':dt' => substr($_GET['notice_history_date'],0,10))));
        $notices = NoticeHistory::model()->findAll(array(
			'condition'=>'date(from_unixtime(unixtime_of_receive))=:dt',
	        'params'=>array(':dt' => substr($_GET['notice_history_date'],0,10)),        	
        	'order' => 't.id', 
        	'limit' => $_GET['limit'],
        	'offset' => $_GET['start']));    	
        foreach($notices as $notice){
        	if (isset($notice->notice_response)){
				$this->result['data'][] = array(
					'id'			=> $notice->id,
					'notice_header' 		=> $notice->notice->header,
					'notice_type_name'		=> $notice->notice->notice_type->name,
					'notice_response_value'	=> $notice->notice_response->value,
					'receive_time'			=> gmdate('Y-m-d H:i:s', $notice->unixtime_of_receive),
					'create_time'			=> gmdate('Y-m-d H:i:s', $notice->unixtime_of_create),
					'bort_number'			=> $notice->bort->number,
					'state_number'			=> $notice->bort->state_number,
					'latitude'				=> $notice->latitude,
					'longitude'				=> $notice->longitude
				);
			}
			else if (isset($notice->notifications_responses_value)){
				$this->result['data'][] = array(
					'id'			=> $notice->id,
					'notice_header' 		=> $notice->notice->header,
					'notice_type_name'		=> $notice->notice->notice_type->name,
					'notice_response_value'	=> $notice->notifications_responses_value,
					'receive_time'			=> gmdate('Y-m-d H:i:s', $notice->unixtime_of_receive),
					'create_time'			=> gmdate('Y-m-d H:i:s', $notice->unixtime_of_create),
					'bort_number'			=> $notice->bort->number,
					'state_number'			=> $notice->bort->state_number,
					'latitude'				=> $notice->latitude,
					'longitude'				=> $notice->longitude
				);				

			}
        }
        echo CJSON::encode($this->result);
	}
}
?>
