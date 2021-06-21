<?php

class VidhylChartCOntroller extends BaseChartController
{

	protected $limit;

	protected $graph_id = 'vidhyl';

	public function actionCreate($chartType){
		$this->parseRequest($chartType);

		$categories = $this->getCategories();		
		$this->setSeries($categories);
		$this->setChartOption($categories);

		$this->showResult();
	}
	
	protected function parseRequest ($chartType){
		$this->limit = $_POST['vidhyl_limit'];
		parent::parseRequest($chartType);
	}

	protected function showResult (){
		echo json_encode($this->result);
	}

	protected function setChartOption ($categories){
		$this->setDefaultOption();
		$this->setTitleOption();
		$this->setCreditsOption();
		$this->setExportingOption();
		$this->setPlotOption();
		$this->setYaxisOption();
		$this->setXaxisOption($categories);
	}

	protected function getCategories (){
		$x_categories = array();
        foreach($this->chartData as $value){
            $x_categories[intval($value['r_n'])][$value['name']] = 1;
        }  
        ksort($x_categories, SORT_NUMERIC);
        $categories = array();
        foreach($x_categories as $race_num => $race_val){
            foreach($race_val as $cat_name => $cat_value){
                $categories[] = $race_num.'-'.$cat_name;  
            }
        }      

        return $categories;
	}

	protected function setSeries ($categories){
		$series = $this->getSeries($categories);
		$this->result['series'] = $series;
	}

	protected function getSeries ($categories){
		$series_name = $this->getSeriesName();
		$i = 0;
        $series = array();
        // print_r($series_name);
        // exit;
        foreach($series_name as $s_name => $s_value){
            $series[$i]['name'] = $s_name;
            foreach($categories as $cat_ => $cat_name){
            	// echo 'cat name: '.$cat_name."\n";
            	// print_r($s_value);
            	// echo "s_value: ".$s_value[$cat_name]."\n";
            	// echo "s_name: ".$s_name."\n";
                if(isset($s_value[$cat_name]) && ( $s_value[$cat_name]['date'] == $s_name) ){
                    $series[$i]['data'][] = array(
                        "name"  => $s_value[$cat_name]['t_N_t'],
                        "y"     => $s_value[$cat_name]['t_rizn']
                    ); 
                }
                else{
                    $series[$i]['data'][] = NULL;
                }
            }    
            ++$i;    
        }

        return $series;
	}

	protected function getSeriesName (){
		foreach($this->chartData as $value){
            $series_name[$value['date']][$value['r_n'].'-'.$value['name']] = $value;   
        }

        return $series_name; 
	}	

	protected function setDefaultOption (){
		$this->result['chart'] = array(
            "renderTo" => $this->graph_id . 'panel',
            "height" => $this->height,
            "width" => $this->width,
            "marginTop" => 60,
            "marginRight" => 10,
            "marginBottom" => 200,
            "marginLeft" => 50,
            "zoomType" => 'xy',
            "defaultSeriesType" => 'line'
        );         
	}

	protected function setTitleOption (){
		$this->result['title'] = array(
            "text" => $this->title,
            "style"=> array(
                "fontSize" => '11px'
            )
        );
	}

	protected function setCreditsOption (){
		$this->result['credits'] = array(
            "enabled" => false
        ); 
	} 

	protected function setExportingOption (){
		$this->result['exporting'] = array(
            'buttons' => array(
                'exportButton' => array(
                    'y' => 30
                ),
                'printButton' => array(
                    'y' => 30
                )
            )
        );  
	}

	protected function setYaxisOption (){
		$this->result['yAxis'] = array(
            "endOnTick" => false,
            "title" => array(
                'text' => ''
            ),
            "plotBands" => array(
                array(
                    "from"  => $this->limit ? -$this->limit : -2,
                    "to"    => $this->limit ? $this->limit : 2,
                    "color" => '#80FF80'
                )
            )  
        );
	}

	protected function setPlotOption (){
		$this->result['plotOptions'] = array(
            'line' => array(
                'point' => array(
                    'events' => array()
                )
            )
        );
	}

	protected function setXaxisOption ($categories){
		$this->result['xAxis'] = array(
            'categories' => $categories,
            'labels' => array(
                'rotation' => '-90',
                'y' => 50
            )
        );
	}
}