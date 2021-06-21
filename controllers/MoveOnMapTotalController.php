<?php

class MoveOnMapTotalController extends Controller
{

  	   public function actionRead(){
          
            $tree_class = new Tree;
            //$cache_time = 86400;

            // Автобуси
            if($_GET['level'] === '1'){

              // Кешування даних
              /*$cache_name = md5($_GET['level'].'-'.implode ("-",json_decode($_GET["routes"])).$_GET['data_begin'].'-'.$_GET['data_end']);

              if (Yii::app()->cache->get($cache_name)) { echo CJSON::encode(Yii::app()->cache->get($cache_name)); } else {*/
              
              $result = array('days' => array());
              $shedules = array(); // id'и графіків руху
              $j = 0; // Індекс дня

              foreach(json_decode($_GET["routes"]) as $route){
                $shedules = array();
                foreach ($tree_class->getChildNodes(2, $route) as $node => $value) {
                  $shedules[] = $value['id'];
                }
                $in .= ",".implode (",", $shedules);
                $in = trim($in,",");
              }

              $days = new DatePeriod(new DateTime($_GET['data_begin']), DateInterval::createFromDateString('1 day'), new DateTime($_GET['data_end']." +1 day"));

              // Цикл на вибір даних з кожного дня для заданого проміжутку часу
              foreach ( $days as $day ){
                $t_diff = 0; // Відхиленн від графіка
                $do = true;
                $offset = 0;
                $limit = 10000;
                $arr = array();
                $difference = array();
                $gschedules = array();

                while ($do){
                $data = MoveOnMapTotal::model()
                  ->with('bort','schedule')
                  ->findAll(array(
                  'condition'=>'date(datatime) = "'.$day->format( "Y-m-d" ).'" AND schedule.graphs_id IN ('.$in.')',
                  'limit' => $limit,
                  'offset'=> $offset,
                  'order' => 't.id')
                   );

                if ($data) { // Якщо були вибрані хоч якісь дані

                // Обробка отриманих даних
                foreach($data as $element){

                  // Завантаження даних про різницю в часі
                  if (!in_array($element->schedule->moveonscheduletotal->schedules_id, $gschedules)){
                    $gschedules[] = $element->schedule->moveonscheduletotal->schedules_id;

                    $difference_array = Yii::app()->db->createCommand()
                        ->select('time_difference,  datatime,  schedules_id')
                        ->from('MakLutsk.move_on_schedule_total')
                        ->where('date(datatime) = "'.$day->format( "Y-m-d" ).'" AND schedules_id=:id',
                        array(':id' =>  $element->schedule->moveonscheduletotal->schedules_id))
                        ->order('datatime ASC')
                        ->query()
                        ->readAll();                   

                    foreach($difference_array as $diff_element){
                      $time_date = explode(' ', $diff_element['datatime']);
                      $times = explode(':', $time_date[1]);
                      $in_sectime = intval($times[0])*3600+intval($times[1])*60+intval($times[2]);
                      $arr[$element->bort->number][] = array('in_sectime' => $in_sectime,'difference' => $diff_element['time_difference']);
                    }

                  }
                  
                  $routename =  str_replace("/","0", $element->schedule->graph->route->name);
                  $bort = $element->bort->number;
                  $graph = $element->schedule->graph->name;

                  $time_date = explode(' ', $element->datatime);
                  $times = explode(':', $time_date[1]);
                  $in_sectime = intval($times[0])*3600+intval($times[1])*60+intval($times[2]); 

                  if ($arr[$bort]){
                  foreach ($arr[$bort] as $value) {
                    if ($value['in_sectime'] <= $in_sectime) $t_diff = $value['difference'];
                  }}

                  $result['days'][$j]['marks'][] = array(
                    'lt'  =>  (double) (floor($element->latitude/100)*100+(($element->latitude   - floor($element->latitude/100)*100)*100/60))/100,
                    'ln'  =>  (double) (floor($element->longitude/100)*100+(($element->longitude - floor($element->longitude/100)*100)*100/60))/100,
                    's'   =>  $element->speed,
                    't'   =>  $time_date[1],
                    'it'  =>  (int)$in_sectime,
                    'r'   =>  $routename,
                    'b'   =>  (int)$bort,
                    'g'   =>  (int)$graph,
                    'd'   =>  (int)$element->direction,
                    'td'  =>  (int)$t_diff,
                    );
                    unset($element);
                    $result['timeline'][$j][$in_sectime][] = sizeof($result['days'][$j]['marks'])-1;

                }
                unset($data);
                $offset += $limit;

              } else $do = false;
            }
              $result['date'][$j] = $day->format( "Y-m-d" );
              $j++;
            }
            echo json_encode($result);
            //Yii::app()->cache->set($cache_name, $result, $cache_time);}
      }

            // Графіки
            if($_GET['level'] === '2'){

              // Кешування даних
              /*$cache_name = md5($_GET['level'].'-'.$_GET['nodeid'].'-'.$_GET['data_begin'].'-'.$_GET['data_end']);
              if (Yii::app()->cache->get($cache_name)) echo CJSON::encode(Yii::app()->cache->get($cache_name));  else {*/

              $result = array('marks' => array());
              $t_diff = 0;
              $shedules = array();
              $arr = array();
              $difference = array();
              $gschedules = array();

              foreach ($tree_class->getChildNodes(2, $_GET['nodeid']) as $node => $value) {
                $shedules[] = $value['id'];
              }

              $in = implode (",", $shedules);

              $data = MoveOnMapTotal::model()->with('bort','schedule')->findAll(array(
                    'condition' =>  'date(datatime) between "'.$_GET['data_begin'].'" AND "'.$_GET['data_end'].'" AND schedule.graphs_id IN ('.$in.')',
                    'order' => 't.id')
                    );

              if($data){

                foreach($data as $element){

                  // Завантаження даних про різницю в часі
                  if (!in_array($element->schedule->moveonscheduletotal->schedules_id, $gschedules)){
                    $gschedules[] = $element->schedule->moveonscheduletotal->schedules_id;

                    $difference_array = Yii::app()->db->createCommand()
                        ->select('time_difference,  datatime,  schedules_id')
                        ->from('MakLutsk.move_on_schedule_total')
                        ->where('date(datatime) between "'.$_GET['data_begin'].'" AND "'.$_GET['data_end'].'" AND schedules_id=:id',
                        array(':id' =>  $element->schedule->moveonscheduletotal->schedules_id))
                        ->order('datatime ASC')
                        ->query()->readAll();                   

                    foreach($difference_array as $diff_element){
                      $time_date = explode(' ', $diff_element['datatime']);
                      $times = explode(':', $time_date[1]);
                      $in_sectime = intval($times[0])*3600+intval($times[1])*60+intval($times[2]);
                      $arr[$element->bort->number][] = array('in_sectime' => $in_sectime,'difference' => $diff_element['time_difference']);
                    }

                  }
                  
                  $routename =  str_replace("/","0", $element->schedule->graph->route->name);
                  $bort = $element->bort->number;
                  $graph = $element->schedule->graph->name;

                  $time_date = explode(' ', $element->datatime);
                  $times = explode(':', $time_date[1]);
                  $in_sectime = intval($times[0])*3600+intval($times[1])*60+intval($times[2]); 

                  if ($arr[$bort]){
                  foreach ($arr[$bort] as $value) {
                    if ($value['in_sectime'] <= $in_sectime) $t_diff = $value['difference'];
                  }}

                  $result['marks'][] = array(
                    'lt'  =>  (double) (floor($element->latitude/100)*100+(($element->latitude   - floor($element->latitude/100)*100)*100/60))/100,
                    'ln'  =>  (double) (floor($element->longitude/100)*100+(($element->longitude - floor($element->longitude/100)*100)*100/60))/100,
                    's'   =>  $element->speed,
                    't'   =>  $time_date[1],
                    'da'  =>  $time_date[0],
                    'r'   =>  $routename,
                    'b'   =>  (int)$bort,
                    'g'   =>  (int)$graph,
                    'd'   =>  (int)$element->direction,
                    'td'  =>  (int)$t_diff,
                  );
                }
              }
              echo json_encode($result);
              //Yii::app()->cache->set($cache_name, $result, $cache_time);}
          }

              // Один графік
              if($_GET['level'] === '3'){

              // Кешування даних
              /*$cache_name = md5($_GET['level'].'-'.$_GET['nodeid'].'-'.$_GET['data_begin'].'-'.$_GET['data_end']);
              if (Yii::app()->cache->get($cache_name)) echo CJSON::encode(Yii::app()->cache->get($cache_name));  else {*/

              $result = array('marks' => array());
              $t_diff = 0;

              $data = MoveOnMapTotal::model()->with('bort','schedule')->findAll(array(
                   					'condition'=>'date(datatime) between "'.$_GET['data_begin'].'" and "'.$_GET['data_end'].'" and schedule.graphs_id=:grid',
                   					'params'=>array(':grid'=>$_GET['nodeid']),
                   					'order' => 't.id')
                   					);

              if($data){

              $difference_array = Yii::app()->db->createCommand()
                  ->select('time_difference,  datatime,  schedules_id')
                  ->from('MakLutsk.move_on_schedule_total')
                  ->where('date(datatime) between :begin AND :end AND schedules_id=:id',
                  array(':id'   =>  $MoveOnMapTotals[0]->schedule->moveonscheduletotal->schedules_id,
                  ':begin'=>  $_GET['data_begin'],
                  ':end'  =>  $_GET['data_end'])
                  )->order('datatime ASC')
                  ->query()->readAll();

              $difference = array();

              foreach($difference_array as $diff_element){

                $time_date = explode(' ', $diff_element['datatime']);
                $times = explode(':', $time_date[1]);
                $in_sectime = intval($times[0])*3600+intval($times[1])*60+intval($times[2]);

                $arr[$in_sectime] = $diff_element['time_difference'];
              }

              if ($arr){
              foreach ($arr as $key => $value) {
                for ($key; $key < 86400; $key++){
                  $difference[$key] = $value;
                }
              }
              }

              foreach($data as $element){
              $routename =  str_replace("/","0", $element->schedule->graph->route->name);
              $bort = $element->bort->number;
              $graph = $element->schedule->graph->name;

              if (array_key_exists($in_sectime, $difference)) {
                $t_diff = $difference[$in_sectime];
              }

              $time_date = explode(' ', $element->datatime);
              $result['marks'][] = array(
                'lt'  =>  (double) (floor($element->latitude/100)*100+(($element->latitude   - floor($element->latitude/100)*100)*100/60))/100,
                'ln'  =>  (double) (floor($element->longitude/100)*100+(($element->longitude - floor($element->longitude/100)*100)*100/60))/100,
                's'   =>  $element->speed,
                't'   =>  $time_date[1],
                'da'  =>  $time_date[0],
                'r'   =>  $routename,
                'b'   =>  (int)$bort,
                'g'   =>  (int)$graph,
                'd'   =>  (int)$element->direction,
                'td'  =>  (int)$t_diff,
                );
                }
              }
              echo json_encode($result);
              //Yii::app()->cache->set($cache_name, $result, $cache_time);}
        }

}

  	   public function actionStops()
   	   {
      		  if($_POST['level'] === '1'){
      		  		$stops = Stops::model()->findAll();
      		  		$count = Stops::model()->count();
                   $count = 0;
                   $result = array(
									'points'=>array(),
									'count'=>array()
				   );
                      foreach($stops as $stop){

							$result['points'][] = array(
								'id'				=> $stop->id,
								'name'				=> $stop->name,
								'latitude'	 		=> (double) (floor($stop->latitude/100)*100+(($stop->latitude   - floor($stop->latitude/100)*100)*100/60))/100,
								'longitude' 		=> (double) (floor($stop->longitude/100)*100+(($stop->longitude - floor($stop->longitude/100)*100)*100/60))/100,
							);
                            $count++;

                      }
                      $result['count'] = $count;
                      echo json_encode($result);
       			}
 	   }
  	   public function actionPointsControl()
   	   {

  	   }
  	   public function actionAdvertisement()
   	   {

  	   }

}
?>
