<?php

class RaceChartCOntroller extends BaseChartController
{

	protected $limit;

	protected $graph_id = 'race';

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
            if ($_POST['chart_id'] == 'graphic'){
            $x_categories[$i][$value['date']] = 1;
            }elseif ($_POST['chart_id'] == 'race'){
				$x_categories[$i][$value['g_n']] = 1;
			}else{
					$x_categories[$i][$value['date']] = 1;
					}
        ++$i;    
        }  
        ksort($x_categories, SORT_NUMERIC);
        $categories = array();
        foreach($x_categories as $race_num => $race_val){
            foreach($race_val as $cat_name => $cat_value){
                $categories[] = $cat_name;  
            }
        }      
        
        if ($_POST['chart_id'] == 'race'){
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
			$temp[$j] = $categories[$key].'-Графік';
			++$j;
			}
			$categories = $temp;
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
		$i=0;
		if ($_POST['chart_id'] == 'graphic'){
			foreach($series_name as $s_name => $s_value){
				$series[$i]['name'] = 'Графік '.$s_value['g_n'];//'Маршрут - '.$s_value['m_n'];
					if(isset($s_value)){
						$series[$i]['data'][] = $s_value['mileage'];
					}else{
						$series[$i]['data'][] = NULL;
				}
			}
           
        }elseif($_POST['chart_id'] == 'race'){
		
			foreach($series_name as $s_name => $s_value){
				$series[$i]['name'] = $s_value['date'];
				for($j=0; $j<=count($categories)-1;++$j){
					if($s_value['g_n'].'-Графік' == $categories[$j])
					$series[$i]['data'][$j] = $s_value['mileage'];
					else $series[$i]['data'][$j] = NULL;
				}
			++$i;	
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
            'title' => array(
                'text' => 'Пробіг (км)'
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
