<?php

Yii::import('application.models.*');

class NoticeCommand extends CConsoleCommand
{

	public function run($args)
	{
        $constTimeDiff = 900;
        $now = new DateTime('now');
        $nowTimestamp = $now->getTimestamp();
        $noticeType = 4;
        $noticeStatus = 1;
        $createNotice = false;
        $todayMoveOnMapList =  MoveOnMap::model()->findAll(array(
            'condition' => 'date(t.datatime) = CURDATE()',
            'order' => 'borts_id'
        ));
        $todayNoticeList = NoticeAllHistory::model()->findAll(array(
            'condition' => 'notice_types =:nt and notice_statuses =:ns and date_from = CURDATE()',
            'params' => array(':nt' => $noticeType, ':ns' => $noticeStatus)
        ));
        $countTodayNoticeList = count($todayNoticeList);
        foreach ($todayMoveOnMapList as $todayMoveOnMapRecord){
            $bortDatetime = new DateTime($todayMoveOnMapRecord->datatime);
            $bortTimestamp = $bortDatetime->getTimestamp();
            $timestampDiff = $nowTimestamp - $bortTimestamp;
            if($timestampDiff > $constTimeDiff){
                $createNotice = true;
            }
            else{
                $createNotice = false;
            }

            if ($createNotice){
                if($countTodayNoticeList == 0){
                    //create notice
                    $notice = new NoticeAllHistory();
                    $notice->notice_types = 4;
                    $notice->date_from = $bortDatetime->format('Y-m-d');
                    $notice->time_from = $bortDatetime->format('H:i:s');
                    $notice->value = $timestampDiff;
                    $notice->borts_id = $todayMoveOnMapRecord->borts_id;
                    $notice->routes_id = $todayMoveOnMapRecord->routes_id;
                    $notice->graphs_id = $todayMoveOnMapRecord->graphs_id;
                    $notice->notice_statuses = 1;
                    $notice->save();
                }
                else {
                    $i = 1;
                    foreach ($todayNoticeList as $todayNoticeRecord) {
                        if ($todayMoveOnMapRecord->borts_id === $todayNoticeRecord->borts_id) {
                            $notice = NoticeAllHistory::model()->find(array(
                                'condition' => 't.borts_id=:bid and date_from= CURDATE() and notice_types =:nt',
                                'params' => array(':bid' => $todayNoticeRecord->borts_id, ':nt' => $noticeType)
                            ));
                            $notice->value = $timestampDiff;
                            $notice->save();
                            // update notice
                            $noticeUpdate = true;
                        }
                        if(($i === $countTodayNoticeList) and !isset($noticeUpdate)) {
                            //create notice
                            $notice = new NoticeAllHistory();
                            $notice->notice_types = 4;
                            $notice->date_from = $bortDatetime->format('Y-m-d');
                            $notice->time_from = $bortDatetime->format('H:i:s');
                            $notice->value = $timestampDiff;
                            $notice->borts_id = $todayMoveOnMapRecord->borts_id;
                            $notice->routes_id = $todayMoveOnMapRecord->routes_id;
                            $notice->graphs_id = $todayMoveOnMapRecord->graphs_id;
                            $notice->notice_statuses = 1;
                            $notice->save();
                            $i=1;
                            $noticeUpdate = false;
                        }
                        $i++;
                    }
                }
            }
            else{
                foreach ($todayNoticeList as $todayNoticeRecord) {
                    if ($todayMoveOnMapRecord->borts_id === $todayNoticeRecord->borts_id) {
                        //close notice
                        $notice = NoticeAllHistory::model()->find(array(
                            'condition' => 't.borts_id=:bid and date_from= CURDATE() and notice_types =:nt',
                            'params' => array(':bid' => $todayNoticeRecord->borts_id, ':nt' => $noticeType)
                        ));
                        $notice->notice_statuses = 3;
                        $notice->date_to = $now->format('Y-m-d');
                        $notice->time_to = $now->format('H:i:s');
                        $notice->save();

                    } else {
                        //nothing do
                    }
                }
            }
        }
	}
}