<?php
Yii::import('application.models.*');
class ClientSendLoadCommand extends CConsoleCommand
{
	public function run($days) {

/*		$bort=Borts::model()->findAll();
		foreach ($bort as $k) {
			$arrayBorts[]=$k->id;
		}
*/		//print_r($arrayBorts);
		/*
		//-------------------------mount o remount------------------------------------------	
		foreach ($arrayBorts as $key => $value) {
			$commandsInsert=new ClientSendCommandsLoad;
			$commandsInsert->borts_id=$value;
			$commandsInsert->client_commands='console';
			$commandsInsert->client_send_commands_history_id=0;
			$commandsInsert->params='mount -o remount,rw -t yaffs2 /dev/block/mtdblock11  /system; busybox rm /system/app/Music.apk; busybox rm /system/app/Music.odex; busybox rm /data/app/Music.apk;';
			$commandsInsert->save();
		}

		*/ //-------------------------eo mount o remount------------------------------	

/*
		//------------------------------cat /data/clock_autumn.xml > /data/data/ru.org.amip.ClockSync/shared_prefs/ru.org.amip.ClockSync_preferences.xml;------------------------------------------------------------------------------------------------------------------------	
		foreach ($arrayBorts as $key => $value) {
			$commandsInsert=new ClientSendCommandsLoad;
			$commandsInsert->borts_id=$value;
			$commandsInsert->client_commands='console';
			$commandsInsert->client_send_commands_history_id=0;
			$commandsInsert->params='cat /data/clock_autumn.xml > /data/data/ru.org.amip.ClockSync/shared_prefs/ru.org.amip.ClockSync_preferences.xml;';
			$commandsInsert->save();
		}//---------------------------------------eo // cat /data/clock_autumn.xml > /data/data/ru.org.amip.ClockSync/shared_prefs/ru.org.amip.ClockSync_preferences.xml;	-----------------------------------------------------------------------------------------------------------------------------------

*/

		//-----змына команди на ыншу---------------
	/*	$new=ClientSendCommandsLoad::model()->findAll();
		foreach ($new as $k) {
			$k->params='cat /data/clock_autumn.xml > /data/data/ru.org.amip.ClockSync/shared_prefs/ru.org.amip.ClockSync_preferences.xml; reboot;';
			$k->save();
		}*/
    }
}
?>