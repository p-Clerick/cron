<?php

class LocationsController extends Controller
{
  	public function actionRead(){
        $tree_class = new Tree;
        // Автобуси
        $from = strtotime($_GET['data_begin'].' '.$_GET['time_begin']);
        $to   = strtotime($_GET['data_end'].' '.$_GET['time_end']);
        $offset = 0;
        if($_GET['level'] === '1'){
            $result = array('days' => array());
            $routes = array(); // id'и графіків руху
            $j = 0; // Індекс дня
            foreach(json_decode($_GET["routes"]) as $route){
               $routes[] = $route;
            }
            $in .= ",".implode (",", $routes);
            $in = trim($in,",");
            $data = Locations::model()->with('route','graph','bort')->findAll(array(
                'condition'=>'unixtime between "'.$from.'" and "'.$to.'" AND t.routes_id IN ('.$in.') and bort.connection = "yes"',
                'offset'=> $offset,
                'order' => 't.unixtime')
            );
            $j = 0;
            $i = 0;
            foreach ($data as $element){
                $time_date = explode(' ', date('Y-m-d H:i:s', $element->unixtime));
                $times = explode(':', $time_date[1]);
                $in_sectime = intval($times[0])*3600+intval($times[1])*60+intval($times[2]);

                $result['days'][$j]['marks'][] = array(
                    'lt'  =>  (double) round((floor($element->latitude/100)*100+(($element->latitude - floor($element->latitude/100)*100)*100/60))/100, 6),
                    'ln'  =>  (double) round((floor($element->longitude/100)*100+(($element->longitude - floor($element->longitude/100)*100)*100/60))/100,6),
                    's'   =>  $element->speed,
                    't'   =>  $time_date[1],
                    'it'  =>  (int)$in_sectime,
                    /*'r'   =>  $element->routes_id,*/                    
                    'r'   =>  $element->route->name,
                    //'b'   =>  (int)$element->borts_id,
                    //'g'   =>  (int)$element->graphs_id,
                    'b'   =>  (int)$element->bort->number,
                    'gn'  =>  $element->bort->state_number,
                    'g'   =>  (int)$element->graph->name,
                    'd'   =>  (int)$element->direction,
                    'td'  =>  $element->time_difference
                );
                $result['timeline'][$j][$in_sectime][] = sizeof($result['days'][$j]['marks'])-1;
                $i++;            
            }
            $result['date'][$j] = $time_date[0];
            echo json_encode($result);

        }
        if($_GET['level'] === '2'){
            $result = array('marks' => array());
            $data = Locations::model()->with('route','graph','bort')->findAll(array(
                'condition'=>'unixtime between "'.$from.'" and "'.$to.'" AND t.routes_id = "'.$_GET['nodeid'].'" and bort.connection = "yes"',
                'order' => ' t.graphs_id, t.borts_id, t.unixtime')
            );
            $j = 0;
            $i = 0;
            foreach ($data as $element){
                $time_date = explode(' ', date('Y-m-d H:i:s', $element->unixtime));
                $result['marks'][] = array(
                    'lt'  =>  (double) round((floor($element->latitude/100)*100+(($element->latitude - floor($element->latitude/100)*100)*100/60))/100, 6),
                    'ln'  =>  (double) round((floor($element->longitude/100)*100+(($element->longitude - floor($element->longitude/100)*100)*100/60))/100,6),
                    's'   =>  $element->speed,
                    't'   =>  $time_date[1],
                    'da'  =>  $time_date[0],
                    'r'   =>  $element->route->name,
                    'b'   =>  (int)$element->bort->number,
                    'gn'  =>  $element->bort->state_number,
                    'g'   =>  (int)$element->graph->name,
                    'd'   =>  (int)$element->direction,
                    'td'  =>  $element->time_difference
                );
            }
            echo json_encode($result);           
        }
        if($_GET['level'] === '3'){
            //echo 'graph'; 
            $result = array('marks' => array());
            $data = Locations::model()->with('route','graph','bort')->findAll(array(
                'condition'=>'unixtime between "'.$from.'" and "'.$to.'" AND t.graphs_id = "'.$_GET['nodeid'].'" and bort.connection = "yes"',
                'order' => 't.borts_id, t.unixtime')
            );
            $j = 0;
            $i = 0;
            //print_r($data);
            //echo 'graph'; exit;
            foreach ($data as $element){
                $time_date = explode(' ', date('Y-m-d H:i:s', $element->unixtime));
                $result['marks'][] = array(
                    'lt'  =>  (double) round((floor($element->latitude/100)*100+(($element->latitude - floor($element->latitude/100)*100)*100/60))/100, 6),
                    'ln'  =>  (double) round((floor($element->longitude/100)*100+(($element->longitude - floor($element->longitude/100)*100)*100/60))/100,6),
                    's'   =>  $element->speed,
                    't'   =>  $time_date[1],
                    'da'  =>  $time_date[0],
                    'r'   =>  $element->route->name,
                    'b'   =>  (int)$element->bort->number,
                    'gn'  =>  $element->bort->state_number,
                    'g'   =>  (int)$element->graph->name,
                    'd'   =>  (int)$element->direction,
                    'td'  =>  $element->time_difference
                );
            }
            echo json_encode($result);

        }      
  	}   
}
?>
