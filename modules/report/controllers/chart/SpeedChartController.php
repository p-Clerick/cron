<?php

class SpeedChartCOntroller extends BaseChartController
{

	protected $limit;

	protected $graph_id = 'speed';

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
		$i=0;
		
       foreach($this->chartData as $value){
            $x_categories[$i][$value['date']] = 1;
			++$i;    
        }  
        
        ksort($x_categories, SORT_NUMERIC);
        $categories = array();
        foreach($x_categories as $race_num => $race_val){
            foreach($race_val as $cat_name => $cat_value){
                $categories[] = $cat_name;  
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
        $series = array();
			
			foreach($series_name as $s_name => $s_value){
				$series[0]['name'] = 'Середня швидкість';
					if(isset($s_value['average_speed'])){
						$series[0]['data'][] = $s_value['average_speed'];
					}else{
						$series[0]['data'][] = NULL;
				}
				
			}
			
			foreach($series_name as $s_name => $s_value){
				$series[1]['name'] = 'Максимальна швидкість';
					if(isset($s_value['max_speed'])){
						$series[1]['data'][] = $s_value['max_speed'];
					}else{
						$series[1]['data'][] = NULL;
				}
				
			}
			           
        return $series;
	}

	protected function getSeriesName (){
		$series_name = array();
		$i = 0;
		foreach($this->chartData as $value){
            $series_name[$i] = $value;  
            ++$i; 
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
			"min" => 0,
            "endOnTick" => false,
            "title" => array(
                'text' => 'Швидкість (км/год)'
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
