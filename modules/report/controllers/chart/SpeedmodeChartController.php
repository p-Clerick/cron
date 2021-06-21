<?php

class SpeedmodeChartCOntroller extends BaseChartController
{

	protected $limit;

	protected $graph_id = 'speedmode';

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
			$x_categories[$i][$value['speed']] = 1;
        ++$i;    
        }  
        ksort($x_categories, SORT_NUMERIC);
        $categories = array();
        foreach($x_categories as $race_num => $race_val){
            foreach($race_val as $cat_name => $cat_value){
                $categories[] = $cat_name;  
            }
        }      
        
			foreach ($categories AS $key => $arrAddress) { 
			$categories[$key] = serialize($arrAddress); 
			} 
			$categories = array_unique($categories); 
			foreach ($categories AS $key => $strAddress) { 
			$categories[$key] = unserialize($strAddress); 
			}
			$temp = array();
			$j=0;
			foreach ($categories AS $key => $arrAddress){
			$temp[$j] = $categories[$key];
			++$j;
			}
			$categories = $temp;
		

        return $categories;
	}


	protected function setSeries ($categories){
		$series = $this->getSeries($categories);
		$this->result['series'] = $series;
	}

	protected function getSeries ($categories){
		$series_name = $this->getSeriesName();
        $series = array();
        $i=0;
			
			foreach($series_name as $s_name => $s_value){
				$series[$i]['name'] = $s_value['bort'];
				for($j=0; $j<=count($categories)-1;++$j){
					if($s_value['speed'] == $categories[$j])
					$series[$i]['data'][$j] = $s_value['speed_time_proz'];
					else $series[$i]['data'][$j] = NULL;
				}
			++$i;	
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
            "marginLeft" => 80,
            "zoomType" => 'xy',
            "defaultSeriesType" => 'column'
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
			'min' => 0,
			'max' => 100,
            'title' => array(
                'text' => 'Час руху, %'
            ) 
        );
	}

	protected function setPlotOption (){
		$this->result['plotOptions'] = array(
            'column' => array(
			'pointPadding' => 0.2,
			'borderWidth' => 0
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
